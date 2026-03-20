@echo off
setlocal EnableExtensions EnableDelayedExpansion

cd /d "%~dp0"

set "NO_CACHE="
set "SKIP_MIGRATIONS="
set "SKIP_SEED="
set "COMPOSE_CMD="
set "COMPOSE_FILES=-f docker-compose.yml"
set "APP_SERVICES=api queue-worker phpmyadmin"

:parse_args
if "%~1"=="" goto args_done
if /I "%~1"=="--no-cache" (
    set "NO_CACHE=1"
    shift
    goto parse_args
)
if /I "%~1"=="--skip-migrations" (
    set "SKIP_MIGRATIONS=1"
    shift
    goto parse_args
)
if /I "%~1"=="--skip-seed" (
    set "SKIP_SEED=1"
    shift
    goto parse_args
)
echo Unknown argument: %~1
echo Supported arguments: --no-cache --skip-migrations --skip-seed
exit /b 1

:args_done
where docker >nul 2>nul
if errorlevel 1 (
    echo Docker is not installed or not available in PATH.
    exit /b 1
)

docker compose version >nul 2>nul
if not errorlevel 1 (
    set "COMPOSE_CMD=docker compose"
) else (
    where docker-compose >nul 2>nul
    if errorlevel 1 (
        echo Docker Compose is not installed. Install Docker Desktop or docker-compose.
        exit /b 1
    )
    set "COMPOSE_CMD=docker-compose"
)

if exist docker-compose-kong.yml (
    set "COMPOSE_FILES=%COMPOSE_FILES% -f docker-compose-kong.yml"
    set "APP_SERVICES=%APP_SERVICES% kong"
)

echo [1/9] Stopping existing containers
call :compose down --remove-orphans || exit /b 1

echo [2/9] Building images
if exist composer\public\storage (
    PowerShell.exe -NoProfile -Command "try { [System.IO.Directory]::Delete('composer\\public\\storage') } catch {}"
)
if defined NO_CACHE (
    call :compose build --no-cache || exit /b 1
) else (
    call :compose build || exit /b 1
)

echo [3/9] Starting infrastructure services
call :compose up -d mysql redis || exit /b 1

echo [4/9] Waiting for MySQL and Redis to become healthy
call :wait_for_health mysql 180 || exit /b 1
call :wait_for_health redis 45 || exit /b 1

echo [5/9] Starting application services
call :compose up -d --remove-orphans %APP_SERVICES% || exit /b 1

echo [6/9] Waiting for API container
call :wait_for_service api 60 || exit /b 1

echo [7/9] Installing PHP dependencies
call :execsh api "composer install --no-interaction --prefer-dist --optimize-autoloader" || exit /b 1
call :execsh queue-worker "composer install --no-interaction --prefer-dist --optimize-autoloader" || exit /b 1

echo [8/9] Ensuring Laravel app key and storage link
if not exist composer\.env (
    if exist composer\.env.example (
        copy /Y composer\.env.example composer\.env >nul || exit /b 1
    ) else (
        echo composer\.env and composer\.env.example were not found.
        exit /b 1
    )
)

findstr /B /C:"APP_KEY=base64:" composer\.env >nul 2>nul
if errorlevel 1 (
    call :execsh api "php artisan key:generate --force" || exit /b 1
)

call :execsh api "php artisan storage:link || true" || exit /b 1

echo [9/9] Clearing Laravel caches
call :execsh api "php artisan optimize:clear" || exit /b 1

if defined SKIP_MIGRATIONS (
    echo [10/10] Skipping migrations
) else (
    echo [10/10] Running migrations
    call :execsh api "php artisan migrate --force" || exit /b 1
)

if defined SKIP_SEED (
    echo Seeder step skipped
) else (
    echo Running seeders
    call :execsh api "php artisan db:seed --force" || exit /b 1
)

echo Final service status
call :compose ps || exit /b 1

echo.
echo Rebuild and redeploy completed successfully.
exit /b 0

:compose
call %COMPOSE_CMD% %COMPOSE_FILES% %*
exit /b %errorlevel%

:execsh
set "TARGET_SERVICE=%~1"
set "TARGET_SCRIPT=%~2"
call %COMPOSE_CMD% %COMPOSE_FILES% exec -T %TARGET_SERVICE% sh -lc "%TARGET_SCRIPT%"
exit /b %errorlevel%

:wait_for_service
set "TARGET_SERVICE=%~1"
set /a ATTEMPTS=%~2

:wait_loop
if %ATTEMPTS% LEQ 0 (
    echo Timed out waiting for service %TARGET_SERVICE%.
    exit /b 1
)

call %COMPOSE_CMD% %COMPOSE_FILES% ps %TARGET_SERVICE% | findstr /I "%TARGET_SERVICE%" >nul 2>nul
if not errorlevel 1 exit /b 0

timeout /t 2 /nobreak >nul
set /a ATTEMPTS-=1
goto wait_loop

:wait_for_health
set "TARGET_SERVICE=%~1"
set /a ATTEMPTS=%~2

:health_loop
if %ATTEMPTS% LEQ 0 (
    echo Timed out waiting for service %TARGET_SERVICE% to become healthy.
    exit /b 1
)

call %COMPOSE_CMD% %COMPOSE_FILES% ps %TARGET_SERVICE% | findstr /I /C:"(healthy)" >nul 2>nul
if not errorlevel 1 exit /b 0

timeout /t 2 /nobreak >nul
set /a ATTEMPTS-=1
goto health_loop
