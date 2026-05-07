<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Complete</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui']
                    },
                    boxShadow: {
                        glow: '0 20px 60px -30px rgba(31, 41, 55, 0.35)'
                    }
                }
            }
        };
    </script>
    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui;
            background: radial-gradient(circle at top right, rgba(103, 232, 249, 0.2), transparent 45%), #f7fafc;
        }

        .install-cta {
            background: linear-gradient(135deg, #38BDF8 0%, #67E8F9 100%);
            color: #fff;
            box-shadow: 0 10px 24px rgba(56, 189, 248, 0.3);
            transition: all 0.3s ease;
        }

        .install-cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(56, 189, 248, 0.35);
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden bg-white">
    <div class="fixed inset-0 -z-10">
        <div class="h-full w-full bg-white"></div>
    </div>

    <div class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-5 py-8 sm:px-8">
        <div class="w-full max-w-4xl rounded-3xl border border-slate-200 bg-white p-6 shadow-lg sm:p-10">
            <div class="mb-6 flex items-center gap-4">
                <img
                    src="{{ asset('branding/atglance-logo.png') }}"
                    alt="AtGlance logo"
                    class="h-14 w-auto object-contain"
                >
                <div>
                    <h1 class="text-2xl font-extrabold text-black sm:text-3xl">Installation Completed</h1>
                </div>
            </div>

            <p class="text-sm text-slate-600 sm:text-base">Your AtGlance application is ready. Please use the details below to sign in and continue setup.</p>

            <div class="mt-6 grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="font-semibold text-slate-800">Organization Name</p>
                    <p class="mt-1 text-slate-700">{{ $installation['organization_name'] ?? 'Default Organization' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="font-semibold text-slate-800">IP Address</p>
                    <p class="mt-1 text-slate-700">{{ $installation['app_ip'] ?? parse_url(($installation['app_url'] ?? url('/')), PHP_URL_HOST) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="font-semibold text-slate-800">Domain Alias</p>
                    <p class="mt-1 text-slate-700">{{ !empty($installation['app_alias_domain'] ?? '') ? $installation['app_alias_domain'] : 'Not configured' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="font-semibold text-slate-800">HTTPS Enabled</p>
                    <p class="mt-1 text-slate-700">{{ ($installation['https_enabled'] ?? false) ? 'Yes' : 'No' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="font-semibold text-slate-800">Project URL</p>
                    <p class="mt-1 text-slate-700">{{ $installation['app_url'] ?? url('/') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="font-semibold text-slate-800">Super Admin Email</p>
                    <p class="mt-1 text-slate-700">{{ $installation['superadmin_email'] ?? 'superadmin@admin.com' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <p class="font-semibold text-slate-800">Super Admin Password</p>
                    <p class="mt-1 text-slate-700">{{ $installation['superadmin_password'] ?? 'Atglance@123' }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 sm:p-5">
                <p class="font-semibold text-black">Next steps for SSO integration</p>
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
                class="install-cta mt-6 inline-flex items-center rounded-xl px-5 py-3 text-sm font-bold uppercase tracking-wider transition"
            >
                Go to Login
            </a>
        </div>
    </div>
</body>
</html>