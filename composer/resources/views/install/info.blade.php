<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Complete</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100">
    <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
            <h1 class="text-2xl font-semibold text-green-700">Installation Completed</h1>
            <p class="text-sm text-slate-600 mt-2">Your application is installed successfully. Use the details below to log in and continue setup.</p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="font-semibold text-slate-800">Organization Name</p>
                    <p class="mt-1 text-slate-700">{{ $installation['organization_name'] ?? 'Default Organization' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="font-semibold text-slate-800">Domain</p>
                    <p class="mt-1 text-slate-700">{{ $installation['app_domain'] ?? parse_url(($installation['app_url'] ?? url('/')), PHP_URL_HOST) }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="font-semibold text-slate-800">HTTPS Enabled</p>
                    <p class="mt-1 text-slate-700">{{ ($installation['https_enabled'] ?? false) ? 'Yes' : 'No' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="font-semibold text-slate-800">Project URL</p>
                    <p class="mt-1 text-slate-700">{{ $installation['app_url'] ?? url('/') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="font-semibold text-slate-800">Super Admin Username</p>
                    <p class="mt-1 text-slate-700">{{ $installation['superadmin_email'] ?? 'superadmin@admin.com' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="font-semibold text-slate-800">Super Admin Password</p>
                    <p class="mt-1 text-slate-700">{{ $installation['superadmin_password'] ?? 'Atglance@123' }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm text-slate-700">
                <p class="font-semibold text-indigo-700">Next steps for SSO integration</p>
                <ol class="mt-2 list-decimal pl-5 space-y-1">
                    <li>Login with the super admin account.</li>
                    <li>Open <span class="font-medium">Admin Settings</span> and navigate to SSO settings.</li>
                    <li>Enable desired providers and add client ID/secret values.</li>
                    <li>Set provider callback URLs to: <span class="font-medium">/auth/sso/{provider}/callback</span>.</li>
                    <li>Save settings and test SSO login from the sign-in page.</li>
                </ol>
            </div>

            <a
                href="{{ route('home') }}"
                class="mt-6 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
            >
                Go to Login
            </a>
        </div>
    </div>
</body>
</html>