<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Complete</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sora', 'ui-sans-serif', 'system-ui']
                    },
                    boxShadow: {
                        glow: '0 20px 60px -30px rgba(16, 185, 129, 0.65)'
                    }
                }
            }
        };
    </script>
    <style>
        body {
            font-family: 'Sora', ui-sans-serif, system-ui;
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-950">
    <div class="fixed inset-0 -z-10">
        <div class="absolute -top-24 -left-20 h-72 w-72 rounded-full bg-cyan-300/35 blur-3xl"></div>
        <div class="absolute -bottom-20 -right-20 h-80 w-80 rounded-full bg-emerald-300/35 blur-3xl"></div>
        <div class="absolute top-1/3 left-1/2 h-64 w-64 -translate-x-1/2 rounded-full bg-yellow-200/25 blur-3xl"></div>
        <div class="h-full w-full bg-gradient-to-br from-slate-950 via-teal-950 to-slate-900"></div>
    </div>

    <div class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-5 py-8 sm:px-8">
        <div class="w-full max-w-4xl rounded-3xl border border-white/20 bg-white/85 p-6 shadow-glow backdrop-blur-xl sm:p-10">
            <div class="mb-6 flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-cyan-500 text-lg font-extrabold text-white shadow-lg shadow-emerald-500/30">
                    AG
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-emerald-700 sm:text-3xl">Installation Completed</h1>
                </div>
            </div>

            <p class="text-sm text-slate-600 sm:text-base">Your AtGlance application is ready. Please use the details below to sign in and continue setup.</p>

            <div class="mt-6 grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white/80 p-4">
                    <p class="font-semibold text-slate-800">Organization Name</p>
                    <p class="mt-1 text-slate-700">{{ $installation['organization_name'] ?? 'Default Organization' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white/80 p-4">
                    <p class="font-semibold text-slate-800">Domain</p>
                    <p class="mt-1 text-slate-700">{{ $installation['app_domain'] ?? parse_url(($installation['app_url'] ?? url('/')), PHP_URL_HOST) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white/80 p-4">
                    <p class="font-semibold text-slate-800">HTTPS Enabled</p>
                    <p class="mt-1 text-slate-700">{{ ($installation['https_enabled'] ?? false) ? 'Yes' : 'No' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white/80 p-4">
                    <p class="font-semibold text-slate-800">Project URL</p>
                    <p class="mt-1 text-slate-700">{{ $installation['app_url'] ?? url('/') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white/80 p-4">
                    <p class="font-semibold text-slate-800">Super Admin Username</p>
                    <p class="mt-1 text-slate-700">{{ $installation['superadmin_email'] ?? 'superadmin@admin.com' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white/80 p-4">
                    <p class="font-semibold text-slate-800">Super Admin Password</p>
                    <p class="mt-1 text-slate-700">{{ $installation['superadmin_password'] ?? 'Atglance@123' }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-cyan-200 bg-cyan-50/75 p-4 text-sm text-slate-700 sm:p-5">
                <p class="font-semibold text-cyan-700">Next steps for SSO integration</p>
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
                class="mt-6 inline-flex items-center rounded-xl bg-gradient-to-r from-emerald-600 to-cyan-600 px-5 py-3 text-sm font-bold uppercase tracking-wider text-white transition hover:from-emerald-700 hover:to-cyan-700"
            >
                Go to Login
            </a>
        </div>
    </div>
</body>
</html>