<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function redirectToSso(Request $request, string $provider)
    {
        $provider = strtolower(trim($provider));
        $intent = strtolower(trim((string) $request->query('intent', '')));
        $providerCatalog = config('sso.providers', []);

        if (!isset($providerCatalog[$provider])) {
            return redirect()->route('home')->withErrors([
                'login' => 'Unsupported SSO provider selected.',
            ]);
        }

        $enabledProviders = $this->resolveSsoEnabledProviders(array_keys($providerCatalog));
        $ssoEnabledFlag = $this->resolveSsoEnabledFlag();
        $effectiveSsoEnabled = $ssoEnabledFlag || !empty($enabledProviders);

        if (!$effectiveSsoEnabled) {
            return redirect()->route('home')->withErrors([
                'login' => 'SSO is currently disabled by the administrator.',
            ]);
        }

        if (!in_array($provider, $enabledProviders, true)) {
            return redirect()->route('home')->withErrors([
                'login' => 'This SSO provider is not enabled for your organization.',
            ]);
        }

        $providerUrl = $this->resolveSsoProviderUrl($provider);

        if ($providerUrl === '') {
            return redirect()->route('home')->withErrors([
                'login' => 'SSO URL is not configured for the selected provider.',
            ]);
        }

        if ($provider === 'github') {
            $providerUrl = $this->buildGithubAuthorizeUrl($providerUrl, $request);
        } elseif (in_array($provider, ['azure-ad', 'microsoft'], true)) {
            $providerUrl = $this->buildAzureAdAuthorizeUrl($providerUrl, $provider, $request);
        } elseif (in_array($provider, ['authentik', 'oidc'], true)) {
            $providerUrl = $this->buildOidcAuthorizeUrl($provider, $request);
        }

        if ($intent === 'pin_reset') {
            $request->session()->put('sso_intent', 'pin_reset');
        } else {
            $request->session()->forget('sso_intent');
        }

        return redirect()->away($providerUrl);
    }

    public function handleSsoCallback(Request $request, string $provider)
    {
        $provider = strtolower(trim($provider));
        $intent = (string) $request->session()->pull('sso_intent', '');

        Log::info('SSO_Callback_Start', [
            'provider' => $provider,
            'intent' => $intent,
            'has_code' => $request->filled('code'),
            'has_state' => $request->filled('state'),
            'has_error' => $request->filled('error'),
        ]);

        if (!in_array($provider, ['github', 'azure-ad', 'microsoft', 'authentik', 'oidc'], true)) {
            Log::warning('SSO_UnsupportedProvider', ['provider' => $provider]);
            return redirect()->route('home')->withErrors([
                'login' => 'Callback for this SSO provider is not implemented yet.',
            ]);
        }

        if ($request->filled('error')) {
            Log::warning('SSO_ProviderError', [
                'provider' => $provider,
                'error' => $request->input('error'),
                'error_description' => $request->input('error_description'),
            ]);
            return redirect()->route('home')->withErrors([
                'login' => ucfirst($provider) . ' login was cancelled or denied.',
            ]);
        }

        if (in_array($provider, ['azure-ad', 'microsoft'], true)) {
            return $this->handleAzureAdCallback($request, $provider, $intent);
        }

        if (in_array($provider, ['authentik', 'oidc'], true)) {
            return $this->handleOidcCallback($request, $provider, $intent);
        }

        return $this->handleGithubCallback($request, $intent);
    }

    private function handleGithubCallback(Request $request, string $intent)
    {
        $code = (string) $request->query('code', '');
        if ($code === '') {
            return redirect()->route('home')->withErrors([
                'login' => 'Missing authorization code from GitHub callback.',
            ]);
        }

        $expectedState = (string) $request->session()->pull('sso_state_github', '');
        $returnedState = (string) $request->query('state', '');
        if ($expectedState !== '' && $returnedState !== '' && !hash_equals($expectedState, $returnedState)) {
            return redirect()->route('home')->withErrors([
                'login' => 'Invalid OAuth state received from GitHub.',
            ]);
        }

        [$clientId, $clientSecret] = $this->getProviderClientCredentials('github');

        if ($clientId === '' || $clientSecret === '') {
            return redirect()->route('home')->withErrors([
                'login' => 'GitHub SSO is not fully configured. Set Client ID and Client Secret in admin settings.',
            ]);
        }

        $callbackUrl = route('auth.sso.callback', ['provider' => 'github']);

        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->timeout(15)
            ->post('https://github.com/login/oauth/access_token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'redirect_uri' => $callbackUrl,
            ]);

        if (!$tokenResponse->ok()) {
            return redirect()->route('home')->withErrors([
                'login' => 'Failed to get access token from GitHub.',
            ]);
        }

        $accessToken = (string) $tokenResponse->json('access_token', '');
        if ($accessToken === '') {
            return redirect()->route('home')->withErrors([
                'login' => 'GitHub did not return an access token.',
            ]);
        }

        $githubUserResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->withHeaders([
                'User-Agent' => 'AtGlance-SSO',
                'Accept' => 'application/vnd.github+json',
            ])
            ->timeout(15)
            ->get('https://api.github.com/user');

        if (!$githubUserResponse->ok()) {
            return redirect()->route('home')->withErrors([
                'login' => 'Failed to fetch GitHub profile information.',
            ]);
        }

        $githubUser = $githubUserResponse->json();
        $email = trim((string) ($githubUser['email'] ?? ''));

        if ($email === '') {
            $emailsResponse = Http::withToken($accessToken)
                ->acceptJson()
                ->withHeaders([
                    'User-Agent' => 'AtGlance-SSO',
                    'Accept' => 'application/vnd.github+json',
                ])
                ->timeout(15)
                ->get('https://api.github.com/user/emails');

            if ($emailsResponse->ok()) {
                $emails = collect($emailsResponse->json())
                    ->filter(fn ($item) => is_array($item));

                $primaryVerified = $emails->first(fn ($item) => ($item['primary'] ?? false) && ($item['verified'] ?? false));
                $fallback = $emails->first();
                $email = trim((string) (($primaryVerified['email'] ?? null) ?? ($fallback['email'] ?? '')));
            }
        }

        if ($email === '') {
            return redirect()->route('home')->withErrors([
                'login' => 'GitHub account email is required to sign in.',
            ]);
        }

        $displayName = trim((string) (($githubUser['name'] ?? null) ?: ($githubUser['login'] ?? null) ?: 'GitHub User'));

        return $this->processOrCreateSsoUser($email, $displayName, 'github', $intent);
    }

    private function handleAzureAdCallback(Request $request, string $provider, string $intent)
    {
        $code = (string) $request->input('code', '');
        if ($code === '') {
            return redirect()->route('home')->withErrors([
                'login' => 'Missing authorization code from ' . ucfirst($provider) . ' callback.',
            ]);
        }

        $expectedState = (string) $request->session()->pull('sso_state_azure_ad', '');
        $returnedState = (string) $request->input('state', '');
        if ($expectedState !== '' && $returnedState !== '' && !hash_equals($expectedState, $returnedState)) {
            return redirect()->route('home')->withErrors([
                'login' => 'Invalid OAuth state received from ' . ucfirst($provider) . '.',
            ]);
        }

        [$clientId, $clientSecret] = $this->getProviderClientCredentials($provider);

        if ($clientId === '' || $clientSecret === '') {
            return redirect()->route('home')->withErrors([
                'login' => ucfirst($provider) . ' SSO is not fully configured. Set Client ID and Client Secret in admin settings.',
            ]);
        }

        $callbackUrl = route('auth.sso.callback', ['provider' => $provider]);
        $baseUrl = $this->resolveSsoProviderUrl($provider);
        preg_match('/^(https?:\/\/[^\/]+)/', $baseUrl, $matches);
        $tenantBaseUrl = $matches[1] ?? 'https://login.microsoftonline.com';

        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->timeout(15)
            ->post($tenantBaseUrl . '/oauth2/v2.0/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'redirect_uri' => $callbackUrl,
                'grant_type' => 'authorization_code',
            ]);

        if (!$tokenResponse->ok()) {
            return redirect()->route('home')->withErrors([
                'login' => 'Failed to get access token from ' . ucfirst($provider) . '.',
            ]);
        }

        $accessToken = (string) $tokenResponse->json('access_token', '');
        if ($accessToken === '') {
            return redirect()->route('home')->withErrors([
                'login' => ucfirst($provider) . ' did not return an access token.',
            ]);
        }

        $graphResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(15)
            ->get('https://graph.microsoft.com/v1.0/me');

        if (!$graphResponse->ok()) {
            return redirect()->route('home')->withErrors([
                'login' => 'Failed to fetch ' . ucfirst($provider) . ' profile information.',
            ]);
        }

        $azureUser = $graphResponse->json();
        $email = trim((string) ($azureUser['mail'] ?? $azureUser['userPrincipalName'] ?? ''));
        $displayName = trim((string) ($azureUser['displayName'] ?? 'Azure AD User'));

        if ($email === '') {
            return redirect()->route('home')->withErrors([
                'login' => ucfirst($provider) . ' account email is required to sign in.',
            ]);
        }

        return $this->processOrCreateSsoUser($email, $displayName, $provider, $intent);
    }

    private function processOrCreateSsoUser(string $email, string $displayName, string $provider, string $intent)
    {
        try {
            Log::info('SSO_ProcessUser_Start', [
                'email' => $email,
                'provider' => $provider,
                'intent' => $intent,
            ]);

            $user = User::where('email', $email)->first();
            if (!$user) {
                Log::info('SSO_UserNotFound_Creating', ['email' => $email]);
                $user = User::create([
                    'name' => $displayName,
                    'email' => $email,
                    'password' => Hash::make(Str::random(32)),
                    'rbac_id' => 102, // Default to user role
                ]);
                Log::info('SSO_UserCreated', ['email' => $email, 'user_id' => $user->id, 'rbac_id' => $user->rbac_id]);
            } else {
                Log::info('SSO_UserFound', ['email' => $email, 'user_id' => $user->id, 'rbac_id' => $user->rbac_id]);
            }

            if (strtolower((string) ($user->status ?? 'active')) !== 'active') {
                Log::warning('SSO_UserInactive', ['email' => $email, 'status' => $user->status]);
                return redirect()->route('home')->withErrors([
                    'login' => 'Your account is inactive. Please contact your administrator.',
                ]);
            }

            if ($intent === 'pin_reset' && Auth::check()) {
                /** @var User $currentUser */
                $currentUser = Auth::user();
                if (strcasecmp((string) $currentUser->email, $email) !== 0) {
                    Log::warning('SSO_PinReset_EmailMismatch', ['current' => $currentUser->email, 'sso' => $email]);
                    return redirect()->route('settings')->withErrors([
                        'current_password' => 'User not authenticated. SSO account does not match current user email.',
                    ]);
                }

                $pendingPin = (string) session()->pull('pending_pin_reset_pin', '');
                if (preg_match('/^\d{5}$/', $pendingPin) === 1) {
                    $currentUser->pin = Hash::make($pendingPin);
                    $currentUser->save();
                    Log::info('SSO_PinReset_Completed', ['email' => $email]);

                    return redirect()->route('settings')->with('success', 'SSO authentication successful. PIN reset completed.');
                }

                session()->put('sso_pin_verified_at', Carbon::now()->timestamp);
                session()->put('auth_method', 'sso');
                session()->put('auth_sso_provider', $provider);
                Log::info('SSO_PinReset_Verified', ['email' => $email]);

                return redirect()->route('settings')->with('success', 'SSO re-authentication successful. You can now reset your PIN.');
            }

            if ($intent === 'pin_reset' && !Auth::check()) {
                Log::warning('SSO_PinReset_NotAuthenticated', ['email' => $email]);
                return redirect()->route('home')->withErrors([
                    'login' => 'User not authenticated. Please login first and retry PIN reset.',
                ]);
            }

            Auth::login($user, true);
            session()->regenerate();
            session()->put('auth_method', 'sso');
            session()->put('auth_sso_provider', $provider);
            
            $targetRoute = in_array((int) $user->rbac_id, [100, 101], true) ? 'admin.dashboard' : 'dashboard';
            Log::info('SSO_LoginSuccess', [
                'email' => $email,
                'user_id' => $user->id,
                'rbac_id' => $user->rbac_id,
                'target_route' => $targetRoute,
                'provider' => $provider,
            ]);

            return redirect()->route($targetRoute)->with('success', 'Logged in with ' . ucfirst($provider) . ' successfully.');
        } catch (\Throwable $e) {
            Log::error('SSO_ProcessUser_Error', [
                'email' => $email,
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('home')->withErrors([
                'login' => 'An error occurred during authentication. Please try again.',
            ]);
        }
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        if ($this->isEmailRegistrationDisabled()) {
            return back()->withErrors([
                'register' => 'Email registration is disabled. Please continue with SSO.',
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Log the user in
            Auth::login($user);
            $request->session()->put('auth_method', 'password');
            $request->session()->forget(['auth_sso_provider', 'sso_pin_verified_at', 'sso_intent']);
            $targetRoute = in_array((int) $user->rbac_id, [100, 101], true) ? 'admin.dashboard' : 'dashboard';

            return redirect()->route($targetRoute)->with('success', 'Registration successful! Welcome to AtGlance.');
        } catch (\Exception $e) {
            return back()->withErrors(['register' => 'Registration failed. Please try again.']);
        }
    }

    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find the user by email
        $user = User::where('email', $credentials['email'])->first();

        // Check if user exists and password matches
        $passwordMatches = $user ? Hash::check($credentials['password'], $user->password_hash) : false;

        Log::info('web_login_attempt', [
            'email' => $credentials['email'],
            'db_default' => config('database.default'),
            'db_name' => DB::connection()->getDatabaseName(),
            'db_host' => config('database.connections.mysql.host'),
            'user_found' => (bool) $user,
            'password_hash_length' => $user ? strlen((string) $user->password_hash) : 0,
            'password_matches' => $passwordMatches,
        ]);

        if ($user && $passwordMatches) {
            if (strtolower((string) ($user->status ?? 'active')) !== 'active') {
                return back()
                    ->withInput($request->only('email'))
                    ->with('inactive_user', 'User is Inactive please reachout to the Administrator');
            }

            // Log the user in
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $request->session()->put('auth_method', 'password');
            $request->session()->forget(['auth_sso_provider', 'sso_pin_verified_at', 'sso_intent']);
            $targetRoute = in_array((int) $user->rbac_id, [100, 101], true) ? 'admin.dashboard' : 'dashboard';

            return redirect()->route($targetRoute)->with('success', 'Welcome back!');
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetLink(Request $request)
    {
        if ($this->isEmailRegistrationDisabled()) {
            return back()->withErrors([
                'email' => 'Password reset is disabled while email registration is turned off. Use SSO sign-in.',
            ]);
        }

        $validated = $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        // Generate a reset token (in a real app, use Laravel's password reset functionality)
        $token = Str::random(64);

        // Store the token in the database or cache
        // For now, just return a message
        return back()->with('status', 'If an account exists with this email, a password reset link will be sent shortly.');
    }

    /**
     * Store contact form submission
     */
    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        try {
            // Here you would typically save the contact message to database
            // and send it to your email
            
            // For now, just return success
            return back()->with('success', 'Thank you for reaching out! We will get back to you soon.');
        } catch (\Exception $e) {
            return back()->withErrors(['contact' => 'Failed to send message. Please try again later.']);
        }
    }

    private function buildGithubAuthorizeUrl(string $providerUrl, Request $request): string
    {
        [$clientId] = $this->getProviderClientCredentials('github');
        if ($clientId === '') {
            return $providerUrl;
        }

        $parts = parse_url($providerUrl);
        $baseUrl = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? 'github.com') . ($parts['path'] ?? '/login/oauth/authorize');

        parse_str($parts['query'] ?? '', $query);

        $state = Str::random(40);
        $request->session()->put('sso_state_github', $state);

        $callbackUrl = route('auth.sso.callback', ['provider' => 'github']);

        $query = array_merge($query, [
            'client_id' => $query['client_id'] ?? $clientId,
            'redirect_uri' => $query['redirect_uri'] ?? $callbackUrl,
            'scope' => $query['scope'] ?? 'read:user user:email',
            'state' => $query['state'] ?? $state,
        ]);

        return $baseUrl . '?' . http_build_query($query);
    }

    private function buildAzureAdAuthorizeUrl(string $baseUrl, string $provider, Request $request): string
    {
        [$clientId] = $this->getProviderClientCredentials($provider);
        if ($clientId === '') {
            return $baseUrl;
        }

        $state = Str::random(40);
        $request->session()->put('sso_state_azure_ad', $state);

        $callbackUrl = route('auth.sso.callback', ['provider' => $provider]);

        $query = [
            'client_id' => $clientId,
            'redirect_uri' => $callbackUrl,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'response_mode' => 'form_post',
            'state' => $state,
        ];

        return $baseUrl . '?' . http_build_query($query);
    }

    private function buildOidcAuthorizeUrl(string $provider, Request $request): string
    {
        [$clientId] = $this->getProviderClientCredentials($provider);
        $providerUrl = $this->resolveSsoProviderUrl($provider);
        $discoveryDocument = $this->resolveOidcDiscoveryDocument($provider);
        $authorizationEndpoint = $this->normalizeBrowserFacingUrl(
            trim((string) ($discoveryDocument['authorization_endpoint'] ?? '')),
            $providerUrl
        );

        if ($clientId === '' || $authorizationEndpoint === '') {
            return $providerUrl;
        }

        $state = Str::random(40);
        $request->session()->put($this->ssoStateSessionKey($provider), $state);

        $callbackUrl = route('auth.sso.callback', ['provider' => $provider]);

        $query = [
            'client_id' => $clientId,
            'redirect_uri' => $callbackUrl,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
        ];

        return $authorizationEndpoint . '?' . http_build_query($query);
    }

    private function handleOidcCallback(Request $request, string $provider, string $intent)
    {
        $code = (string) $request->input('code', $request->query('code', ''));
        if ($code === '') {
            return redirect()->route('home')->withErrors([
                'login' => 'Missing authorization code from ' . ucfirst($provider) . ' callback.',
            ]);
        }

        $expectedState = (string) $request->session()->pull($this->ssoStateSessionKey($provider), '');
        $returnedState = (string) $request->input('state', $request->query('state', ''));
        if ($expectedState !== '' && $returnedState !== '' && !hash_equals($expectedState, $returnedState)) {
            return redirect()->route('home')->withErrors([
                'login' => 'Invalid OAuth state received from ' . ucfirst($provider) . '.',
            ]);
        }

        [$clientId, $clientSecret] = $this->getProviderClientCredentials($provider);

        if ($clientId === '' || $clientSecret === '') {
            return redirect()->route('home')->withErrors([
                'login' => ucfirst($provider) . ' SSO is not fully configured. Set Client ID and Client Secret in admin settings.',
            ]);
        }

        $discoveryDocument = $this->resolveOidcDiscoveryDocument($provider);
        $tokenEndpoint = $this->resolveContainerAccessibleUrl(trim((string) ($discoveryDocument['token_endpoint'] ?? '')));
        $userinfoEndpoint = $this->resolveContainerAccessibleUrl(trim((string) ($discoveryDocument['userinfo_endpoint'] ?? '')));

        if ($tokenEndpoint === '') {
            return redirect()->route('home')->withErrors([
                'login' => ucfirst($provider) . ' discovery document did not provide a token endpoint.',
            ]);
        }

        $callbackUrl = route('auth.sso.callback', ['provider' => $provider]);

        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->timeout(15)
            ->post($tokenEndpoint, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'redirect_uri' => $callbackUrl,
                'grant_type' => 'authorization_code',
            ]);

        if (!$tokenResponse->ok()) {
            return redirect()->route('home')->withErrors([
                'login' => 'Failed to get access token from ' . ucfirst($provider) . '.',
            ]);
        }

        $accessToken = (string) $tokenResponse->json('access_token', '');
        $idToken = (string) $tokenResponse->json('id_token', '');

        if ($accessToken === '') {
            return redirect()->route('home')->withErrors([
                'login' => ucfirst($provider) . ' did not return an access token.',
            ]);
        }

        $oidcUser = [];
        if ($userinfoEndpoint !== '') {
            $userinfoResponse = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(15)
                ->get($userinfoEndpoint);

            if ($userinfoResponse->ok()) {
                $userinfoPayload = $userinfoResponse->json();
                if (is_array($userinfoPayload)) {
                    $oidcUser = $userinfoPayload;
                }
            }
        }

        if (empty($oidcUser) && $idToken !== '') {
            $oidcUser = $this->decodeJwtPayload($idToken);
        }

        $email = trim((string) ($oidcUser['email'] ?? $oidcUser['preferred_username'] ?? $oidcUser['upn'] ?? $oidcUser['username'] ?? ''));
        $displayName = trim((string) ($oidcUser['name'] ?? ''));
        if ($displayName === '') {
            $displayName = trim((string) (($oidcUser['given_name'] ?? '') . ' ' . ($oidcUser['family_name'] ?? '')));
        }
        if ($displayName === '') {
            $displayName = trim((string) ($oidcUser['preferred_username'] ?? 'OIDC User'));
        }

        if ($email === '') {
            return redirect()->route('home')->withErrors([
                'login' => ucfirst($provider) . ' account email is required to sign in.',
            ]);
        }

        return $this->processOrCreateSsoUser($email, $displayName, $provider, $intent);
    }

    private function getProviderClientCredentials(string $provider): array
    {
        $provider = strtolower(trim($provider));

        $prefix = $this->providerEnvKeyPrefix($provider);
        $clientId = $this->resolveSsoProviderValue('sso_provider_client_ids', $provider, $this->getEnvValue($prefix . '_CLIENT_ID', ''));
        $clientSecret = $this->resolveSsoProviderSecret($provider, $this->getSecretEnvValue($prefix . '_CLIENT_SECRET', ''));

        return [$clientId, $clientSecret];
    }

    private function getEnvValue(string $key, string $default = ''): string
    {
        $fileValue = $this->readEnvFileValue($key);
        if ($fileValue !== null) {
            return $fileValue;
        }

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

    private function readEnvFileValue(string $key): ?string
    {
        $envPath = base_path('.env');
        if (!is_readable($envPath)) {
            return null;
        }

        $pattern = '/^' . preg_quote($key, '/') . '=(.*)$/m';
        $contents = @file_get_contents($envPath);
        if ($contents === false || preg_match($pattern, $contents, $matches) !== 1) {
            return null;
        }

        $raw = trim((string) ($matches[1] ?? ''));
        if (
            strlen($raw) >= 2
            && str_starts_with($raw, '"')
            && str_ends_with($raw, '"')
        ) {
            $raw = substr($raw, 1, -1);
            $raw = str_replace('\\"', '"', $raw);
        }

        return trim($raw);
    }

    private function getSecretEnvValue(string $key, string $default = ''): string
    {
        $value = $this->getEnvValue($key, $default);
        if ($value === '') {
            return '';
        }

        if (!str_starts_with($value, 'ENC:')) {
            return $value;
        }

        try {
            return trim(Crypt::decryptString(substr($value, 4)));
        } catch (\Throwable) {
            return '';
        }
    }

    private function resolveOidcDiscoveryDocument(string $provider): array
    {
        $providerUrl = $this->resolveSsoProviderUrl($provider);
        if ($providerUrl === '') {
            return [];
        }

        $discoveryUrl = $this->normalizeOidcDiscoveryUrl($providerUrl);
        if ($discoveryUrl === '') {
            return [];
        }

        foreach ($this->candidateContainerUrls($discoveryUrl) as $candidateUrl) {
            try {
                $response = Http::acceptJson()
                    ->timeout(15)
                    ->get($candidateUrl);

                if (!$response->ok()) {
                    continue;
                }

                $document = $response->json();
                if (is_array($document)) {
                    return $document;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return [];
    }

    private function normalizeOidcDiscoveryUrl(string $providerUrl): string
    {
        $providerUrl = trim($providerUrl);
        if ($providerUrl === '') {
            return '';
        }

        if (str_contains($providerUrl, '/.well-known/openid-configuration')) {
            return $providerUrl;
        }

        return rtrim($providerUrl, '/') . '/.well-known/openid-configuration';
    }

    private function decodeJwtPayload(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) < 2) {
            return [];
        }

        $payload = $parts[1];
        $payload .= str_repeat('=', (4 - (strlen($payload) % 4)) % 4);
        $decoded = base64_decode(strtr($payload, '-_', '+/'), true);
        if ($decoded === false) {
            return [];
        }

        $claims = json_decode($decoded, true);
        return is_array($claims) ? $claims : [];
    }

    private function candidateContainerUrls(string $url): array
    {
        $url = trim($url);
        if ($url === '') {
            return [];
        }

        $candidates = [$url];

        if (str_contains($url, '://localhost:')) {
            $candidates[] = str_replace('://localhost:', '://host.docker.internal:', $url);
        }

        if (str_contains($url, '://127.0.0.1:')) {
            $candidates[] = str_replace('://127.0.0.1:', '://host.docker.internal:', $url);
        }

        return array_values(array_unique($candidates));
    }

    private function resolveContainerAccessibleUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (str_contains($url, '://localhost:')) {
            return str_replace('://localhost:', '://host.docker.internal:', $url);
        }

        if (str_contains($url, '://127.0.0.1:')) {
            return str_replace('://127.0.0.1:', '://host.docker.internal:', $url);
        }

        return $url;
    }

    private function normalizeBrowserFacingUrl(string $url, string $providerUrl): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        $providerHost = parse_url($providerUrl, PHP_URL_HOST);
        $providerScheme = parse_url($providerUrl, PHP_URL_SCHEME) ?: 'http';
        $providerPort = parse_url($providerUrl, PHP_URL_PORT);

        if (in_array($providerHost, ['localhost', '127.0.0.1'], true)) {
            $url = str_replace('://host.docker.internal', '://' . $providerHost, $url);
        }

        if ($providerPort !== null && preg_match('/^https?:\/\/[^\/]+/', $url) === 1) {
            $url = preg_replace('/^https?:\/\/[^\/]+/', $providerScheme . '://' . $providerHost . ':' . $providerPort, $url, 1);
        }

        return $url;
    }

    private function ssoStateSessionKey(string $provider): string
    {
        return 'sso_state_oidc_' . str_replace('-', '_', strtolower(trim($provider)));
    }

    private function resolveSsoProviderUrl(string $provider): string
    {
        $urlFromEnvOrSettings = $this->resolveSsoProviderValue('sso_provider_urls', $provider, $this->getEnvValue($this->providerEnvKeyPrefix($provider) . '_URL', ''));
        
        // Auto-construct Azure AD / Microsoft URLs from tenant ID if URL not explicitly provided
        if ($urlFromEnvOrSettings === '' && in_array($provider, ['azure-ad', 'microsoft'], true)) {
            $tenantId = trim((string) $this->getEnvValue($this->providerEnvKeyPrefix($provider) . '_TENANT_ID', ''));
            if ($tenantId !== '') {
                return "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize";
            }
        }
        
        return $urlFromEnvOrSettings;
    }

    private function resolveSsoProviderSecret(string $provider, string $fallback): string
    {
        return $this->resolveSsoProviderValue('sso_provider_client_secrets', $provider, $fallback);
    }

    private function resolveSsoProviderValue(string $settingKey, string $provider, string $fallback): string
    {
        $storedValue = AdminSetting::getValue($settingKey, null);
        if ($storedValue === null || $storedValue === '') {
            return $fallback;
        }

        if (!is_array($storedValue)) {
            $decoded = json_decode((string) $storedValue, true);
            $storedValue = is_array($decoded) ? $decoded : [];
        }

        return trim((string) ($storedValue[$provider] ?? $fallback));
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

    private function resolveSsoEnabledFlag(): bool
    {
        $storedValue = AdminSetting::getValue('sso_enabled', null);
        $source = $storedValue !== null ? (string) $storedValue : $this->getEnvValue('SSO_ENABLED', 'false');

        return filter_var($source, FILTER_VALIDATE_BOOL);
    }

    private function resolveSsoEnabledProviders(array $providerKeys): array
    {
        $storedProviders = $this->resolveStoredSsoProviders($providerKeys);
        if (!empty($storedProviders)) {
            return $storedProviders;
        }

        return $this->resolveEnabledSsoProvidersFromEnvironment($providerKeys);
    }

    private function resolveStoredSsoProviders(array $providerKeys): array
    {
        $storedValue = AdminSetting::getValue('sso_enabled_providers', null);
        if ($storedValue === null || $storedValue === '') {
            return [];
        }

        if (is_array($storedValue)) {
            $providers = $storedValue;
        } else {
            $providers = json_decode((string) $storedValue, true);
            if (!is_array($providers)) {
                $providers = array_map('trim', explode(',', (string) $storedValue));
            }
        }

        return collect($providers)
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

    private function isEmailRegistrationDisabled(): bool
    {
        $value = (string) AdminSetting::getValue('disable_email_registration', 'false');

        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
