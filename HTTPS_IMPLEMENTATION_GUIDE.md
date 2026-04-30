# 🔧 HTTPS Implementation Guide - Step by Step

## Phase 1: Generate SSL Certificates

### For Development (Self-Signed)

Run these commands in your project root:

```bash
# Create kong/config directory
mkdir -p kong/config

# Generate self-signed certificate valid for 1 year
openssl req -x509 -newkey rsa:2048 -keyout kong/config/server.key \
  -out kong/config/server.crt -days 365 -nodes \
  -subj "/C=US/ST=State/L=City/O=Org/CN=localhost"

# Verify certificate was created
ls -la kong/config/
```

**Expected output**:
```
kong/config/server.crt
kong/config/server.key
```

### For Production (Let's Encrypt)

```bash
# Install certbot if not already installed
# macOS: brew install certbot
# Ubuntu: sudo apt-get install certbot
# Windows: Use WSL or Docker

# Get certificate (DNS must point to your server)
certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com

# Certificates will be at:
# /etc/letsencrypt/live/yourdomain.com/fullchain.pem
# /etc/letsencrypt/live/yourdomain.com/privkey.pem

# Copy to Kong config
cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem kong/config/server.crt
cp /etc/letsencrypt/live/yourdomain.com/privkey.pem kong/config/server.key

# Set permissions
chmod 600 kong/config/server.*
```

---

## Phase 2: Update .env Configuration

### Current State
```env
APP_URL=http://0.0.0.0
APP_FORCE_HTTPS=false
SESSION_SECURE_COOKIE=
```

### Updated State (Development)
```env
APP_URL=https://localhost:8443
APP_FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=localhost
TRUSTED_PROXIES=*
```

### Updated State (Production)
```env
APP_URL=https://yourdomain.com
APP_FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=yourdomain.com
TRUSTED_PROXIES=172.18.0.0/16   # Your load balancer/reverse proxy IP range
```

---

## Phase 3: Update Docker Compose

### File: `docker-compose-kong.yml`

**BEFORE**:
```yaml
services:
  kong:
    image: kong:3.6
    environment:
      KONG_DATABASE: "off"
      KONG_DECLARATIVE_CONFIG: /kong/declarative/kong.yml
      KONG_PROXY_LISTEN: 0.0.0.0:8002
      KONG_ADMIN_LISTEN: 0.0.0.0:8001
    ports:
      - "8002:8002"
      - "8001:8001"
    volumes:
      - ./kong:/kong/declarative
    depends_on:
      - api
```

**AFTER**:
```yaml
services:
  kong:
    image: kong:3.6
    environment:
      KONG_DATABASE: "off"
      KONG_DECLARATIVE_CONFIG: /kong/declarative/kong.yml
      KONG_PROXY_LISTEN: 0.0.0.0:8002, 0.0.0.0:8443 ssl
      KONG_ADMIN_LISTEN: 0.0.0.0:8001
      KONG_SSL_CERT: /kong/declarative/config/server.crt
      KONG_SSL_CERT_KEY: /kong/declarative/config/server.key
    ports:
      - "8002:8002"
      - "8443:8443"      # ← NEW: HTTPS port
      - "8001:8001"
    volumes:
      - ./kong:/kong/declarative
    depends_on:
      - api
```

---

## Phase 4: Update Kong Configuration

### File: `kong/kong.yml`

**Add SSL certificates section** (at beginning):

```yaml
_format_version: "3.0"

# ← ADD THIS SECTION
certificates:
  - id: server-cert
    cert: |
      -----BEGIN CERTIFICATE-----
      [Your certificate content from server.crt]
      -----END CERTIFICATE-----
    key: |
      -----BEGIN PRIVATE KEY-----
      [Your key content from server.key]
      -----END PRIVATE KEY-----

snis:
  - name: localhost          # For development
    certificate: server-cert
  - name: yourdomain.com     # For production
    certificate: server-cert
# ← END NEW SECTION

services:
  - name: users-service
    url: http://api:8000/api
    ...
```

**How to format certificates**:

```bash
# Read certificate and convert for YAML
cat kong/config/server.crt | sed 's/^/      /' 

# Copy output to kong.yml (maintaining indentation)
```

**Simplified version** (just reference the files):

If Kong can read files directly:
```yaml
_format_version: "3.0"

services:
  - name: users-service
    url: http://api:8000/api
    routes: [...]
```

Then use environment variables:
```yaml
# In docker-compose-kong.yml
KONG_SSL_CERT: /kong/declarative/config/server.crt
KONG_SSL_CERT_KEY: /kong/declarative/config/server.key
```

---

## Phase 5: Add HTTP → HTTPS Redirect in Kong

### File: `kong/kong.yml`

Add redirect plugin to HTTP service:

```yaml
services:
  - name: users-service
    url: http://api:8000/api
    routes:
      - name: users-route
        paths: [/users]
        strip_path: false
        methods: [GET, POST, PUT, DELETE]
    # ← ADD THIS:
    plugins:
      - name: request-transformer
        config:
          add:
            headers:
              - "X-Forwarded-Proto:https"
      
  - name: redirect-service
    url: http://api:8000/api
    routes:
      - name: redirect-all
        paths: [/]
        strip_path: false
    plugins:
      - name: response-transformer
        config:
          add:
            headers:
              - "Strict-Transport-Security:max-age=31536000; includeSubDomains"
```

---

## Phase 6: Configure Laravel to Trust Proxies

### File: `composer/bootstrap/app.php`

**BEFORE**:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->append(\App\Http\Middleware\ActivityLogger::class);
    $middleware->alias([...]);
})
```

**AFTER**:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->append(\App\Http\Middleware\ActivityLogger::class);
    
    // ← ADD THIS: Trust reverse proxy headers
    $middleware->appendWeb(\Illuminate\Http\Middleware\TrustProxies::class);
    
    $middleware->alias([...]);
})
```

### File: `composer/config/app.php`

Add after the `'debug'` setting:

```php
'trusted_proxies' => [
    '172.18.0.0/16',        // Docker network
    '127.0.0.1',            // Localhost
    // Add your load balancer IP: '10.0.0.5',
],

'trusted_hosts' => [
    'localhost',
    '127.0.0.1',
    'yourdomain.com',
],
```

---

## Phase 7: Secure Session Configuration

### File: `composer/config/session.php`

**BEFORE** (around line 168-194):
```php
'secure' => env('SESSION_SECURE_COOKIE'),
'same_site' => 'lax',
'http_only' => true,
```

**AFTER**:
```php
'secure' => env('SESSION_SECURE_COOKIE', true),
'same_site' => 'strict',  # Stricter CSRF protection
'http_only' => true,       # No JS access to cookies
'domain' => env('SESSION_DOMAIN', null),
'path' => env('SESSION_PATH', '/'),
```

---

## Phase 8: Add Security Headers

### File: Create `composer/app/Http/Middleware/SecurityHeaders.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Force HTTPS
        $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        
        // Prevent clickjacking
        $response->header('X-Frame-Options', 'SAMEORIGIN');
        
        // Prevent MIME type sniffing
        $response->header('X-Content-Type-Options', 'nosniff');
        
        // Enable XSS protection
        $response->header('X-XSS-Protection', '1; mode=block');
        
        // Content Security Policy
        $response->header('Content-Security-Policy', "default-src 'self'");
        
        // Referrer Policy
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions Policy
        $response->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}
```

### Register middleware in `composer/bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->append(\App\Http\Middleware\ActivityLogger::class);
    $middleware->appendWeb(\Illuminate\Http\Middleware\TrustProxies::class);
    $middleware->appendWeb(\App\Http\Middleware\SecurityHeaders::class);  # ← ADD
    $middleware->alias([...]);
})
```

---

## Phase 9: Update .gitignore

### File: `composer/.gitignore`

Add certificate files (they contain secrets):

```gitignore
# SSL/TLS Certificates
kong/config/server.crt
kong/config/server.key
kong/config/*.pem

# Environment secrets
.env.local
.env.*.local

# System files
.DS_Store
Thumbs.db
```

---

## Testing Checklist

### Step 1: Rebuild Docker Containers
```bash
# Stop old containers
docker compose -f docker-compose.yml -f docker-compose-kong.yml down

# Rebuild with new config
docker compose -f docker-compose.yml -f docker-compose-kong.yml up -d --build

# Check Kong started
docker compose logs kong
```

### Step 2: Test HTTP Redirect (Optional)
```bash
# Should redirect to port 8443
curl -i http://localhost:8002/login
# Expected: 301 redirect to https://localhost:8443

# Direct HTTPS access
curl -k https://localhost:8443/login
# Expected: 200 OK (ignore cert warning for self-signed)
```

### Step 3: Test Form Submission
1. Open browser: `https://localhost:8443`
2. Check address bar: **Green lock icon** ✅
3. Try login/register form
4. Check browser console: **No mixed content warnings** ✅

### Step 4: Check Session Cookies
1. Open DevTools (F12) → Storage → Cookies
2. Verify session cookie shows:
   - ✅ `Secure` flag
   - ✅ `HttpOnly` flag
   - ✅ `SameSite: Strict`

---

## Troubleshooting

### "Certificate not found" Error
```bash
# Check certificate files exist
ls -la kong/config/server.crt kong/config/server.key

# Check permissions
chmod 644 kong/config/server.crt
chmod 600 kong/config/server.key

# Restart Kong
docker compose restart kong
```

### "Connection refused" on 8443
```bash
# Check Kong is listening
docker compose logs kong | grep "8443"

# Check port mapping
docker compose ps kong
```

### Browser still shows "Not Secure"
```bash
# For self-signed cert, add exception in browser
# Chrome: Proceed (Advanced) → proceed despite warning
# Firefox: Add Security Exception

# For production, use Let's Encrypt (no warnings)
```

### Forms still submit to HTTP
```bash
# Check APP_URL in .env
grep APP_URL composer/.env

# Verify it starts with https://
```

### Mixed Content Warning
```bash
# Check Kong forwards X-Forwarded-Proto header
docker compose logs kong | grep X-Forwarded-Proto

# Ensure TRUSTED_PROXIES is set in Laravel
grep TRUSTED_PROXIES composer/.env
```

---

## Timeline & Effort

| Phase | Task | Time | Difficulty |
|-------|------|------|-----------|
| 1 | Generate certificates | 5 min | Easy |
| 2 | Update .env | 5 min | Easy |
| 3 | Update docker-compose | 10 min | Easy |
| 4 | Update kong.yml | 15 min | Medium |
| 5 | Add Kong plugins | 10 min | Easy |
| 6 | Laravel proxy config | 10 min | Easy |
| 7 | Session config | 5 min | Easy |
| 8 | Security headers | 10 min | Medium |
| 9 | Testing & validation | 20 min | Medium |
| **Total** | | **90 min** | |

---

## Next Steps

1. ✅ Review HTTPS_SECURITY_ANALYSIS.md
2. ✅ Generate SSL certificates (Phase 1)
3. ✅ Update .env environment
4. ✅ Update Docker Compose
5. ✅ Update Kong configuration
6. ✅ Rebuild and test

**Questions?** Each phase includes troubleshooting commands and expected outputs.

---

**Last Updated**: April 30, 2026
