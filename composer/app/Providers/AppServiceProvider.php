<?php

namespace App\Providers;

use App\Models\AdminSetting;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $defaultLogoUrl = asset('branding/atglance-logo.png');
        $defaultFaviconUrl = asset('branding/favicon.ico');

        $sharedSettings = [
            'siteLogoUrl' => $defaultLogoUrl,
            'siteFaviconUrl' => $defaultFaviconUrl,
            'siteContent' => '',
            'siteFeatures' => [],
            'ssoEnabled' => false,
            'ssoProvidersForAuth' => [],
            'appVersion' => $this->resolveVersionFromDotEnv(),
        ];

        try {
            if (Schema::hasTable('admin_settings')) {
                $overrideLogoUrl = trim((string) AdminSetting::getValue('site_logo_url', ''));
                $storedLogoPath = trim((string) AdminSetting::getValue('site_logo_path', ''));

                if ($overrideLogoUrl !== '') {
                    $sharedSettings['siteLogoUrl'] = $overrideLogoUrl;
                } elseif ($storedLogoPath !== '') {
                    $localStorageBaseUrl = trim((string) env('LOCAL_STORAGE_BASE_URL', ''));
                    if ($localStorageBaseUrl === '') {
                        $siteUrl = rtrim((string) config('app.url', ''), '/');
                        $localStorageBaseUrl = $siteUrl !== '' ? $siteUrl . '/storage' : '';
                    }

                    $s3StorageBaseUrl = trim((string) env('S3_STORAGE_BASE_URL', ''));
                    if ($s3StorageBaseUrl === '') {
                        $bucket = trim((string) config('filesystems.disks.s3.bucket', env('AWS_BUCKET', '')));
                        $region = trim((string) config('filesystems.disks.s3.region', env('AWS_DEFAULT_REGION', '')));
                        if ($bucket !== '' && $region !== '') {
                            $s3StorageBaseUrl = sprintf('https://%s.s3.%s.amazonaws.com/', $bucket, $region);
                        }
                    }

                    $s3Enabled = filter_var($this->getEnvValue('S3_ENABLED', 'false'), FILTER_VALIDATE_BOOL);
                    $baseUrl = $s3Enabled ? $s3StorageBaseUrl : $localStorageBaseUrl;

                    if ($s3Enabled) {
                        $logoPath = ltrim($storedLogoPath, '/');
                        $encodedLogoPath = str_replace('%2F', '/', rawurlencode($logoPath));
                        $sharedSettings['siteLogoUrl'] = url('/site-logo/' . $encodedLogoPath);
                        $baseUrl = '';
                    }

                    if ($baseUrl !== '') {
                        $sharedSettings['siteLogoUrl'] = rtrim($baseUrl, '/') . '/' . ltrim($storedLogoPath, '/');
                    }
                }
                $faviconOverrideUrl = trim((string) AdminSetting::getValue('site_favicon_url', ''));
                if ($faviconOverrideUrl !== '') {
                    $sharedSettings['siteFaviconUrl'] = $faviconOverrideUrl;
                } elseif (!empty($sharedSettings['siteLogoUrl'])) {
                    $sharedSettings['siteFaviconUrl'] = (string) $sharedSettings['siteLogoUrl'];
                }

                $sharedSettings['siteContent'] = (string) AdminSetting::getValue('site_content', '');

                $featuresRaw = AdminSetting::getValue('site_features', '[]');
                $decodedFeatures = json_decode((string) $featuresRaw, true);
                $sharedSettings['siteFeatures'] = is_array($decodedFeatures) ? $decodedFeatures : [];

                $providerCatalog = config('sso.providers', []);
                $providerKeys = array_keys($providerCatalog);
                $enabledProviders = $this->resolveEnabledSsoProvidersFromEnvironment($providerKeys);
                $ssoEnabledFlag = filter_var($this->getEnvValue('SSO_ENABLED', 'false'), FILTER_VALIDATE_BOOL);

                $sharedSettings['ssoEnabled'] = $ssoEnabledFlag || !empty($enabledProviders);
                $sharedSettings['ssoProvidersForAuth'] = collect($enabledProviders)
                    ->map(function ($providerKey) use ($providerCatalog) {
                        $providerKey = (string) $providerKey;
                        if (!isset($providerCatalog[$providerKey])) {
                            return null;
                        }

                        return [
                            'key' => $providerKey,
                            'label' => $providerCatalog[$providerKey]['label'] ?? ucfirst($providerKey),
                            'icon' => $providerCatalog[$providerKey]['icon'] ?? 'fas fa-shield-alt',
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();
            }
        } catch (\Throwable $e) {
            $sharedSettings = [
                'siteLogoUrl' => $defaultLogoUrl,
                'siteFaviconUrl' => $defaultFaviconUrl,
                'siteContent' => '',
                'siteFeatures' => [],
                'ssoEnabled' => false,
                'ssoProvidersForAuth' => [],
                'appVersion' => $this->resolveVersionFromDotEnv(),
            ];
        }

        View::share($sharedSettings);

        View::composer('*', function ($view) {
            $workspaceSelectorWorkspaces = collect();
            $selectedWorkspaceId = null;

            try {
                if (
                    Auth::check()
                    && Schema::hasTable('workspaces')
                    && Schema::hasTable('workspace_user')
                ) {
                    $user = Auth::user();

                    if ((int) ($user->rbac_id ?? 0) === 100) {
                        $workspaceSelectorWorkspaces = Workspace::query()
                            ->where('org_id', (int) ($user->org_id ?? 200))
                            ->where('status', 'active')
                            ->orderBy('name')
                            ->get(['id', 'name']);
                    } else {
                        $workspaceSelectorWorkspaces = $user->workspaces()
                            ->orderBy('workspaces.name')
                            ->get(['workspaces.id', 'workspaces.name']);
                    }

                    $allowedWorkspaceIds = $workspaceSelectorWorkspaces
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->all();

                    $selectedWorkspaceId = (int) session('selected_workspace_id', 0);

                    if (!empty($allowedWorkspaceIds)) {
                        if (!in_array($selectedWorkspaceId, $allowedWorkspaceIds, true)) {
                            $selectedWorkspaceId = $allowedWorkspaceIds[0];
                            session(['selected_workspace_id' => $selectedWorkspaceId]);
                        }
                    } else {
                        $selectedWorkspaceId = null;
                        session()->forget('selected_workspace_id');
                    }
                }
            } catch (\Throwable $e) {
                $workspaceSelectorWorkspaces = collect();
                $selectedWorkspaceId = null;
            }

            $view->with('workspaceSelectorWorkspaces', $workspaceSelectorWorkspaces);
            $view->with('selectedWorkspaceId', $selectedWorkspaceId);
        });
    }

    private function getEnvValue(string $key, string $default = ''): string
    {
        $value = env($key);
        if ($value !== null && $value !== false) {
            return trim((string) $value);
        }

        $runtime = getenv($key);
        if ($runtime !== false) {
            return trim((string) $runtime);
        }

        return trim($default);
    }

    private function resolveEnabledSsoProvidersFromEnvironment(array $providerKeys): array
    {
        $raw = $this->getEnvValue('SSO_ENABLED_PROVIDERS', '');
        if ($raw === '') {
            return [];
        }

        return collect(explode(',', $raw))
            ->map(fn (string $provider) => strtolower(trim($provider)))
            ->filter(fn (string $provider) => in_array($provider, $providerKeys, true))
            ->unique()
            ->values()
            ->all();
    }

    private function resolveVersionFromDotEnv(): string
    {
        $envPath = base_path('.env');
        if (is_readable($envPath)) {
            $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $line = trim((string) $line);
                    if (str_starts_with($line, 'VERSION=')) {
                        return trim(substr($line, 8), " \t\n\r\0\x0B\"'");
                    }
                }
            }
        }

        return $this->getEnvValue('VERSION', (string) config('app.version', '0.1.0'));
    }
}
