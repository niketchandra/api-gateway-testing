<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AtGlance Installer</title>
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
                    colors: {
                        brand: {
                            50: '#effdf7',
                            100: '#d8f9e8',
                            500: '#10b981',
                            600: '#0f9f71',
                            700: '#0c7e5a'
                        }
                    },
                    boxShadow: {
                        glow: '0 20px 60px -30px rgba(16, 185, 129, 0.65)'
                    }
                }
            }
        };
    </script>
    <style>
        :root {
            --bg-1: #f5f5f5;
            --bg-2: #ffffff;
            --bg-3: #cccccc;
            --ink: #0f172a;
        }

        body {
            font-family: 'Sora', ui-sans-serif, system-ui;
            color: var(--ink);
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden bg-white">
    <div class="fixed inset-0 -z-10">
        <div class="h-full w-full bg-white"></div>
    </div>

    <div class="mx-auto flex min-h-screen w-full max-w-5xl items-center justify-center px-5 py-8 sm:px-8">
        <div class="w-full max-w-3xl rounded-3xl border border-white/20 bg-white/85 p-6 shadow-glow backdrop-blur-xl sm:p-10">
            <div class="mb-6 flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-black text-lg font-extrabold text-white shadow-lg">
                    AT
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">AtGlance Project Installer</h1>
                </div>
            </div>

            <div class="rounded-2xl border border-cccccc bg-f5f5f5 p-4 sm:p-5">
                <p class="text-sm leading-relaxed text-slate-700 sm:text-base">
                    <span class="font-semibold text-slate-900">AtGlance</span> is a Configuration Files Backup as a Service platform.
                    It helps Linux administrators keep server configuration files safe and recoverable.
                    This is the first build of the project, and we plan to bring many more useful features soon.
                </p>
            </div>

            <p class="mt-5 text-sm font-medium text-slate-600 sm:text-base">Please help set up the AtGlance application for your organization.</p>

            @if ($errors->any())
                <div class="mt-5 rounded-xl border border-cccccc bg-efefef p-4 text-sm text-333333">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="installerForm" action="{{ route('install.run') }}" method="POST" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="organization_name" class="block text-sm font-semibold text-slate-800">Organization Name</label>
                    <input
                        id="organization_name"
                        name="organization_name"
                        type="text"
                        required
                        value="{{ old('organization_name', 'Default Organization') }}"
                        placeholder="Acme Corp"
                        class="mt-2 w-full rounded-xl border border-cccccc bg-white px-4 py-3 text-sm outline-none transition focus:border-black focus:ring-4 focus:ring-slate-200"
                    >
                </div>

                <div>
                    <label for="app_url" class="block text-sm font-semibold text-slate-800">Domain or IP</label>
                    <input
                        id="app_url"
                        name="app_url"
                        type="text"
                        required
                        value="{{ old('app_url', $defaultDomain ?? request()->getHttpHost()) }}"
                        placeholder="example.com or 192.168.1.50:8000"
                        class="mt-2 w-full rounded-xl border border-cccccc bg-white px-4 py-3 text-sm outline-none transition focus:border-black focus:ring-4 focus:ring-slate-200"
                    >
                    <p class="mt-2 text-xs text-slate-500">Do not include http:// or https://</p>
                </div>

                <div>
                    <label for="use_https" class="block text-sm font-semibold text-slate-800">Use HTTPS</label>
                    <select
                        id="use_https"
                        name="use_https"
                        class="mt-2 w-full rounded-xl border border-cccccc bg-white px-4 py-3 text-sm outline-none transition focus:border-black focus:ring-4 focus:ring-slate-200"
                    >
                        <option value="1" {{ old('use_https', '1') === '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('use_https') === '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div>
                    <label for="superadmin_email" class="block text-sm font-semibold text-slate-800">Super Admin Email</label>
                    <input
                        id="superadmin_email"
                        name="superadmin_email"
                        type="email"
                        required
                        placeholder="username@org-name.com"
                        class="mt-2 w-full rounded-xl border border-cccccc bg-white px-4 py-3 text-sm outline-none transition focus:border-black focus:ring-4 focus:ring-slate-200"
                    >
                </div>

                <div>
                    <label for="superadmin_password" class="block text-sm font-semibold text-slate-800">Super Admin Password</label>
                    <input
                        id="superadmin_password"
                        name="superadmin_password"
                        type="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Minimum 8 characters"
                        class="mt-2 w-full rounded-xl border border-cccccc bg-white px-4 py-3 text-sm outline-none transition focus:border-black focus:ring-4 focus:ring-slate-200"
                    >
                </div>

                <div>
                    <label for="superadmin_password_confirmation" class="block text-sm font-semibold text-slate-800">Confirm Password</label>
                    <input
                        id="superadmin_password_confirmation"
                        name="superadmin_password_confirmation"
                        type="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="Re-enter super admin password"
                        class="mt-2 w-full rounded-xl border border-cccccc bg-white px-4 py-3 text-sm outline-none transition focus:border-black focus:ring-4 focus:ring-slate-200"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-black px-4 py-3 text-sm font-bold uppercase tracking-wider text-white transition"
                    onmouseover="this.style.background='#555555'"
                    onmouseout="this.style.background='#000000'"
                >
                    Next - Install Application
                </button>
            </form>
        </div>
    </div>

    <div id="installOverlay" class="pointer-events-none fixed inset-0 z-40 hidden items-center justify-center bg-slate-950/70 px-5">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <p class="text-sm font-semibold uppercase tracking-widest text-black">AtGlance</p>
            <h2 class="mt-2 text-xl font-extrabold text-slate-900">We are setting up it for you...</h2>
            <p class="mt-2 text-sm text-slate-600">Please wait while we prepare your AtGlance application.</p>

            <div class="mt-5 h-3 w-full overflow-hidden rounded-full bg-slate-200">
                <div id="installProgressBar" class="h-full w-[8%] rounded-full bg-black transition-all duration-500"></div>
            </div>
            <p id="installProgressText" class="mt-2 text-right text-xs font-semibold text-slate-500">8%</p>
        </div>
    </div>

    <script>
        (function () {
            var form = document.getElementById('installerForm');
            var overlay = document.getElementById('installOverlay');
            var bar = document.getElementById('installProgressBar');
            var text = document.getElementById('installProgressText');
            var progressTimer = null;

            if (!form || !overlay || !bar || !text) {
                return;
            }

            form.addEventListener('submit', function () {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');

                var progress = 8;
                bar.style.width = progress + '%';
                text.textContent = progress + '%';

                progressTimer = window.setInterval(function () {
                    if (progress >= 92) {
                        window.clearInterval(progressTimer);
                        return;
                    }

                    progress += Math.floor(Math.random() * 8) + 3;
                    if (progress > 92) {
                        progress = 92;
                    }

                    bar.style.width = progress + '%';
                    text.textContent = progress + '%';
                }, 420);
            });
        })();
    </script>
</body>
</html>