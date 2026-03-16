<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\ConfigurationFile;
use App\Models\Service;
use App\Models\SystemRegister;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->whereIn('users.rbac_id', [100, 101])
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
        $siteFeatures = $this->getJsonSetting('site_features', []);
        $siteMetadata = $this->getJsonSetting('site_metadata', []);
        $siteTags = $this->getJsonSetting('site_tags', []);
        $mailRecipients = $this->getJsonSetting('mail_recipients', []);

        return view('admin.settings', [
            'siteLogoUrl' => AdminSetting::getValue('site_logo_url', ''),
            'siteDescription' => AdminSetting::getValue('site_description', AdminSetting::getValue('site_content', '')),
            'siteContent' => AdminSetting::getValue('site_content', ''),
            'siteMetadataText' => json_encode($siteMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'siteTagsText' => implode(',', $siteTags),
            'siteFeaturesText' => implode("\n", $siteFeatures),
            'useS3Storage' => (bool) AdminSetting::getValue('s3_enabled', false),
            's3Region' => AdminSetting::getValue('s3_region', ''),
            's3Bucket' => AdminSetting::getValue('s3_bucket', ''),
            's3AccessKey' => AdminSetting::getValue('s3_access_key', ''),
            'hasS3Secret' => AdminSetting::getValue('s3_secret_key', null) !== null,
            'mailHost' => AdminSetting::getValue('mail_host', ''),
            'mailPort' => AdminSetting::getValue('mail_port', ''),
            'mailUsername' => AdminSetting::getValue('mail_username', ''),
            'hasMailPassword' => AdminSetting::getValue('mail_password', null) !== null,
            'mailEncryption' => AdminSetting::getValue('mail_encryption', ''),
            'mailFromAddress' => AdminSetting::getValue('mail_from_address', ''),
            'mailFromName' => AdminSetting::getValue('mail_from_name', ''),
            'mailRecipientsText' => implode(',', $mailRecipients),
            'ssoEnabled' => (bool) AdminSetting::getValue('sso_enabled', false),
            'ssoProvider' => AdminSetting::getValue('sso_provider', ''),
            'ssoClientId' => AdminSetting::getValue('sso_client_id', ''),
            'hasSsoClientSecret' => AdminSetting::getValue('sso_client_secret', null) !== null,
            'ssoTenantId' => AdminSetting::getValue('sso_tenant_id', ''),
            'ssoRedirectUrl' => AdminSetting::getValue('sso_redirect_url', ''),
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

        $logoUrl = (string) ($validated['site_logo_url'] ?? '');
        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('site-settings', 'public');
            $logoUrl = Storage::url($path);
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
        $validated = $request->validate([
            's3_enabled' => ['nullable', 'boolean'],
            's3_access_key' => ['required', 'string', 'max:255'],
            's3_secret_key' => ['nullable', 'string', 'max:255'],
            's3_region' => ['required', 'string', 'max:100'],
            's3_bucket' => ['required', 'string', 'max:255'],
        ]);

        AdminSetting::putValue('storage', 's3_enabled', $request->boolean('s3_enabled'));
        AdminSetting::putValue('storage', 's3_access_key', $validated['s3_access_key']);

        if (!empty($validated['s3_secret_key'])) {
            AdminSetting::putValue('storage', 's3_secret_key', $validated['s3_secret_key'], true);
        }

        AdminSetting::putValue('storage', 's3_region', $validated['s3_region']);
        AdminSetting::putValue('storage', 's3_bucket', $validated['s3_bucket']);

        return redirect()->route('admin.settings', ['tab' => 's3'])->with('success', 'S3 settings saved successfully.');
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
        $validated = $request->validate([
            'sso_enabled' => ['nullable', 'boolean'],
            'sso_provider' => ['required', Rule::in(['azure-ad', 'okta', 'google', 'auth0', 'custom'])],
            'sso_client_id' => ['required', 'string', 'max:255'],
            'sso_client_secret' => ['nullable', 'string', 'max:255'],
            'sso_tenant_id' => ['nullable', 'string', 'max:255'],
            'sso_redirect_url' => ['required', 'url', 'max:2048'],
        ]);

        AdminSetting::putValue('sso', 'sso_enabled', $request->boolean('sso_enabled'));
        AdminSetting::putValue('sso', 'sso_provider', $validated['sso_provider']);
        AdminSetting::putValue('sso', 'sso_client_id', $validated['sso_client_id']);

        if (!empty($validated['sso_client_secret'])) {
            AdminSetting::putValue('sso', 'sso_client_secret', $validated['sso_client_secret'], true);
        }

        AdminSetting::putValue('sso', 'sso_tenant_id', $validated['sso_tenant_id'] ?? '');
        AdminSetting::putValue('sso', 'sso_redirect_url', $validated['sso_redirect_url']);

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
}
