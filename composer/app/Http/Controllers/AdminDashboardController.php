<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\ConfigurationFile;
use App\Models\Organization;
use App\Models\Service;
use App\Models\SystemRegister;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $totalSystems = SystemRegister::count();
        $totalServices = Service::count();
        $totalConfigFiles = ConfigurationFile::count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalSystems',
            'totalServices',
            'totalConfigFiles'
        ));
    }

    public function usersIndex(): View
    {
        $users = User::query()
            ->leftJoin('system_register', 'system_register.user_id', '=', 'users.id')
            ->leftJoin('services', 'services.user_id', '=', 'users.id')
            ->leftJoin('configuration_files', 'configuration_files.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.status',
                'users.created_at',
                DB::raw('COUNT(DISTINCT system_register.id) as system_count'),
                DB::raw('COUNT(DISTINCT services.service_id) as service_count'),
                DB::raw('COUNT(DISTINCT configuration_files.id) as configuration_count')
            )
            ->where(function ($query) {
                $query->whereNull('users.rbac_id')
                    ->orWhereNotIn('users.rbac_id', [100, 101]);
            })
            ->groupBy('users.id', 'users.name', 'users.email', 'users.status', 'users.created_at')
            ->orderByDesc('users.created_at')
            ->paginate(25, ['*'], 'users_page');

        $adminUsers = User::query()
            ->leftJoin('system_register', 'system_register.user_id', '=', 'users.id')
            ->leftJoin('services', 'services.user_id', '=', 'users.id')
            ->leftJoin('configuration_files', 'configuration_files.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.status',
                'users.created_at',
                DB::raw('COUNT(DISTINCT system_register.id) as system_count'),
                DB::raw('COUNT(DISTINCT services.service_id) as service_count'),
                DB::raw('COUNT(DISTINCT configuration_files.id) as configuration_count')
            )
            ->where('users.rbac_id', 101)
            ->groupBy('users.id', 'users.name', 'users.email', 'users.status', 'users.created_at')
            ->orderByDesc('users.created_at')
            ->paginate(25, ['*'], 'admin_page');

        return view('admin.users', compact('users', 'adminUsers'));
    }

    public function createUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['user', 'admin'])],
        ]);

        $rbacId = $validated['role'] === 'admin' ? 101 : 102;

        User::create([
            'name' => $validated['username'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'rbac_id' => $rbacId,
        ]);

        return redirect()->route('admin.users')->with('success', 'User registered successfully.');
    }

    public function userProfile(User $user): View
    {
        $stats = [
            'systems' => SystemRegister::where('user_id', $user->id)->count(),
            'services' => Service::where('user_id', $user->id)->count(),
            'configurations' => ConfigurationFile::where('user_id', $user->id)->count(),
        ];

        return view('admin.user-profile', compact('user', 'stats'));
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'role' => ['required', Rule::in(['user', 'admin'])],
        ]);

        $user->name = $validated['username'];
        $user->first_name = $validated['first_name'] ?? null;
        $user->last_name = $validated['last_name'] ?? null;
        $user->email = $validated['email'];
        $user->status = $validated['status'];
        $user->rbac_id = $validated['role'] === 'admin' ? 101 : 102;
        $user->save();

        return redirect()
            ->route('admin.users.profile', ['user' => $user->id])
            ->with('success', 'User profile updated successfully.');
    }

    public function userDashboard(User $user): View
    {
        $systems = SystemRegister::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $serviceCountsBySystem = Service::query()
            ->where('user_id', $user->id)
            ->select('system_id', DB::raw('COUNT(*) as total'))
            ->groupBy('system_id')
            ->pluck('total', 'system_id');

        $systems->each(function (SystemRegister $system) use ($serviceCountsBySystem) {
            $system->service_count = (int) ($serviceCountsBySystem[$system->id] ?? 0);
        });

        $stats = [
            'systems' => SystemRegister::where('user_id', $user->id)->count(),
            'services' => Service::where('user_id', $user->id)->count(),
            'configurations' => ConfigurationFile::where('user_id', $user->id)->count(),
        ];

        return view('admin.user-show', compact('user', 'systems', 'stats'));
    }

    public function userSystemServices(User $user, int $systemId): View
    {
        $system = SystemRegister::query()
            ->where('id', $systemId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $services = Service::query()
            ->where('services.user_id', $user->id)
            ->where('services.system_id', $systemId)
            ->leftJoin('configuration_files', 'services.service_id', '=', 'configuration_files.service_id')
            ->select(
                'services.service_id',
                'services.service_name',
                'services.system_id',
                'services.status',
                'services.created_at',
                DB::raw('COUNT(configuration_files.id) as config_count'),
                DB::raw('MAX(configuration_files.version) as latest_version')
            )
            ->groupBy(
                'services.service_id',
                'services.service_name',
                'services.system_id',
                'services.status',
                'services.created_at'
            )
            ->orderByDesc('services.created_at')
            ->get();

        return view('admin.user-services', compact('user', 'system', 'services'));
    }

    public function userServiceVersions(User $user, int $serviceId): View
    {
        $service = Service::query()
            ->where('service_id', $serviceId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $versions = DB::table('configuration_files as cf')
            ->leftJoin('system_register as sr', 'cf.system_register_id', '=', 'sr.id')
            ->where('cf.user_id', $user->id)
            ->where('cf.service_id', $serviceId)
            ->select(
                'cf.id',
                'cf.service_id',
                'cf.file_name',
                'cf.service_name',
                'cf.version',
                'cf.validation_hash',
                'cf.status',
                'cf.created_at',
                'cf.updated_at',
                'sr.system_name'
            )
            ->orderByDesc('cf.created_at')
            ->get();

        return view('admin.user-service-versions', compact('user', 'service', 'versions'));
    }

    public function settings(): View
    {
        $this->syncStorageBaseUrlsToDatabase();

        $s3Runtime = $this->resolveS3RuntimeCredentials();
        $siteUrl = rtrim((string) config('app.url', ''), '/');
        $siteDomain = parse_url($siteUrl, PHP_URL_HOST) ?: $siteUrl;
        $sitePort = parse_url($siteUrl, PHP_URL_PORT);
        if (!empty($sitePort) && is_numeric($sitePort)) {
            $siteDomain .= ':' . $sitePort;
        }

        $organizationName = Organization::query()
            ->where('id', 200)
            ->value('name') ?? 'Default Organization';

        $localStorageBaseUrl = $this->resolveStorageBaseUrl('local');
        $s3StorageBaseUrl = $this->resolveStorageBaseUrl('s3');

        $useS3Storage = $this->isS3Enabled();
        $migrationDirection = (string) session('migration_direction', $useS3Storage ? 'local_to_s3' : 's3_to_local');
        if (!in_array($migrationDirection, ['local_to_s3', 's3_to_local'], true)) {
            $migrationDirection = $useS3Storage ? 'local_to_s3' : 's3_to_local';
        }
        $migrationKeepSource = (bool) session('migration_keep_source', true);
        $migrationAnalysis = session('migration_analysis');
        $migrationResult = session('migration_result');

        $siteFeatures = $this->getJsonSetting('site_features', []);
        $siteMetadata = $this->getJsonSetting('site_metadata', []);
        $siteTags = $this->getJsonSetting('site_tags', []);
        $mailRecipients = $this->getJsonSetting('mail_recipients', []);
        $ssoProviderOptions = config('sso.providers', []);
        $ssoSettings = $this->resolveSsoSettingsFromEnvironment($ssoProviderOptions);
        $ssoEnabledProviders = $ssoSettings['enabled_providers'];
        $ssoProviderUrls = $ssoSettings['provider_urls'];
        $ssoProviderClientIds = $ssoSettings['provider_client_ids'];
        $ssoProviderClientSecrets = $ssoSettings['provider_client_secrets'];
        $ssoProviderTenantIds = $ssoSettings['provider_tenant_ids'];

        $hasSsoProviderClientSecrets = collect($ssoProviderOptions)
            ->mapWithKeys(function ($meta, $providerKey) use ($ssoProviderClientSecrets) {
                return [$providerKey => !empty($ssoProviderClientSecrets[$providerKey] ?? '')];
            })
            ->all();

        return view('admin.settings', [
            'siteUrl' => $siteUrl,
            'siteDomain' => $siteDomain,
            'organizationName' => $organizationName,
            'siteLogoUrl' => $this->resolveSiteLogoUrl(),
            'siteLogoUrlOverride' => AdminSetting::getValue('site_logo_url', ''),
            'siteDescription' => AdminSetting::getValue('site_description', AdminSetting::getValue('site_content', '')),
            'siteContent' => AdminSetting::getValue('site_content', ''),
            'siteMetadataText' => json_encode($siteMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'siteTagsText' => implode(',', $siteTags),
            'siteFeaturesText' => implode("\n", $siteFeatures),
            'useS3Storage' => $useS3Storage,
            's3Region' => $s3Runtime['region'],
            's3Bucket' => $s3Runtime['bucket'],
            's3AccessKey' => $s3Runtime['key'],
            'localStorageBaseUrl' => $localStorageBaseUrl,
            's3StorageBaseUrl' => $s3StorageBaseUrl,
            'hasS3Secret' => $s3Runtime['secret'] !== '',
            'migrationDirection' => $migrationDirection,
            'migrationKeepSource' => $migrationKeepSource,
            'migrationAnalysis' => is_array($migrationAnalysis) ? $migrationAnalysis : null,
            'migrationResult' => is_array($migrationResult) ? $migrationResult : null,
            'migrationNotice' => (string) session('migration_notice', ''),
            'migrationStatus' => (string) AdminSetting::getValue('migration_status', ''),
            'migrationProgress' => $this->getJsonSetting('migration_progress', []),
            'mailHost' => AdminSetting::getValue('mail_host', ''),
            'mailPort' => AdminSetting::getValue('mail_port', ''),
            'mailUsername' => AdminSetting::getValue('mail_username', ''),
            'hasMailPassword' => AdminSetting::getValue('mail_password', null) !== null,
            'mailEncryption' => AdminSetting::getValue('mail_encryption', ''),
            'mailFromAddress' => AdminSetting::getValue('mail_from_address', ''),
            'mailFromName' => AdminSetting::getValue('mail_from_name', ''),
            'mailRecipientsText' => implode(',', $mailRecipients),
            'ssoEnabled' => $ssoSettings['enabled'],
            'ssoProvider' => $ssoEnabledProviders[0] ?? '',
            'ssoProviderOptions' => $ssoProviderOptions,
            'ssoEnabledProviders' => $ssoEnabledProviders,
            'ssoProviderUrls' => $ssoProviderUrls,
            'ssoProviderClientIds' => $ssoProviderClientIds,
            'hasSsoProviderClientSecrets' => $hasSsoProviderClientSecrets,
            'ssoProviderTenantIds' => $ssoProviderTenantIds,
            'ssoClientId' => $ssoProviderClientIds[$ssoEnabledProviders[0] ?? ''] ?? '',
            'hasSsoClientSecret' => !empty($ssoProviderClientSecrets[$ssoEnabledProviders[0] ?? ''] ?? ''),
            'ssoTenantId' => $ssoProviderTenantIds[$ssoEnabledProviders[0] ?? ''] ?? '',
            'ssoRedirectUrl' => $ssoProviderUrls[$ssoEnabledProviders[0] ?? ''] ?? '',
        ]);
    }

    public function updateSiteSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_logo_url' => ['nullable', 'url', 'max:2048'],
            'site_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'site_description' => ['nullable', 'string', 'max:5000'],
            'site_metadata' => ['nullable', 'string', 'max:20000'],
            'site_tags' => ['nullable', 'string', 'max:2000'],
            'site_features' => ['nullable', 'string'],
            'site_content' => ['nullable', 'string', 'max:5000'],
        ]);

        $metadata = [];
        if (!empty($validated['site_metadata'])) {
            $decodedMetadata = json_decode((string) $validated['site_metadata'], true);
            if (!is_array($decodedMetadata)) {
                return back()->withErrors([
                    'site_metadata' => 'Site metadata must be valid JSON object/array.',
                ])->withInput();
            }

            $metadata = $decodedMetadata;
        }

        $tags = collect(explode(',', (string) ($validated['site_tags'] ?? '')))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        $features = collect(explode("\n", (string) ($validated['site_features'] ?? '')))
            ->map(fn (string $feature) => trim($feature))
            ->filter()
            ->values()
            ->all();

        $logoUrl = trim((string) ($validated['site_logo_url'] ?? ''));
        if ($request->hasFile('site_logo')) {
            $storageDisk = $this->resolveStorageDisk();
            if ($storageDisk === 's3') {
                $path = $request->file('site_logo')->store('site-settings', ['disk' => 's3', 'visibility' => 'public']);
            } else {
                $path = $request->file('site_logo')->store('site-settings', $storageDisk);
            }
            AdminSetting::putValue('site', 'site_logo_disk', $storageDisk);
            AdminSetting::putValue('site', 'site_logo_path', (string) $path);
            $logoUrl = '';
        }

        AdminSetting::putValue('site', 'site_logo_url', $logoUrl);
        AdminSetting::putValue('site', 'site_description', $validated['site_description'] ?? '');
        AdminSetting::putValue('site', 'site_metadata', $metadata);
        AdminSetting::putValue('site', 'site_tags', $tags);
        AdminSetting::putValue('site', 'site_features', $features);
        AdminSetting::putValue('site', 'site_content', $validated['site_description'] ?? ($validated['site_content'] ?? ''));

        return redirect()->route('admin.settings', ['tab' => 'site'])->with('success', 'Site settings updated successfully.');
    }

    public function updateS3Settings(Request $request): RedirectResponse
    {
        $currentlyEnabled = $this->isS3Enabled();

        $validated = $request->validate([
            's3_enabled' => ['nullable', 'boolean'],
            's3_access_key' => ['required', 'string', 'max:255'],
            's3_secret_key' => ['nullable', 'string', 'max:255'],
            's3_region' => ['required', 'string', 'max:100'],
            's3_bucket' => ['required', 'string', 'max:255'],
        ]);

        $requestedEnabled = $request->boolean('s3_enabled');
        $runtimeCredentials = $this->resolveS3RuntimeCredentials();
        $resolvedSecret = trim((string) ($validated['s3_secret_key'] ?? ''));

        if ($resolvedSecret === '') {
            $resolvedSecret = $runtimeCredentials['secret'];
        }

        if ($requestedEnabled && $resolvedSecret === '') {
            return back()
                ->withErrors(['s3_secret_key' => 'S3 secret key is required when enabling S3.'])
                ->withInput();
        }

        if ($currentlyEnabled && !$requestedEnabled) {
            $analysis = $this->analyzeStorageMigrationDirection('s3_to_local');
            if (($analysis['files_pending_migration'] ?? 0) > 0) {
                return redirect()
                    ->route('admin.settings', ['tab' => 'migration'])
                    ->withErrors([
                        's3_enabled' => 'Before disabling S3, migrate data from S3 to local in the Migration tab.',
                    ])
                    ->with('migration_analysis', $analysis)
                    ->with('migration_direction', 's3_to_local');
            }
        }

        $siteUrl = rtrim((string) config('app.url', ''), '/');
        $localStorageBaseUrl = $siteUrl !== '' ? $siteUrl . '/storage' : '';
        $s3StorageBaseUrl = '';

        if ($requestedEnabled && trim($validated['s3_bucket']) !== '' && trim($validated['s3_region']) !== '') {
            $s3StorageBaseUrl = sprintf('https://%s.s3.%s.amazonaws.com/', trim($validated['s3_bucket']), trim($validated['s3_region']));
        }

        $this->setEnvironmentValues([
            'S3_ENABLED' => $requestedEnabled ? 'true' : 'false',
            'AWS_ACCESS_KEY_ID' => $validated['s3_access_key'],
            'AWS_SECRET_ACCESS_KEY' => $this->encryptSecretForEnvironment($resolvedSecret),
            'AWS_DEFAULT_REGION' => $validated['s3_region'],
            'AWS_BUCKET' => $validated['s3_bucket'],
            'AWS_URL' => $s3StorageBaseUrl,
            'LOCAL_STORAGE_BASE_URL' => $localStorageBaseUrl,
            'S3_STORAGE_BASE_URL' => $s3StorageBaseUrl,
            'VERSION' => (string) config('app.version', '0.1.0'),
        ]);

        Config::set('filesystems.disks.s3.key', $validated['s3_access_key']);
        Config::set('filesystems.disks.s3.secret', $resolvedSecret);
        Config::set('filesystems.disks.s3.region', $validated['s3_region']);
        Config::set('filesystems.disks.s3.bucket', $validated['s3_bucket']);
        Config::set('filesystems.disks.s3.url', $s3StorageBaseUrl);

        $this->syncStorageBaseUrlsToDatabase();

        if (!$currentlyEnabled && $requestedEnabled) {
            return redirect()
                ->route('admin.settings', ['tab' => 'migration'])
                ->with('success', 'S3 has been enabled. Go ahead and migrate existing local data to S3 from the Migration tab.')
                ->with('migration_notice', 'S3 is enabled. Run Local to S3 migration to move existing local data.')
                ->with('migration_direction', 'local_to_s3');
        }

        return redirect()->route('admin.settings', ['tab' => 's3'])->with('success', 'S3 settings saved successfully.');
    }

    public function analyzeMigration(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'direction' => ['required', Rule::in(['local_to_s3', 's3_to_local'])],
            'keep_source' => ['nullable', 'boolean'],
        ]);

        $analysis = $this->analyzeStorageMigrationDirection($validated['direction']);
        $keepSource = $request->boolean('keep_source');

        return redirect()
            ->route('admin.settings', ['tab' => 'migration'])
            ->with('migration_analysis', $analysis)
            ->with('migration_direction', $validated['direction'])
            ->with('migration_keep_source', $keepSource);
    }

    public function startMigration(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'direction' => ['required', Rule::in(['local_to_s3', 's3_to_local'])],
            'keep_source' => ['nullable', 'boolean'],
        ]);

        $keepSource = $request->boolean('keep_source');

        $analysis = $this->analyzeStorageMigrationDirection($validated['direction']);

        if (($analysis['files_pending_migration'] ?? 0) === 0) {
            return redirect()
                ->route('admin.settings', ['tab' => 'migration'])
                ->with('success', 'No files pending migration. Source and destination are already synchronized.')
                ->with('migration_result', [
                    'direction' => $validated['direction'],
                    'keep_source' => $keepSource,
                    'migrated_files' => 0,
                    'verified_files' => (int) ($analysis['source_files_found'] ?? 0),
                    'failed_verification' => 0,
                    'source_deleted_files' => 0,
                    'warnings' => [],
                    'progress_percent' => 100,
                ])
                ->with('migration_analysis', $analysis)
                ->with('migration_direction', $validated['direction'])
                ->with('migration_keep_source', $keepSource);
        }

        $result = $this->executeStorageMigration($validated['direction'], $analysis['entries'] ?? [], $keepSource);

        $postAnalysis = $this->analyzeStorageMigrationDirection($validated['direction']);

        if ($validated['direction'] === 's3_to_local' && (($postAnalysis['files_pending_migration'] ?? 0) === 0)) {
            $this->setEnvironmentValues(['S3_ENABLED' => 'false']);
        }

        if (!empty($result['errors'])) {
            return redirect()
                ->route('admin.settings', ['tab' => 'migration'])
                ->withErrors(['migration' => implode(' | ', $result['errors'])])
                ->with('migration_analysis', $postAnalysis)
                ->with('migration_result', $result)
                ->with('migration_direction', $validated['direction'])
                ->with('migration_keep_source', $keepSource);
        }

        if ($validated['direction'] === 'local_to_s3') {
            AdminSetting::putValue('storage', 'migration_local_to_s3_completed_at', now()->toDateTimeString());
            AdminSetting::putValue('site', 'site_logo_disk', 's3');
            if ($this->hasStorageDiskColumn()) {
                ConfigurationFile::query()->whereNotNull('file_location')->update(['storage_disk' => 's3']);
            }
        } else {
            AdminSetting::putValue('storage', 'migration_s3_to_local_completed_at', now()->toDateTimeString());
            $this->setEnvironmentValues(['S3_ENABLED' => 'false']);
            AdminSetting::putValue('site', 'site_logo_disk', 'public');
            if ($this->hasStorageDiskColumn()) {
                ConfigurationFile::query()->whereNotNull('file_location')->update(['storage_disk' => 'local']);
            }
        }

        return redirect()
            ->route('admin.settings', ['tab' => 'migration'])
            ->with('success', 'Migration completed successfully with verification.')
            ->with('migration_result', $result)
            ->with('migration_analysis', $postAnalysis)
                ->with('migration_direction', $validated['direction'])
                ->with('migration_keep_source', $keepSource);
    }

    public function serveSiteLogo(?string $path = null)
    {
        $storedPath = ltrim(trim((string) AdminSetting::getValue('site_logo_path', '')), '/');
        if ($storedPath === '') {
            abort(404, 'Site logo not configured.');
        }

        $requestedPath = ltrim(trim((string) ($path ?? '')), '/');
        if ($requestedPath !== '' && $requestedPath !== $storedPath) {
            abort(404, 'Logo not found.');
        }

        $activeDisk = $this->resolveStorageDisk();
        $fallbackDisk = strtolower(trim((string) AdminSetting::getValue('site_logo_disk', 'public')));
        if ($fallbackDisk === 'local') {
            $fallbackDisk = 'public';
        }

        $disk = $activeDisk;
        if (!Storage::disk($disk)->exists($storedPath) && $fallbackDisk !== '' && Storage::disk($fallbackDisk)->exists($storedPath)) {
            $disk = $fallbackDisk;
        }

        if (!Storage::disk($disk)->exists($storedPath)) {
            abort(404, 'Logo file is missing in storage.');
        }

        $content = Storage::disk($disk)->get($storedPath);
        $extension = strtolower(pathinfo($storedPath, PATHINFO_EXTENSION));
        $mimeMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'gif' => 'image/gif',
        ];
        $mimeType = $mimeMap[$extension] ?? 'application/octet-stream';

        return response($content, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=300');
    }

    public function updateMailSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_host' => ['required', 'string', 'max:255'],
            'mail_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['required', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', Rule::in(['tls', 'ssl', 'starttls'])],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
            'mail_recipients' => ['required', 'string', 'max:2000'],
        ]);

        $recipientList = collect(explode(',', $validated['mail_recipients']))
            ->map(fn (string $email) => trim($email))
            ->filter()
            ->values();

        foreach ($recipientList as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return back()->withErrors([
                    'mail_recipients' => 'Every recipient must be a valid email address.',
                ])->withInput();
            }
        }

        AdminSetting::putValue('mail', 'mail_host', $validated['mail_host']);
        AdminSetting::putValue('mail', 'mail_port', (string) $validated['mail_port']);
        AdminSetting::putValue('mail', 'mail_username', $validated['mail_username']);

        if (!empty($validated['mail_password'])) {
            AdminSetting::putValue('mail', 'mail_password', $validated['mail_password'], true);
        }

        AdminSetting::putValue('mail', 'mail_encryption', $validated['mail_encryption'] ?? '');
        AdminSetting::putValue('mail', 'mail_from_address', $validated['mail_from_address']);
        AdminSetting::putValue('mail', 'mail_from_name', $validated['mail_from_name']);
        AdminSetting::putValue('mail', 'mail_recipients', $recipientList->all());

        return redirect()->route('admin.settings', ['tab' => 'email'])->with('success', 'Mail settings saved successfully.');
    }

    public function updateSsoSettings(Request $request): RedirectResponse
    {
        $providerOptions = config('sso.providers', []);
        $providerKeys = array_keys($providerOptions);

        $validated = $request->validate([
            'sso_enabled' => ['nullable', 'boolean'],
            'sso_enabled_providers' => ['nullable', 'array'],
            'sso_enabled_providers.*' => [Rule::in($providerKeys)],
            'sso_provider_urls' => ['nullable', 'array'],
            'sso_provider_urls.*' => ['nullable', 'url', 'max:2048'],
            'sso_provider_client_ids' => ['nullable', 'array'],
            'sso_provider_client_ids.*' => ['nullable', 'string', 'max:255'],
            'sso_provider_client_secrets' => ['nullable', 'array'],
            'sso_provider_client_secrets.*' => ['nullable', 'string', 'max:255'],
            'sso_provider_tenant_ids' => ['nullable', 'array'],
            'sso_provider_tenant_ids.*' => ['nullable', 'string', 'max:255'],
            'sso_client_id' => ['nullable', 'string', 'max:255'],
            'sso_client_secret' => ['nullable', 'string', 'max:255'],
            'sso_tenant_id' => ['nullable', 'string', 'max:255'],
            'sso_redirect_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $enabledProviders = collect($request->input('sso_enabled_providers', []))
            ->map(fn ($provider) => strtolower(trim((string) $provider)))
            ->filter(fn ($provider) => in_array($provider, $providerKeys, true))
            ->unique()
            ->values()
            ->all();

        $effectiveSsoEnabled = $request->boolean('sso_enabled') || !empty($enabledProviders);

        if ($effectiveSsoEnabled && empty($enabledProviders)) {
            return back()->withErrors([
                'sso_enabled_providers' => 'Select at least one SSO provider when SSO is enabled.',
            ])->withInput();
        }

        $providerUrls = collect($request->input('sso_provider_urls', []))
            ->mapWithKeys(function ($url, $provider) use ($providerKeys) {
                $provider = strtolower(trim((string) $provider));
                if (!in_array($provider, $providerKeys, true)) {
                    return [];
                }

                return [$provider => trim((string) $url)];
            })
            ->all();

        $providerClientIds = collect($request->input('sso_provider_client_ids', []))
            ->mapWithKeys(function ($clientId, $provider) use ($providerKeys) {
                $provider = strtolower(trim((string) $provider));
                if (!in_array($provider, $providerKeys, true)) {
                    return [];
                }

                return [$provider => trim((string) $clientId)];
            })
            ->all();

        $providerTenantIds = collect($request->input('sso_provider_tenant_ids', []))
            ->mapWithKeys(function ($tenantId, $provider) use ($providerKeys) {
                $provider = strtolower(trim((string) $provider));
                if (!in_array($provider, $providerKeys, true)) {
                    return [];
                }

                return [$provider => trim((string) $tenantId)];
            })
            ->all();

        $existingProviderClientSecrets = [];
        foreach ($providerKeys as $providerKey) {
            $existingProviderClientSecrets[$providerKey] = $this->getSecretEnvValue($this->providerEnvKeyPrefix($providerKey) . '_CLIENT_SECRET', '');
        }
        $providerClientSecrets = [];
        foreach ($providerKeys as $providerKey) {
            $incomingSecret = trim((string) $request->input("sso_provider_client_secrets.$providerKey", ''));
            if ($incomingSecret !== '') {
                $providerClientSecrets[$providerKey] = $incomingSecret;
                continue;
            }

            $existingSecret = trim((string) ($existingProviderClientSecrets[$providerKey] ?? ''));
            if ($existingSecret !== '') {
                $providerClientSecrets[$providerKey] = $existingSecret;
            }
        }

        $envUpdates = [
            'SSO_ENABLED' => $effectiveSsoEnabled ? 'true' : 'false',
            'SSO_ENABLED_PROVIDERS' => implode(',', $enabledProviders),
        ];

        foreach ($providerKeys as $providerKey) {
            $prefix = $this->providerEnvKeyPrefix($providerKey);
            $envUpdates[$prefix . '_URL'] = (string) ($providerUrls[$providerKey] ?? '');
            $envUpdates[$prefix . '_CLIENT_ID'] = (string) ($providerClientIds[$providerKey] ?? '');
            $envUpdates[$prefix . '_CLIENT_SECRET'] = $this->encryptSecretForEnvironment((string) ($providerClientSecrets[$providerKey] ?? ''));
            $envUpdates[$prefix . '_TENANT_ID'] = (string) ($providerTenantIds[$providerKey] ?? '');
        }

        $this->setEnvironmentValues($envUpdates);

        return redirect()->route('admin.settings', ['tab' => 'sso'])->with('success', 'SSO settings saved successfully.');
    }

    private function getJsonSetting(string $key, array $default): array
    {
        $value = AdminSetting::getValue($key, null);

        if ($value === null || $value === '') {
            return $default;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : $default;
    }

    private function resolveSsoSettingsFromEnvironment(array $providerOptions): array
    {
        $providerKeys = array_keys($providerOptions);
        $enabledProviders = $this->resolveEnabledSsoProvidersFromEnvironment($providerKeys);

        $providerUrls = [];
        $providerClientIds = [];
        $providerClientSecrets = [];
        $providerTenantIds = [];

        foreach ($providerKeys as $providerKey) {
            $prefix = $this->providerEnvKeyPrefix($providerKey);
            $providerUrls[$providerKey] = $this->getEnvValue($prefix . '_URL', '');
            $providerClientIds[$providerKey] = $this->getEnvValue($prefix . '_CLIENT_ID', '');
            $providerClientSecrets[$providerKey] = $this->getSecretEnvValue($prefix . '_CLIENT_SECRET', '');
            $providerTenantIds[$providerKey] = $this->getEnvValue($prefix . '_TENANT_ID', '');
        }

        $ssoEnabled = filter_var($this->getEnvValue('SSO_ENABLED', 'false'), FILTER_VALIDATE_BOOL) || !empty($enabledProviders);

        return [
            'enabled' => $ssoEnabled,
            'enabled_providers' => $enabledProviders,
            'provider_urls' => $providerUrls,
            'provider_client_ids' => $providerClientIds,
            'provider_client_secrets' => $providerClientSecrets,
            'provider_tenant_ids' => $providerTenantIds,
        ];
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

    private function providerEnvKeyPrefix(string $provider): string
    {
        $normalized = strtoupper((string) preg_replace('/[^A-Za-z0-9]+/', '_', strtolower(trim($provider))));
        $normalized = trim($normalized, '_');

        return 'SSO_' . $normalized;
    }

    private function resolveStorageDisk(): string
    {
        $s3Enabled = $this->isS3Enabled();

        if (!$s3Enabled) {
            return 'public';
        }

        $credentials = $this->resolveS3RuntimeCredentials();
        $key = $credentials['key'];
        $secret = $credentials['secret'];
        $region = $credentials['region'];
        $bucket = $credentials['bucket'];

        if ($key === '' || $secret === '' || $region === '' || $bucket === '') {
            return 'public';
        }

        Config::set('filesystems.disks.s3.key', $key);
        Config::set('filesystems.disks.s3.secret', $secret);
        Config::set('filesystems.disks.s3.region', $region);
        Config::set('filesystems.disks.s3.bucket', $bucket);

        return 's3';
    }

    private function resolveSiteLogoUrl(): string
    {
        $overrideUrl = trim((string) AdminSetting::getValue('site_logo_url', ''));
        if ($overrideUrl !== '') {
            return $overrideUrl;
        }

        $storedPath = trim((string) AdminSetting::getValue('site_logo_path', ''));
        if ($storedPath === '') {
            return '';
        }

        return $this->buildStorageObjectUrl($this->resolveStorageDisk(), $storedPath);
    }

    private function buildStorageObjectUrl(string $disk, string $path): string
    {
        $normalizedPath = ltrim($path, '/');
        if ($normalizedPath === '') {
            return '';
        }

        if ($disk === 's3') {
            return route('site.logo', ['path' => $normalizedPath]);
        }

        $baseUrl = $this->resolveStorageBaseUrl($disk);

        if ($baseUrl !== '') {
            return rtrim($baseUrl, '/') . '/' . $normalizedPath;
        }

        if (in_array($disk, ['public', 'local'], true)) {
            return Storage::url($normalizedPath);
        }

        if ($disk === 's3') {
            return $this->buildS3ObjectUrl($normalizedPath);
        }

        return $normalizedPath;
    }

    private function resolveStorageBaseUrl(string $disk): string
    {
        $normalizedDisk = strtolower(trim($disk));

        if ($normalizedDisk === 's3') {
            $configured = trim($this->getEnvValue('S3_STORAGE_BASE_URL', ''));
            if ($configured !== '') {
                return $configured;
            }

            $credentials = $this->resolveS3RuntimeCredentials();
            if ($credentials['bucket'] !== '' && $credentials['region'] !== '') {
                return sprintf('https://%s.s3.%s.amazonaws.com/', $credentials['bucket'], $credentials['region']);
            }

            return '';
        }

        $configured = trim($this->getEnvValue('LOCAL_STORAGE_BASE_URL', ''));
        if ($configured !== '') {
            return $configured;
        }

        $siteUrl = rtrim((string) config('app.url', ''), '/');

        return $siteUrl !== '' ? $siteUrl . '/storage' : '';
    }

    private function syncStorageBaseUrlsToDatabase(): void
    {
        $siteUrl = rtrim((string) config('app.url', ''), '/');
        $localStorageBaseUrl = $siteUrl !== '' ? $siteUrl . '/storage' : '';

        $s3Enabled = $this->isS3Enabled();
        $credentials = $this->resolveS3RuntimeCredentials();
        $bucket = $credentials['bucket'];
        $region = $credentials['region'];
        $s3StorageBaseUrl = '';

        if ($s3Enabled && $bucket !== '' && $region !== '') {
            $s3StorageBaseUrl = sprintf('https://%s.s3.%s.amazonaws.com/', $bucket, $region);
        }

        $this->setEnvironmentValues([
            'LOCAL_STORAGE_BASE_URL' => $localStorageBaseUrl,
            'S3_STORAGE_BASE_URL' => $s3StorageBaseUrl,
            'VERSION' => (string) config('app.version', '0.1.0'),
        ]);

        AdminSetting::putValue('storage', 'site_url', $siteUrl);
    }

    private function analyzeStorageMigrationDirection(string $direction): array
    {
        $entries = $this->buildMigrationEntries($direction);

        $sourceFilesFound = 0;
        $filesPendingMigration = 0;
        $missingSourceFiles = 0;
        $folders = [];
        $localFilesFound = 0;
        $s3FilesFound = 0;

        foreach ($entries as &$entry) {
            $sourceExists = Storage::disk($entry['source_disk'])->exists($entry['path']);
            $destinationExists = Storage::disk($entry['destination_disk'])->exists($entry['path']);

            if (Storage::disk('local')->exists($entry['path']) || Storage::disk('public')->exists($entry['path'])) {
                $localFilesFound++;
            }
            if (Storage::disk('s3')->exists($entry['path'])) {
                $s3FilesFound++;
            }

            $entry['source_exists'] = $sourceExists;
            $entry['destination_exists'] = $destinationExists;

            if (!$sourceExists) {
                $missingSourceFiles++;
                continue;
            }

            $sourceFilesFound++;

            if (!$destinationExists) {
                $filesPendingMigration++;
                $folders[dirname($entry['path'])] = true;
            }
        }

        return [
            'direction' => $direction,
            'total_tracked_files' => count($entries),
            'source_files_found' => $sourceFilesFound,
            'missing_source_files' => $missingSourceFiles,
            'files_pending_migration' => $filesPendingMigration,
            'folders_pending_migration' => count(array_filter(array_keys($folders), fn ($folder) => $folder !== '.' && $folder !== '')),
            'local_files_found' => $localFilesFound,
            's3_files_found' => $s3FilesFound,
            'entries' => $entries,
        ];
    }

    private function executeStorageMigration(string $direction, array $entries, bool $keepSource = true): array
    {
        $errors = [];
        $warnings = [];
        $migrated = 0;
        $verified = 0;
        $failedVerification = 0;
        $sourceDeleted = 0;
        $processed = 0;
        $total = count($entries);

        AdminSetting::putValue('storage', 'migration_status', 'in_progress');
        AdminSetting::putValue('storage', 'migration_progress', [
            'processed' => 0,
            'total' => $total,
            'percent' => 0,
            'direction' => $direction,
            'started_at' => now()->toDateTimeString(),
        ]);

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $path = (string) ($entry['path'] ?? '');
            $sourceDisk = (string) ($entry['source_disk'] ?? '');
            $destinationDisk = (string) ($entry['destination_disk'] ?? '');

            if ($path === '' || $sourceDisk === '' || $destinationDisk === '') {
                $processed++;
                continue;
            }

            $sourceExists = Storage::disk($sourceDisk)->exists($path);
            $destinationExists = Storage::disk($destinationDisk)->exists($path);

            if (!$sourceExists) {
                $warnings[] = "Missing source file (skipped): {$path} on {$sourceDisk}";
                $processed++;
                AdminSetting::putValue('storage', 'migration_progress', [
                    'processed' => $processed,
                    'total' => $total,
                    'percent' => $total > 0 ? (int) floor(($processed / $total) * 100) : 100,
                    'direction' => $direction,
                    'started_at' => now()->toDateTimeString(),
                ]);
                continue;
            }

            if (!$destinationExists) {
                try {
                    $stream = Storage::disk($sourceDisk)->readStream($path);
                    if ($stream === false) {
                        $errors[] = "Cannot read source stream: {$path}";
                        $processed++;
                        continue;
                    }

                    $writeOptions = [];
                    if ($destinationDisk === 's3') {
                        $writeOptions['visibility'] = 'public';
                    }

                    $written = Storage::disk($destinationDisk)->writeStream($path, $stream, $writeOptions);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }

                    if (!$written) {
                        $errors[] = "Cannot write destination stream: {$path}";
                        $processed++;
                        continue;
                    }

                    $migrated++;
                } catch (\Throwable $e) {
                    $errors[] = "Migration failed for {$path}: {$e->getMessage()}";
                    $processed++;
                    continue;
                }
            }

            try {
                if (!Storage::disk($destinationDisk)->exists($path)) {
                    $failedVerification++;
                    $errors[] = "Verification failed (destination missing): {$path}";
                    continue;
                }

                $sourceChecksum = md5((string) Storage::disk($sourceDisk)->get($path));
                $destinationChecksum = md5((string) Storage::disk($destinationDisk)->get($path));

                if ($sourceChecksum !== $destinationChecksum) {
                    $failedVerification++;
                    $errors[] = "Verification checksum mismatch: {$path}";
                    continue;
                }

                $verified++;

                if (!$keepSource && $sourceDisk !== $destinationDisk) {
                    try {
                        if (Storage::disk($sourceDisk)->delete($path)) {
                            $sourceDeleted++;
                        } else {
                            $warnings[] = "Could not delete source file after migration: {$path} ({$sourceDisk})";
                        }
                    } catch (\Throwable $e) {
                        $warnings[] = "Source delete error for {$path}: {$e->getMessage()}";
                    }
                }
            } catch (\Throwable $e) {
                $failedVerification++;
                $errors[] = "Verification error for {$path}: {$e->getMessage()}";
            }

            $processed++;
            AdminSetting::putValue('storage', 'migration_progress', [
                'processed' => $processed,
                'total' => $total,
                'percent' => $total > 0 ? (int) floor(($processed / $total) * 100) : 100,
                'direction' => $direction,
                'started_at' => now()->toDateTimeString(),
            ]);
        }

        AdminSetting::putValue('storage', 'migration_status', empty($errors) ? 'completed' : 'completed_with_errors');
        AdminSetting::putValue('storage', 'migration_progress', [
            'processed' => $processed,
            'total' => $total,
            'percent' => 100,
            'direction' => $direction,
            'started_at' => now()->toDateTimeString(),
            'completed_at' => now()->toDateTimeString(),
        ]);

        return [
            'direction' => $direction,
            'keep_source' => $keepSource,
            'migrated_files' => $migrated,
            'verified_files' => $verified,
            'failed_verification' => $failedVerification,
            'source_deleted_files' => $sourceDeleted,
            'processed_files' => $processed,
            'total_files' => $total,
            'progress_percent' => 100,
            'warnings' => $warnings,
            'errors' => $errors,
        ];
    }

    private function buildMigrationEntries(string $direction): array
    {
        $entries = [];
        $seen = [];

        $logoPath = trim((string) AdminSetting::getValue('site_logo_path', ''));
        if ($logoPath !== '') {
            $logoEntry = $direction === 'local_to_s3'
                ? ['type' => 'logo', 'path' => ltrim($logoPath, '/'), 'source_disk' => 'public', 'destination_disk' => 's3']
                : ['type' => 'logo', 'path' => ltrim($logoPath, '/'), 'source_disk' => 's3', 'destination_disk' => 'public'];

            $key = $logoEntry['type'] . '|' . $logoEntry['path'] . '|' . $logoEntry['source_disk'] . '|' . $logoEntry['destination_disk'];
            $entries[] = $logoEntry;
            $seen[$key] = true;
        }

        $configPaths = ConfigurationFile::query()
            ->whereNotNull('file_location')
            ->where('file_location', '!=', '')
            ->pluck('file_location')
            ->all();

        foreach ($configPaths as $configPath) {
            $normalizedPath = ltrim((string) $configPath, '/');
            if ($normalizedPath === '') {
                continue;
            }

            $entry = $direction === 'local_to_s3'
                ? ['type' => 'configuration_file', 'path' => $normalizedPath, 'source_disk' => 'local', 'destination_disk' => 's3']
                : ['type' => 'configuration_file', 'path' => $normalizedPath, 'source_disk' => 's3', 'destination_disk' => 'local'];

            $key = $entry['type'] . '|' . $entry['path'] . '|' . $entry['source_disk'] . '|' . $entry['destination_disk'];
            if (isset($seen[$key])) {
                continue;
            }

            $entries[] = $entry;
            $seen[$key] = true;
        }

        if ($this->configureS3DiskFromSettings() === false && in_array($direction, ['local_to_s3', 's3_to_local'], true)) {
            return [];
        }

        return $entries;
    }

    private function configureS3DiskFromSettings(): bool
    {
        $credentials = $this->resolveS3RuntimeCredentials();
        $key = $credentials['key'];
        $secret = $credentials['secret'];
        $region = $credentials['region'];
        $bucket = $credentials['bucket'];

        if ($key === '' || $secret === '' || $region === '' || $bucket === '') {
            return false;
        }

        Config::set('filesystems.disks.s3.key', $key);
        Config::set('filesystems.disks.s3.secret', $secret);
        Config::set('filesystems.disks.s3.region', $region);
        Config::set('filesystems.disks.s3.bucket', $bucket);

        return true;
    }

    private function resolveS3RuntimeCredentials(): array
    {
        $key = trim((string) config('filesystems.disks.s3.key', $this->getEnvValue('AWS_ACCESS_KEY_ID', '')));
        $secret = trim((string) config('filesystems.disks.s3.secret', $this->getSecretEnvValue('AWS_SECRET_ACCESS_KEY', '')));
        $region = trim((string) config('filesystems.disks.s3.region', $this->getEnvValue('AWS_DEFAULT_REGION', '')));
        $bucket = trim((string) config('filesystems.disks.s3.bucket', $this->getEnvValue('AWS_BUCKET', '')));

        return [
            'key' => $key,
            'secret' => $secret,
            'region' => $region,
            'bucket' => $bucket,
        ];
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

    private function getSecretEnvValue(string $key, string $default = ''): string
    {
        return $this->decryptSecretFromEnvironment($this->getEnvValue($key, $default));
    }

    private function isS3Enabled(): bool
    {
        return filter_var($this->getEnvValue('S3_ENABLED', 'false'), FILTER_VALIDATE_BOOL);
    }

    private function encryptSecretForEnvironment(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        if (str_starts_with($trimmed, 'ENC:')) {
            return $trimmed;
        }

        return 'ENC:' . Crypt::encryptString($trimmed);
    }

    private function decryptSecretFromEnvironment(string $value): string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return '';
        }

        if (!str_starts_with($trimmed, 'ENC:')) {
            return $trimmed;
        }

        try {
            return trim(Crypt::decryptString(substr($trimmed, 4)));
        } catch (\Throwable) {
            return '';
        }
    }

    private function setEnvironmentValues(array $values): void
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath) || !File::isWritable($envPath)) {
            return;
        }

        $contents = File::get($envPath);
        $originalContents = $contents;
        $effectiveUpdates = [];

        foreach ($values as $key => $rawValue) {
            $value = trim((string) ($rawValue ?? ''));
            $line = $key . '=' . $this->formatEnvValue($value);
            $pattern = "/^" . preg_quote($key, '/') . "=.*/m";
            $existingValue = $this->getEnvValue($key, '');

            if (preg_match($pattern, $contents) === 1) {
                $contents = preg_replace($pattern, $line, $contents, 1) ?? $contents;
            } else {
                $contents = rtrim($contents, "\r\n") . PHP_EOL . $line . PHP_EOL;
            }

            if ($existingValue !== $value) {
                $effectiveUpdates[$key] = $value;
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        if ($contents !== $originalContents) {
            File::put($envPath, $contents);
        }

        foreach ($effectiveUpdates as $key => $value) {
            putenv($key . '=' . $value);
        }
    }

    private function formatEnvValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"/', $value) === 1) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        return $value;
    }

    private function hasStorageDiskColumn(): bool
    {
        static $hasColumn;

        if ($hasColumn !== null) {
            return $hasColumn;
        }

        $hasColumn = DB::getSchemaBuilder()->hasColumn('configuration_files', 'storage_disk');

        return $hasColumn;
    }

    private function buildS3ObjectUrl(string $path): string
    {
        $customUrl = trim((string) config('filesystems.disks.s3.url', ''));
        if ($customUrl !== '') {
            return rtrim($customUrl, '/') . '/' . ltrim($path, '/');
        }

        $bucket = trim((string) config('filesystems.disks.s3.bucket', ''));
        $region = trim((string) config('filesystems.disks.s3.region', ''));

        if ($bucket === '' || $region === '') {
            return $path;
        }

        return sprintf('https://%s.s3.%s.amazonaws.com/%s', $bucket, $region, ltrim($path, '/'));
    }
}
