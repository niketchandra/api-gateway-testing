@extends('app')

@section('title', 'Site Setting - AtGlance')

@section('dashboard-content')
<div style="padding:40px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="font-size:28px; color:#111827;">Site Setting</h1>
        <a href="{{ route('admin.dashboard') }}" style="text-decoration:none; color:#4f46e5;">← Back to Dashboard</a>
    </div>

    @if(session('success'))
        <div style="padding:12px; border-radius:8px; background:#dcfce7; color:#166534; margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="padding:12px; border-radius:8px; background:#fee2e2; color:#991b1b; margin-bottom:16px;">
            <ul style="margin-left:16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $activeTab = request('tab', 'site');
    @endphp

    <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
        <button type="button" class="settings-tab-btn" data-tab="site" style="padding:10px 14px; border-radius:8px; border:1px solid #d1d5db; background:{{ $activeTab === 'site' ? '#4f46e5' : '#ffffff' }}; color:{{ $activeTab === 'site' ? '#ffffff' : '#111827' }}; cursor:pointer; font-weight:600;">Site Configuration</button>
        <button type="button" class="settings-tab-btn" data-tab="s3" style="padding:10px 14px; border-radius:8px; border:1px solid #d1d5db; background:{{ $activeTab === 's3' ? '#4f46e5' : '#ffffff' }}; color:{{ $activeTab === 's3' ? '#ffffff' : '#111827' }}; cursor:pointer; font-weight:600;">S3 Configuration</button>
        <button type="button" class="settings-tab-btn" data-tab="email" style="padding:10px 14px; border-radius:8px; border:1px solid #d1d5db; background:{{ $activeTab === 'email' ? '#4f46e5' : '#ffffff' }}; color:{{ $activeTab === 'email' ? '#ffffff' : '#111827' }}; cursor:pointer; font-weight:600;">Email Configuration</button>
        <button type="button" class="settings-tab-btn" data-tab="migration" style="padding:10px 14px; border-radius:8px; border:1px solid #d1d5db; background:{{ $activeTab === 'migration' ? '#4f46e5' : '#ffffff' }}; color:{{ $activeTab === 'migration' ? '#ffffff' : '#111827' }}; cursor:pointer; font-weight:600;">Migration</button>
        <button type="button" class="settings-tab-btn" data-tab="plugins" style="padding:10px 14px; border-radius:8px; border:1px solid #d1d5db; background:{{ $activeTab === 'plugins' ? '#4f46e5' : '#ffffff' }}; color:{{ $activeTab === 'plugins' ? '#ffffff' : '#111827' }}; cursor:pointer; font-weight:600;">Plugins</button>
        <button type="button" class="settings-tab-btn" data-tab="crons" style="padding:10px 14px; border-radius:8px; border:1px solid #d1d5db; background:{{ $activeTab === 'crons' ? '#4f46e5' : '#ffffff' }}; color:{{ $activeTab === 'crons' ? '#ffffff' : '#111827' }}; cursor:pointer; font-weight:600;">Crons</button>
        <button type="button" class="settings-tab-btn" data-tab="sso" style="padding:10px 14px; border-radius:8px; border:1px solid #d1d5db; background:{{ $activeTab === 'sso' ? '#4f46e5' : '#ffffff' }}; color:{{ $activeTab === 'sso' ? '#ffffff' : '#111827' }}; cursor:pointer; font-weight:600;">SSO Configuration</button>
    </div>

    <div id="tab-site" class="settings-tab-content" style="display:{{ $activeTab === 'site' ? 'block' : 'none' }}; background:white; border-radius:10px; padding:22px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="font-size:18px; margin-bottom:12px;">Site Configuration</h2>
        <form method="POST" action="{{ route('admin.settings.site', ['tab' => 'site']) }}" enctype="multipart/form-data">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Logo Image Upload</label>
                    <input type="file" name="site_logo" accept=".jpg,.jpeg,.png,.webp,.svg" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; background:white;">
                    @if(!empty($siteLogoUrl))
                        <div style="margin-top:8px;">
                            <img src="{{ $siteLogoUrl }}" alt="Site Logo" style="max-height:48px; border-radius:6px; border:1px solid #e5e7eb; padding:4px; background:white;">
                        </div>
                    @endif
                </div>
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Logo URL (optional override)</label>
                    <input type="url" name="site_logo_url" value="{{ old('site_logo_url', $siteLogoUrl) }}" placeholder="https://example.com/logo.png" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Site Description</label>
                <textarea name="site_description" rows="3" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">{{ old('site_description', $siteDescription) }}</textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Site Tags (comma separated)</label>
                    <input type="text" name="site_tags" value="{{ old('site_tags', $siteTagsText) }}" placeholder="security, api-gateway, monitoring" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Features (one per line)</label>
                    <textarea name="site_features" rows="4" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">{{ old('site_features', $siteFeaturesText) }}</textarea>
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Metadata (JSON)</label>
                <textarea name="site_metadata" rows="6" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; font-family:'Courier New', monospace;">{{ old('site_metadata', $siteMetadataText) }}</textarea>
            </div>

            <button type="submit" style="background:#4f46e5; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600; cursor:pointer;">Save Site Settings</button>
        </form>
    </div>

    <div id="tab-s3" class="settings-tab-content" style="display:{{ $activeTab === 's3' ? 'block' : 'none' }}; background:white; border-radius:10px; padding:22px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="font-size:18px; margin-bottom:12px;">S3 Configuration</h2>
        <form method="POST" action="{{ route('admin.settings.s3', ['tab' => 's3']) }}">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="s3_enabled" value="1" {{ old('s3_enabled', $useS3Storage) ? 'checked' : '' }}>
                    <span>Enable S3 storage for configuration files</span>
                </label>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Access Key</label>
                    <input type="text" name="s3_access_key" value="{{ old('s3_access_key', $s3AccessKey) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Secret Key {{ $hasS3Secret ? '(leave blank to keep existing)' : '' }}</label>
                    <input type="password" name="s3_secret_key" {{ $hasS3Secret ? '' : 'required' }} style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Region</label>
                    <input type="text" name="s3_region" value="{{ old('s3_region', $s3Region) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Bucket Name</label>
                    <input type="text" name="s3_bucket" value="{{ old('s3_bucket', $s3Bucket) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
            </div>
            <button type="submit" style="background:#111827; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600; cursor:pointer;">Save S3 Settings</button>
        </form>
    </div>

    <div id="tab-email" class="settings-tab-content" style="display:{{ $activeTab === 'email' ? 'block' : 'none' }}; background:white; border-radius:10px; padding:22px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="font-size:18px; margin-bottom:12px;">Email Configuration</h2>
        <form method="POST" action="{{ route('admin.settings.mail', ['tab' => 'email']) }}">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ old('mail_host', $mailHost) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">SMTP Port</label>
                    <input type="number" name="mail_port" value="{{ old('mail_port', $mailPort) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">SMTP Username</label>
                    <input type="text" name="mail_username" value="{{ old('mail_username', $mailUsername) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">SMTP Password {{ $hasMailPassword ? '(leave blank to keep existing)' : '' }}</label>
                    <input type="password" name="mail_password" {{ $hasMailPassword ? '' : 'required' }} style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Encryption</label>
                    <select name="mail_encryption" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                        <option value="" {{ old('mail_encryption', $mailEncryption) === '' ? 'selected' : '' }}>None</option>
                        <option value="tls" {{ old('mail_encryption', $mailEncryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('mail_encryption', $mailEncryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="starttls" {{ old('mail_encryption', $mailEncryption) === 'starttls' ? 'selected' : '' }}>STARTTLS</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $mailFromName) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">From Address</label>
                <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $mailFromAddress) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
            </div>
            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Alert Recipients (comma separated)</label>
                <textarea name="mail_recipients" rows="3" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">{{ old('mail_recipients', $mailRecipientsText) }}</textarea>
            </div>
            <button type="submit" style="background:#16a34a; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600; cursor:pointer;">Save Email Settings</button>
        </form>
    </div>

    <div id="tab-migration" class="settings-tab-content" style="display:{{ $activeTab === 'migration' ? 'block' : 'none' }}; background:white; border-radius:10px; padding:22px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="font-size:18px; margin-bottom:8px;">Migration</h2>
        <p style="color:#6b7280;">Coming Soon</p>
    </div>

    <div id="tab-plugins" class="settings-tab-content" style="display:{{ $activeTab === 'plugins' ? 'block' : 'none' }}; background:white; border-radius:10px; padding:22px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="font-size:18px; margin-bottom:8px;">Plugins</h2>
        <p style="color:#6b7280;">Coming Soon</p>
    </div>

    <div id="tab-crons" class="settings-tab-content" style="display:{{ $activeTab === 'crons' ? 'block' : 'none' }}; background:white; border-radius:10px; padding:22px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="font-size:18px; margin-bottom:8px;">Crons</h2>
        <p style="color:#6b7280;">Coming Soon</p>
    </div>

    <div id="tab-sso" class="settings-tab-content" style="display:{{ $activeTab === 'sso' ? 'block' : 'none' }}; background:white; border-radius:10px; padding:22px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="font-size:18px; margin-bottom:12px;">SSO Configuration</h2>
        <form method="POST" action="{{ route('admin.settings.sso', ['tab' => 'sso']) }}">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" name="sso_enabled" value="1" {{ old('sso_enabled', $ssoEnabled) ? 'checked' : '' }}>
                    <span>Enable SSO login</span>
                </label>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Provider</label>
                    <select name="sso_provider" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; background:white;">
                        <option value="azure-ad" {{ old('sso_provider', $ssoProvider) === 'azure-ad' ? 'selected' : '' }}>Azure AD</option>
                        <option value="okta" {{ old('sso_provider', $ssoProvider) === 'okta' ? 'selected' : '' }}>Okta</option>
                        <option value="google" {{ old('sso_provider', $ssoProvider) === 'google' ? 'selected' : '' }}>Google</option>
                        <option value="auth0" {{ old('sso_provider', $ssoProvider) === 'auth0' ? 'selected' : '' }}>Auth0</option>
                        <option value="custom" {{ old('sso_provider', $ssoProvider) === 'custom' ? 'selected' : '' }}>Custom OIDC</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Tenant ID / Domain</label>
                    <input type="text" name="sso_tenant_id" value="{{ old('sso_tenant_id', $ssoTenantId) }}" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:12px;">
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Client ID</label>
                    <input type="text" name="sso_client_id" value="{{ old('sso_client_id', $ssoClientId) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
                <div>
                    <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Client Secret {{ $hasSsoClientSecret ? '(leave blank to keep existing)' : '' }}</label>
                    <input type="password" name="sso_client_secret" {{ $hasSsoClientSecret ? '' : 'required' }} style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <label style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Redirect URL</label>
                <input type="url" name="sso_redirect_url" value="{{ old('sso_redirect_url', $ssoRedirectUrl) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
            </div>

            <button type="submit" style="background:#0ea5e9; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600; cursor:pointer;">Save SSO Settings</button>
        </form>
    </div>
</div>

<script>
(function () {
    const tabButtons = document.querySelectorAll('.settings-tab-btn');
    const tabContents = document.querySelectorAll('.settings-tab-content');

    function activateTab(tabName) {
        tabContents.forEach((content) => {
            content.style.display = content.id === `tab-${tabName}` ? 'block' : 'none';
        });

        tabButtons.forEach((button) => {
            if (button.dataset.tab === tabName) {
                button.style.background = '#4f46e5';
                button.style.color = '#ffffff';
            } else {
                button.style.background = '#ffffff';
                button.style.color = '#111827';
            }
        });

        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url.toString());
    }

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => activateTab(button.dataset.tab));
    });
})();
</script>
@endsection
