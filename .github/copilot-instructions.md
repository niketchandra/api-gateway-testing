# AtGlance Project Master Instructions

AtGlance Management Console is an enterprise-grade, self-hosted application designed for organizations that want to manage internal system configurations and services within their own network boundary. The platform provides centralized operational control, configuration backup/version management, service visibility, and secure access through multiple user layers (superadmin, admin, and user), so each role can monitor and manage the responsibilities relevant to them.

The management console integrates with AtGlance CLI (an Ubuntu-focused command-line tool) to support end-to-end operational workflows such as service monitoring, system registration/validation lifecycle, and configuration backup handling (save, list, import, local/remote sync) from terminal operations into centralized dashboard visibility. As a one-stop console, AtGlance allows users and admins to view registered systems, mapped services, configuration files, and current configuration versions in a single interface.

This file is the full project operating manual for the AtGlance API Gateway platform in this repository.

## 1) Project Summary

AtGlance is a Laravel-based platform that combines:

- Web UI portal for users/admin/superadmin
- REST APIs behind Kong API Gateway
- MySQL as primary database
- Redis-backed queues for resilience
- Circuit breaker strategy for database outages
- Optional S3 storage and migration from local storage
- SSO provider support (currently GitHub callback implemented)

Main goals:

- Register systems and services
- Upload and version configuration backups
- Manage organizations/workspaces/users/roles
- Operate through role-scoped dashboards
- Continue accepting write requests during DB outages by queueing jobs

## 2) Runtime Topology

Core containers:

- `api`: Laravel app, serves web + API
- `mysql`: MySQL 8 database
- `redis`: queue/cache backend
- `queue-worker`: background worker for queued jobs
- `kong`: gateway/proxy for public API routing
- `phpmyadmin`: DB admin UI

Primary endpoints:

- Laravel direct: `http://localhost:8000`
- Kong proxy: `http://localhost:8002`
- Kong admin API: `http://localhost:8001`
- phpMyAdmin: `http://localhost:8080`

## 3) Installation And First-Time Setup Flow

### 3.1 Start containers

Use project root compose files:

- `docker-compose.yml` (api/mysql/redis/queue-worker/phpmyadmin)
- `docker-compose-kong.yml` (kong)

Command:

```bash
docker compose -f docker-compose.yml -f docker-compose-kong.yml up -d --build
```

### 3.2 Laravel installer flow

Open `http://localhost:8000`.

If app is not installed, root redirects to `/install`.

Installer captures:

- organization name
- app IP and optional domain alias
- HTTPS toggle
- superadmin email/password

Installer actions:

- runs migrations and seeders
- ensures default superadmin account exists
- creates/updates provided superadmin
- stores installation marker in `storage/app/installer/installed.json`
- stores initial site settings in DB (`admin_settings`)

### 3.3 Post-install role path

- Super admin/admin login redirects to admin dashboard
- Regular user login redirects to user dashboard

## 4) Authentication Model (How Same Login Supports Different Users)

### 4.1 Web login

Web login checks `users.email` + `password_hash`, then uses Laravel session auth.

After auth, role route split by `rbac_id`:

- `100` super_admin -> admin area
- `101` admin -> admin area
- `102` user -> user dashboard

### 4.2 API login (session token)

`POST /api/auth/login` returns a temporary bearer token saved in `session_tokens` as SHA-256 hash.

Used by middleware `auth.session` for endpoints like PAT token creation.

### 4.3 PAT login (permanent API token)

PAT tokens are created with prefix `atgla-...`, stored hashed in `personal_access_tokens`.

Used by middleware `auth.pat` for system/service/config-file APIs.

## 5) Roles, Responsibilities, And Data Classification

RBAC table seeded with:

- `100` super_admin (read/write/execute true)
- `101` admin (read/write/execute true)
- `102` user (read true, write/execute false)

### 5.1 Superadmin

- Full enterprise control
- Create organizations/workspaces
- Add admin/user to workspace
- Manage workspace metadata and lifecycle
- Access enterprise console routes
- Access admin settings (site, s3, migration, mail, sso)

### 5.2 Admin

- Manage users in allowed workspaces
- View user dashboards/profiles within scope
- Add/remove regular users in workspace
- Cannot perform superadmin-only operations

### 5.3 User

- Access personal dashboard/profile/settings
- Manage own API keys
- Register systems/services/config files through PAT-auth APIs
- Cannot access admin middleware routes

### 5.4 Workspace and org scope

- Organizations own workspaces
- `workspace_user` maps users to workspaces with `is_admin`
- visibility helpers in dashboard/admin controllers enforce workspace-based data access

## 6) Middleware Pipeline And Behavior

Global middleware:

- `ActivityLogger`: logs transactional requests to `activity_logs`

Aliases configured in bootstrap:

- `auth.session`: validates bearer token against `session_tokens`
- `auth.pat`: validates PAT format, status, expiry, user binding
- `admin.role`: allows only RBAC 100/101
- `super.admin.role`: allows only RBAC 100
- `active.user`: blocks inactive users and logs out
- `profile.completed`: requires DOB + PIN setup
- `app.installed`: blocks app routes before installation

## 7) API Section: Endpoints, Purpose, And Logic

All API routes are prefixed with `/api` internally by Laravel. Through Kong, they are exposed as non-prefixed paths (for example `/users`).

### 7.1 Auth APIs

1. `POST /auth/register`
- Purpose: create API user account
- Logic: validate name/email/password(+confirm), optional dob, save hashed password

2. `POST /auth/login`
- Purpose: create temporary session token
- Logic: validate credentials, create 24h token in `session_tokens`

3. `POST /auth/logout` (`auth.session`)
- Purpose: invalidate session token
- Logic: hash bearer token and delete matching `session_tokens` row

### 7.2 Token validation + PAT lifecycle

4. `POST /auth/validate-token`
- Purpose: verify PAT from request body
- Logic: hash token, validate status/expiry/user active, return user+workspace payload

5. `GET /auth/validate-token`
- Purpose: verify PAT from Authorization header
- Logic: same validation path as above

6. `POST /auth/pat-tokens` (`auth.session`)
- Purpose: mint permanent PAT token
- Logic: create `atgla-` token, store SHA-256 hash, default long expiry

7. `GET /auth/pat-tokens` (`auth.session`)
- Purpose: list caller PAT tokens
- Logic: returns token metadata (not plaintext token)

### 7.3 User APIs (with resilience)

Resource: `/users` -> `index/store/show/update/destroy`

- Purpose: user CRUD
- Logic:
  - uses `DatabaseCircuitBreaker`
  - reads return `503` if breaker open/error
  - writes attempt direct DB first
  - if unavailable/failure: queue jobs (`CreateUserJob`, `UpdateUserJob`, `DeleteUserJob`) and return `202`

### 7.4 Product APIs (with resilience)

Resource: `/products` -> `index/store/show/update/destroy`

- Purpose: product CRUD
- Logic mirrors user APIs using product jobs and circuit-breaker-aware writes

### 7.5 File and configuration APIs (`auth.pat`)

1. `POST /files/upload`
- generic raw file record + raw_data entry

2. `GET /files/{fileId}`
- returns stored metadata + raw data

3. `POST /config-files/upload`
- uploads config file to active storage disk (local or s3)
- resolves/creates related service
- writes metadata to `configuration_files`, content to `raw_data`
- auto increments service version label (`v1`, `v2`, ...)

4. `GET /config-files`
- list active config files for caller

5. `GET /config-files/filter?system_id=&validation_hash=`
- filter active configs by system and hash

6. `GET /config-files/config-show?system_id=&validation_key=&service_name=`
- cross-user version history by exact matching tuple

7. `GET /config-files/{fileId}`
- download file from resolved storage disk/path

8. `GET /config-files/download/{id}?system_id=`
- download by config id + system validation

9. `GET /config-files/{fileId}/raw-data`
- fetch raw data row for active config

10. `DELETE /config-files/{fileId}`
- soft-delete (set inactive) on config and raw_data

### 7.6 Services APIs (`auth.pat`)

1. `POST /services`
- create or reuse service (`firstOrCreate`) for user+system+service_name

2. `GET /services`
- list active services, optional `system_id` filter

3. `GET /services/{serviceId}`
- show one active service owned by caller

### 7.7 System registration/state APIs (`auth.pat` except force endpoints)

1. `POST /system-register`
- register a system with metadata, workspace assignment, PAT binding

2. `GET /system-register`
- list active systems of caller

3. `GET /system-register/pat/{patTokenId}`
- list systems grouped by selected PAT token

4. `GET /system-register/user/{userId}`
- list systems for caller-only user id check

5. `POST /system-deregister`
- set status inactive on owned system
- requires password or PIN confirmation

6. `POST|GET /system-reactive`
- reactivate owned system
- requires password or PIN confirmation

7. `POST|GET /system-deregister-force`
- force inactivate any system by `email + password/pin`
- writes action to `activity_logs`

8. `POST|GET /system-reactivate-force`
- force reactivate any system by `email + password/pin`
- writes action to `activity_logs`

## 8) Web Routes And GUI Modules

### 8.1 Public and install routes

- `GET /install`, `POST /install`, `GET /install/info`
- `GET /` root app/installer gateway
- `GET /login` redirect to home

### 8.2 Authentication routes

- `POST /login`, `POST /register`, `POST /logout`
- `GET /auth/sso/{provider}`
- `GET /auth/sso/{provider}/callback`
- `POST /password/email`
- `POST /contact`

### 8.3 User protected routes

- workspace selection, dashboard, backups, systems, services, monitoring, vulnerabilities
- settings update, PIN reset, API key create/view/revoke
- password update, profile setup, products page

### 8.4 Admin protected routes (`admin.role`)

- admin dashboard
- user listing, create, profile, update, user-level system/service drilldowns
- workspace management for admins
- admin settings tabs: site/s3/backup-restore/migration/mail/sso

### 8.5 Superadmin-only routes (`super.admin.role`)

- enterprise console
- enterprise workspace/org creation
- workspace CRUD + admin assignments

### 8.6 GUI templates available

User portal templates:

- `resources/views/app.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/settings.blade.php`
- `resources/views/profile.blade.php`
- `resources/views/products.blade.php`
- `resources/views/configuration-backups.blade.php`
- `resources/views/systems-registered.blade.php`
- `resources/views/systems-registered-edit.blade.php`
- `resources/views/system-services.blade.php`
- `resources/views/view-service-versions.blade.php`
- `resources/views/view-configuration.blade.php`
- `resources/views/live-service-monitoring.blade.php`
- `resources/views/vulnerabilities-identified.blade.php`

Admin templates:

- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/users.blade.php`
- `resources/views/admin/user-show.blade.php`
- `resources/views/admin/user-profile.blade.php`
- `resources/views/admin/user-services.blade.php`
- `resources/views/admin/user-service-versions.blade.php`
- `resources/views/admin/manage-workspaces.blade.php`
- `resources/views/admin/workspace-detail.blade.php`
- `resources/views/admin/enterprise-console.blade.php`
- `resources/views/admin/settings.blade.php`

Install templates:

- `resources/views/install/index.blade.php`
- `resources/views/install/info.blade.php`

## 9) Controller Function Map (Implementation Inventory)

### API controllers

- `Api/AuthController`: `register`, `login`, `logout`
- `Api/PatTokenController`: `index`, `store`
- `Api/TokenValidationController`: `validateToken`, `validateFromHeader`
- `Api/UserController`: `index`, `store`, `show`, `update`, `destroy`
- `Api/ProductController`: `index`, `store`, `show`, `update`, `destroy`
- `Api/ServiceController`: `index`, `store`, `show`
- `Api/SystemRegisterController`: `index`, `getByPatToken`, `getByUser`, `store`, `deregister`, `deregisterForce`, `reactive`, `reactiveForce`
- `Api/FileController`: `uploadConfigFile`, `upload`, `download`, `listConfigFiles`, `listConfigFilesBySystemAndHash`, `listConfigVersionsBySystemValidationAndService`, `downloadConfigFile`, `downloadConfigFileById`, `getRawData`, `deleteConfigFile`

### Web controllers

- `AuthController`: web login/register/logout, SSO redirect/callback, password reset email stub, contact capture
- `DashboardController`: user dashboard data, workspace scoping, settings/profile/API-key management, config/system/service views
- `AdminDashboardController`: admin/superadmin dashboards, users/workspaces/enterprise operations, site/s3/backup/migration/mail/sso settings, logo serving
- `InstallerController`: installation wizard and bootstrap process

### 9.1 Middleware functions

- `ActivityLogger`: `shouldLog`, `handle`
- `AuthenticateSession`: `handle`
- `AuthenticatePatToken`: `handle`
- `AdminRoleMiddleware`: `handle`
- `SuperAdminRoleMiddleware`: `handle`
- `EnsureUserIsActive`: `handle`
- `EnsureProfileSetupComplete`: `handle`
- `EnsureApplicationInstalled`: `handle`

### 9.2 Model functions

- `ActivityLog`: `user`
- `AdminSetting`: `getValue`, `putValue`
- `ConfigurationFile`: `user`, `rawData`, `service`
- `Organization`: `users`, `systemRegisters`, `scopeActive`
- `PatToken`: `booted`, `tokenable`, `user`, `generateCustomToken`
- `RawData`: `file`, `user`, `service`
- `Rbac`: `users`, `hasPermission`
- `Service`: `user`, `system`, `configurationFiles`
- `Session`: `user`, `generateToken`, `isExpired`
- `SystemRegister`: `workspace`, `generateUniqueId`, `boot`
- `User`: `casts`, `getAuthPassword`, `setPasswordAttribute`, `configurationFiles`, `rawData`, `sessions`, `patTokens`, `workspaces`, `rbac`, `organization`, `hasPermission`, `isSuperAdmin`, `isAdmin`
- `Workspace`: `users`, `admins`, `regularUsers`, `organization`, `scopeActive`, `hasUserAsAdmin`, `addUser`, `removeUser`

### 9.3 Job and service functions

- Jobs (`CreateUserJob`, `UpdateUserJob`, `DeleteUserJob`, `CreateProductJob`, `UpdateProductJob`, `DeleteProductJob`): `__construct`, `handle`, `failed`
- `CircuitBreaker`: `call`, `onSuccess`, `onFailure`, `isOpen`, `getState`, `reset`
- `DatabaseCircuitBreaker`: `query`, `isAvailable`, `getState`, `reset`

## 10) Model And Database Classification

Key models and purpose:

- `User`: account, role, org, profile, credentials
- `Rbac`: role permissions read/write/execute
- `Organization`: tenancy root entity
- `Workspace`: sub-scope inside org; user membership + admin flag
- `PatToken`: permanent API token records
- `Session`: temporary API session tokens
- `SystemRegister`: registered systems and metadata
- `Service`: services attached to systems
- `ConfigurationFile`: config metadata and storage location
- `RawData`: config body/content snapshots
- `AdminSetting`: dynamic admin-controlled config
- `ActivityLog`: auditable transactional activity

Core relationship classification:

- user -> many PAT/session/config/raw rows
- org -> many users/workspaces
- workspace <-> users (pivot `workspace_user` with `is_admin`)
- system belongs to user/org/workspace + PAT token
- service belongs to system and user
- config belongs to user/system/service and has one raw_data

## 11) Queue, Redis, Circuit Breaker

Queue jobs:

- `CreateUserJob`, `UpdateUserJob`, `DeleteUserJob`
- `CreateProductJob`, `UpdateProductJob`, `DeleteProductJob`

Retry/backoff policy in worker command:

- tries: 5
- backoff: `30,60,120,300,600`
- timeout: 60s

Circuit breaker services:

- `CircuitBreaker`
- `DatabaseCircuitBreaker`

Behavior:

- reads fail fast with `503` when unavailable
- writes are queued and return `202` with request_id
- queue worker retries until DB recovers

## 12) Kong Setup And What Is Configured

Kong uses DB-less declarative config at `kong/kong.yml` with format `3.0`.

Configured services include:

- `users-service` -> `http://api:8000/api`
- `auth-service` -> `http://api:8000/api`
- `files-service` -> `http://api:8000/api`
- additional `/api/...` force/reactive compatibility services

Configured route families:

- users/products CRUD routes
- auth register/login/logout + PAT + validate-token routes
- system-register/deregister/reactive and force endpoints
- files/config-files/services route groups

Configured plugin:

- rate limiting plugin on `users-service` (`minute: 4`, `policy: local`)

Operational note:

- Kong does not auto-reload declarative file; restart Kong container after changes.

## 13) Dockerfile And Compose Purpose

### Dockerfile purpose

- Base image `php:8.2-cli`
- installs PHP extensions needed by app (`pdo_mysql`, `mbstring`, `xml`, `zip`, `redis`)
- installs Composer
- copies Laravel app from `composer/` into `/app`
- sets baseline env and installs dependencies
- serves Laravel on port 8000

### docker-compose purpose

- `docker-compose.yml`: app runtime stack (api, db, queue, cache, phpmyadmin)
- `docker-compose-kong.yml`: gateway layer stack
- combined usage provides full path: client -> kong -> api -> db/redis

Additional compose variants in repo:

- `docker-compose-atglance-1_6.yml`: single-file compose that includes Kong + app stack using published `1.6` images.
- `docker-compose-atglance-1_6-2.yml`: alternate compatibility stack (MySQL 5.7, phpMyAdmin on 8181, Kong built from `kong/` with image tag `1.6-2`).

## 14) phpMyAdmin Purpose

phpMyAdmin container provides a browser database administration UI for MySQL:

- inspect tables/rows
- run SQL
- troubleshoot migrations/data

Connected via env:

- host `mysql`
- user `root`
- password `root`

## 15) .env Purpose And What Is Defined

Environment file controls app behavior, secrets, endpoints, and feature toggles.

Major groups in `.env.example`:

- App/runtime: `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`, `APP_FORCE_HTTPS`, `VERSION`
- Database: `DB_*`
- Sessions/cache/queue: `SESSION_*`, `CACHE_*`, `QUEUE_CONNECTION`, `REDIS_*`
- Mail: `MAIL_*`
- Storage local/s3: `FILESYSTEM_DISK`, `LOCAL_STORAGE_BASE_URL`, `S3_ENABLED`, `AWS_*`, `S3_STORAGE_BASE_URL`
- SSO global/provider vars: `SSO_ENABLED`, `SSO_ENABLED_PROVIDERS`, provider URL/client/secret/tenant keys
- Migration toggle: `MIGRATION_ENABLED`

Secrets can be stored as encrypted `ENC:...` strings and decrypted at runtime by controller helpers.

## 16) SSO Feature Details

SSO is configurable in admin settings and env/provider maps.

Supported provider catalog in config:

- azure-ad, microsoft, github, gitlab, okta, auth0, oidc, authentik, google

### 16.1 Azure AD / Microsoft URL Construction

For Azure AD and Microsoft providers, the authorization URL is automatically constructed from the tenant ID if no explicit URL is provided:

- Format: `https://login.microsoftonline.com/{TENANT_ID}/oauth2/v2.0/authorize`
- **Required**: `SSO_AZURE_AD_TENANT_ID` or `SSO_MICROSOFT_TENANT_ID`
- Optional: `SSO_AZURE_AD_URL` or `SSO_MICROSOFT_URL` (can be omitted for standard Azure endpoints)
- This reduces configuration overhead and eliminates URL construction errors

### 16.2 Other callback details

Current callback implementation in code:

- GitHub OAuth callback fully handled

PIN reset flow supports SSO re-verification intent:

- initiate reset -> redirect to provider -> callback validates identity -> allow/reset PIN

## 17) S3, Backups, Migration, Cron Features

### 17.1 S3 storage mode

- runtime disk resolves to `local` unless S3 is enabled and credentials are complete
- config files can be stored/read from local or s3

### 17.2 Backup-restore settings

Admin tab stores backup flags and cron frequency preferences in `admin_settings` and `.env`.

Supported frequency labels include:

- hourly
- every_six_hours
- every_twelve_hours
- daily
- weekly
- monthly

### 17.3 Migration (local <-> s3)

Migration flow:

- analyze direction (`local_to_s3` or `s3_to_local`)
- compute file entry set
- stream copy files between disks
- verify checksum
- optionally delete source
- update progress/status in `admin_settings`

### 17.4 Cron note

The project stores cron preferences, but there is no explicit scheduler task registration in `routes/console.php` for backups yet. Runtime scheduling/execution must be wired by deploy/ops layer.

## 18) End-To-End Flow (Step By Step)

1. Build/start docker stack.
2. Open app, complete installer.
3. Superadmin logs in.
4. Superadmin creates organization/workspace(s) as needed.
5. Superadmin creates admin users and/or assigns admins to workspaces.
6. Admin creates regular users and assigns them into managed workspaces.
7. User logs in and completes profile setup (DOB + PIN).
8. User creates PAT token from settings/API.
9. External client uses PAT to call system registration APIs.
10. Services are created/linked to systems.
11. Config files uploaded and versioned.
12. Dashboard and admin screens show systems/services/config trends.
13. If DB outage occurs, write APIs queue jobs to Redis and return `202`.
14. Queue worker retries and applies writes after DB recovery.
15. Optional: enable S3, run migration, and configure backup preferences.

## 19) Important Constraints And Security Notes

- PAT tokens are shown only at creation time (store securely).
- Sensitive operations (for example system deactivate/reactivate, key actions) use password/PIN validation paths.
- Inactive users are blocked at middleware level.
- Activity logging intentionally avoids blocking request flow on logging failure.

## 20) Source Of Truth Files

Primary implementation files:

- `composer/routes/api.php`
- `composer/routes/web.php`
- `composer/bootstrap/app.php`
- `composer/app/Http/Controllers/Api/*.php`
- `composer/app/Http/Controllers/*.php`
- `composer/app/Http/Middleware/*.php`
- `composer/app/Models/*.php`
- `composer/database/migrations/2026_03_01_000000_create_consolidated_schema.php`
- `docker-compose.yml`
- `docker-compose-kong.yml`
- `Dockerfile`
- `kong/kong.yml`
- `composer/.env.example`

This document should be updated whenever routes, role rules, storage strategy, SSO providers, Kong routes, or queue behavior changes.


# Caveman Mode

Be concise.
No fluff.
Technical accuracy important.
Commands/configs first.
Short debugging responses.

For troubleshooting:
- root cause
- fix
- verification

Avoid long introductions.

Use caveman style.

Short technical responses.
No filler.
Commands first.
Root cause + fix + verification.