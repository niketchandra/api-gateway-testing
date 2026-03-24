<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Support\InstallationState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InstallerController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (InstallationState::isInstalled()) {
            return redirect()->route('home');
        }

        return view('install.index', [
            'defaultDomain' => request()->getHttpHost(),
        ]);
    }

    public function install(Request $request): RedirectResponse
    {
        if (InstallationState::isInstalled()) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'app_url' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $input = trim((string) $value);
                    if (preg_match('/^https?:\/\//i', $input)) {
                        $fail('Enter domain or IP only, without http:// or https://.');
                        return;
                    }

                    if (str_contains($input, '/')) {
                        $fail('Domain or IP cannot include path segments.');
                        return;
                    }

                    if (!preg_match('/^[A-Za-z0-9.-]+(?::\d{1,5})?$/', $input)) {
                        $fail('Enter a valid domain or IP address.');
                    }
                },
            ],
            'use_https' => ['required', 'in:0,1'],
            'superadmin_email' => ['required', 'email', 'max:255'],
            'superadmin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $organizationName = trim((string) $validated['organization_name']);
        $appDomain = strtolower(trim((string) $validated['app_url']));
        $httpsEnabled = $validated['use_https'] === '1';
        $superAdminEmail = strtolower(trim((string) $validated['superadmin_email']));
        $superAdminPassword = (string) $validated['superadmin_password'];
        $normalizedUrl = ($httpsEnabled ? 'https://' : 'http://') . $appDomain;

        $this->updateEnv([
            'APP_URL' => $normalizedUrl,
            'APP_FORCE_HTTPS' => $httpsEnabled ? 'true' : 'false',
        ]);

        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);

            if (Schema::hasTable('organizations')) {
                Organization::query()
                    ->where('id', 200)
                    ->update([
                        'name' => $organizationName,
                        'updated_at' => now(),
                    ]);
            }

            if (Schema::hasTable('users')) {
                // Always keep the default super admin account present without overriding existing values.
                User::query()->firstOrCreate(
                    ['email' => 'superadmin@admin.com'],
                    [
                        'rbac_id' => 100,
                        'org_id' => 200,
                        'name' => 'admin',
                        'password' => 'Atglance@123',
                        'status' => 'active',
                    ]
                );

                User::query()->updateOrCreate(
                    ['email' => $superAdminEmail],
                    [
                        'rbac_id' => 100,
                        'org_id' => 200,
                        'name' => strstr($superAdminEmail, '@', true) ?: $superAdminEmail,
                        'password' => $superAdminPassword,
                        'status' => 'active',
                    ]
                );
            }

            Artisan::call('optimize:clear');
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors(['install' => 'Installation failed: ' . $exception->getMessage()]);
        }

        InstallationState::markInstalled([
            'installed_at' => now()->toDateTimeString(),
            'organization_name' => $organizationName,
            'app_domain' => $appDomain,
            'app_url' => $normalizedUrl,
            'https_enabled' => $httpsEnabled,
            'superadmin_email' => $superAdminEmail,
            'default_superadmin_email' => 'superadmin@admin.com',
            'superadmin_password' => $superAdminPassword,
        ]);

        return redirect()->route('install.info');
    }

    public function info(): View|RedirectResponse
    {
        if (!InstallationState::isInstalled()) {
            return redirect()->route('install.show');
        }

        return view('install.info', [
            'installation' => InstallationState::getData(),
        ]);
    }

    private function updateEnv(array $pairs): void
    {
        $envPath = base_path('.env');
        $envContent = is_file($envPath) ? (string) file_get_contents($envPath) : '';

        foreach ($pairs as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $line = $key . '=' . $value;

            if (preg_match($pattern, $envContent)) {
                $envContent = (string) preg_replace($pattern, $line, $envContent);
            } else {
                $envContent .= (str_ends_with($envContent, PHP_EOL) ? '' : PHP_EOL) . $line . PHP_EOL;
            }
        }

        file_put_contents($envPath, $envContent);
    }
}