<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AtGlance Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100">
    <div class="max-w-2xl mx-auto px-6 py-10">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
            <h1 class="text-2xl font-semibold text-slate-900">AtGlance Project Installer</h1>
            <p class="text-sm text-slate-600 mt-2">Complete the details below to install the application. Migration and seed will run automatically.</p>

            @if ($errors->any())
                <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('install.run') }}" method="POST" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="organization_name" class="block text-sm font-medium text-slate-700">Organization Name</label>
                    <input
                        id="organization_name"
                        name="organization_name"
                        type="text"
                        required
                        value="{{ old('organization_name', 'Default Organization') }}"
                        placeholder="Acme Corp"
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <div>
                    <label for="app_url" class="block text-sm font-medium text-slate-700">Domain or IP</label>
                    <input
                        id="app_url"
                        name="app_url"
                        type="text"
                        required
                        value="{{ old('app_url', $defaultDomain ?? request()->getHttpHost()) }}"
                        placeholder="example.com or 192.168.1.50:8000"
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                    <p class="mt-1 text-xs text-slate-500">Do not include http:// or https://</p>
                </div>

                <div>
                    <label for="use_https" class="block text-sm font-medium text-slate-700">Use HTTPS</label>
                    <select
                        id="use_https"
                        name="use_https"
                        class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="1" {{ old('use_https', '1') === '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('use_https') === '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                >
                    Next - Install Application
                </button>
            </form>
        </div>
    </div>
</body>
</html>