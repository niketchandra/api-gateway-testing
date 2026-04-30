# 🔒 HTTPS Security Issues - Summary & Quick Reference

## Overview
Your application has **7 critical/important HTTPS configuration issues** causing browser "connection is not secure" warnings. All issues are fixable through configuration changes.

---

## Issues At a Glance

| # | Issue | Severity | File | Current | Required | Impact |
|---|-------|----------|------|---------|----------|--------|
| 1 | APP_URL uses HTTP | 🔴 CRITICAL | `.env` | `http://0.0.0.0` | `https://yourdomain.com` | Forms submit insecurely |
| 2 | HTTPS not enforced | 🔴 CRITICAL | `.env` | `APP_FORCE_HTTPS=false` | `APP_FORCE_HTTPS=true` | HTTP access allowed |
| 3 | Kong no HTTPS | 🔴 CRITICAL | `docker-compose-kong.yml` | Port 8002 only | Add port 8443 | No HTTPS endpoint |
| 4 | No SSL certificates | 🔴 CRITICAL | Not exists | Missing | Generate/install | Browsers reject connection |
| 5 | Insecure cookies | ⚠️ IMPORTANT | `.env` | `SESSION_SECURE_COOKIE=` | `SESSION_SECURE_COOKIE=true` | Cookies sent over HTTP |
| 6 | Kong SSL config | ⚠️ IMPORTANT | `kong/kong.yml` | No certs | Add certificates | Connection fails |
| 7 | Missing proxy headers | ⚠️ MODERATE | `bootstrap/app.php` | Not configured | Add middleware | Redirect loops |

---

## Critical Path (Do These First)

### 1️⃣ Generate SSL Certificate (5 minutes)
```bash
mkdir -p kong/config
openssl req -x509 -newkey rsa:2048 -keyout kong/config/server.key \
  -out kong/config/server.crt -days 365 -nodes \
  -subj "/CN=localhost"
```

### 2️⃣ Fix `.env` (2 minutes)
```env
# Change FROM:
APP_URL=http://0.0.0.0
APP_FORCE_HTTPS=false
SESSION_SECURE_COOKIE=

# Change TO:
APP_URL=https://localhost:8443
APP_FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
TRUSTED_PROXIES=*
```

### 3️⃣ Update Kong Docker Config (5 minutes)
In `docker-compose-kong.yml`, add to `kong` service environment:
```yaml
KONG_PROXY_LISTEN: 0.0.0.0:8002, 0.0.0.0:8443 ssl
KONG_SSL_CERT: /kong/declarative/config/server.crt
KONG_SSL_CERT_KEY: /kong/declarative/config/server.key
```

And add port:
```yaml
ports:
  - "8443:8443"  # ← Add this line
```

### 4️⃣ Restart Docker (2 minutes)
```bash
docker compose -f docker-compose.yml -f docker-compose-kong.yml down
docker compose -f docker-compose.yml -f docker-compose-kong.yml up -d --build
```

### ✅ Verify
```bash
curl -k https://localhost:8443/
# Should return content, no "connection refused"
```

---

## Issue Detail View

### Issue #1: HTTP in APP_URL 🔴

**Location**: `composer/.env` line 5
```env
APP_URL=http://0.0.0.0
```

**Why it matters**:
- Laravel's `route()` helper generates URLs using this base
- Login form uses `{{ route('login') }}` → generates `http://...`
- Browser sees form action on HTTP → security warning

**Solution**:
```env
# Development
APP_URL=https://localhost:8443

# Production
APP_URL=https://yourdomain.com
```

**Verify**:
```
1. Open app
2. Right-click login form → Inspect
3. Look for: <form action="https://..." (should be HTTPS)
```

---

### Issue #2: HTTPS Not Enforced 🔴

**Location**: `composer/.env` line 112
```env
APP_FORCE_HTTPS=false
```

**Why it matters**:
- Users can access site via insecure HTTP
- No automatic redirect to HTTPS
- Credentials transmitted unencrypted

**Solution**:
```env
APP_FORCE_HTTPS=true
```

**Verify**:
```bash
# Try accessing HTTP
curl http://localhost:8002/
# Should redirect to HTTPS or refuse
```

---

### Issue #3: Kong Only Listens on HTTP 🔴

**Location**: `docker-compose-kong.yml` lines 7-8
```yaml
KONG_PROXY_LISTEN: 0.0.0.0:8002
```

**Why it matters**:
- Kong API gateway only accepts HTTP connections
- No HTTPS endpoint available
- Browsers can't connect securely

**Solution**: Update docker-compose-kong.yml
```yaml
# BEFORE
KONG_PROXY_LISTEN: 0.0.0.0:8002

# AFTER
KONG_PROXY_LISTEN: 0.0.0.0:8002, 0.0.0.0:8443 ssl
```

Plus add port and SSL env vars:
```yaml
environment:
  KONG_SSL_CERT: /kong/declarative/config/server.crt
  KONG_SSL_CERT_KEY: /kong/declarative/config/server.key

ports:
  - "8443:8443"
```

**Verify**:
```bash
# Test HTTP port
curl http://localhost:8002/
# Response OK

# Test HTTPS port
curl -k https://localhost:8443/
# Response OK (with cert warning for self-signed)
```

---

### Issue #4: No SSL Certificates 🔴

**Location**: Not found (need to create)
```
kong/config/server.crt  ← Missing
kong/config/server.key  ← Missing
```

**Why it matters**:
- TLS/SSL requires valid certificates to establish secure connections
- Browsers cannot verify server identity without certificates
- Connections will fail

**Solution**: Generate self-signed certificate
```bash
mkdir -p kong/config

# Generate certificate (valid 365 days)
openssl req -x509 -newkey rsa:2048 \
  -keyout kong/config/server.key \
  -out kong/config/server.crt \
  -days 365 -nodes \
  -subj "/CN=localhost"

# Verify
ls -la kong/config/server.*
```

**For production**, use Let's Encrypt:
```bash
certbot certonly --standalone -d yourdomain.com
# Copy to: kong/config/server.crt & kong/config/server.key
```

**Verify**:
```bash
# Check files exist and are readable
cat kong/config/server.crt | head -3
# Should output: -----BEGIN CERTIFICATE-----
```

---

### Issue #5: Insecure Cookies ⚠️

**Location**: `composer/.env` line 111
```env
SESSION_SECURE_COOKIE=
```

**Why it matters**:
- Session cookies not marked as "Secure"
- Browsers send cookies over HTTP connections
- Attackers can intercept and steal session tokens
- Even if app is HTTPS, setting is missing

**Solution**:
```env
SESSION_SECURE_COOKIE=true
```

**Also update** `composer/config/session.php`:
```php
'secure' => env('SESSION_SECURE_COOKIE', true),
'same_site' => 'strict',  # Added protection
'http_only' => true,       # JS cannot access
```

**Verify**:
```
1. Login to app
2. F12 → Application → Cookies
3. Find session cookie
4. Check "Secure" column = ✅ Checked
5. Check "HttpOnly" column = ✅ Checked
```

---

### Issue #6: Kong Missing SSL Configuration ⚠️

**Location**: `kong/kong.yml` (needs addition)
```yaml
_format_version: "3.0"

services:
  - name: users-service
    # ... routes ...
```

**Why it matters**:
- Kong doesn't know which certificate to use
- TLS handshake will fail
- Browsers get "ERR_SSL_PROTOCOL_ERROR"

**Solution**: Add certificate section to Kong config
```yaml
_format_version: "3.0"

# ← ADD BELOW:
certificates:
  - id: server-cert
    cert: |
      -----BEGIN CERTIFICATE-----
      [Content of kong/config/server.crt]
      -----END CERTIFICATE-----
    key: |
      -----BEGIN PRIVATE KEY-----
      [Content of kong/config/server.key]
      -----END PRIVATE KEY-----

snis:
  - name: localhost
    certificate: server-cert

# ← EXISTING CONFIG BELOW:
services:
  - name: users-service
    ...
```

**Quick Convert**:
```bash
# Copy cert content to YAML (handles indentation)
cat kong/config/server.crt | sed 's/^/      /'
cat kong/config/server.key | sed 's/^/      /'
```

**Verify**:
```
1. docker compose logs kong | grep certificate
2. Should show: "loading certificate"
3. No SSL errors
```

---

### Issue #7: Missing Proxy Headers ⚠️

**Location**: `composer/bootstrap/app.php` (needs addition)
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->append(\App\Http\Middleware\ActivityLogger::class);
    // Missing: TrustProxies middleware
    $middleware->alias([...]);
})
```

**Why it matters**:
- Kong forwards HTTPS traffic, but doesn't tell Laravel
- Laravel thinks requests are HTTP
- Session/redirects broken, potential loops

**Solution**: Add middleware to trust proxy headers
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->append(\App\Http\Middleware\ActivityLogger::class);
    
    // ← ADD THIS:
    $middleware->appendWeb(\Illuminate\Http\Middleware\TrustProxies::class);
    
    $middleware->alias([...]);
})
```

Also add to `.env`:
```env
TRUSTED_PROXIES=172.18.0.0/16,127.0.0.1
```

**Verify**:
```bash
# Check Laravel knows about HTTPS
curl -k https://localhost:8443/ -H "X-Forwarded-Proto: https"
# Should see HTTPS in response headers
```

---

## Quick Fixes Flowchart

```
Start
  ↓
[CRITICAL ISSUES BLOCK]
  ├→ 1. Generate certificate? NO → STOP (can't continue)
  │                        YES ↓
  ├→ 2. Update .env?      NO → STOP (will fail)
  │                        YES ↓
  ├→ 3. Update docker?    NO → STOP (no port)
  │                        YES ↓
  ├→ 4. Add Kong certs?   NO → STOP (TLS fails)
  │                        YES ↓
[IMPORTANT ISSUES BLOCK]
  ├→ 5. Secure cookies?   NO → Session insecure (continue?)
  │                        YES ↓
  ├→ 6. Kong SSL config?  NO → TLS fails (continue?)
  │                        YES ↓
[MODERATE ISSUES BLOCK]
  ├→ 7. Proxy headers?    NO → Redirects broken (continue?)
  │                        YES ↓
  ↓
✅ Test & Verify
```

---

## Testing After Fixes

### Manual Testing
```bash
# 1. Check HTTPS endpoint exists
curl -k https://localhost:8443/login

# 2. Check HTTP redirects (optional)
curl -i http://localhost:8002/login | grep Location

# 3. Check form action is HTTPS
curl -k https://localhost:8443/ | grep "form action"

# 4. Test SSL certificate validity
openssl s_client -connect localhost:8443 -showcerts
```

### Browser Testing
```
1. Open https://localhost:8443
2. Check address bar: Green lock icon ✅
3. Click register → form appears
4. Check form → action="https://..." ✅
5. Submit form → redirects securely ✅
6. F12 → Network → All requests HTTPS ✅
7. F12 → Application → Cookies secure ✅
```

---

## File Summary

### Files to CREATE
- `kong/config/server.crt` - SSL certificate
- `kong/config/server.key` - SSL private key
- `HTTPS_SECURITY_ANALYSIS.md` - Detailed analysis (created)
- `HTTPS_IMPLEMENTATION_GUIDE.md` - Step-by-step guide (created)

### Files to MODIFY
1. `composer/.env`
2. `docker-compose-kong.yml`
3. `kong/kong.yml`
4. `composer/bootstrap/app.php`
5. `composer/config/session.php`

### Files to UPDATE .gitignore
```gitignore
kong/config/server.crt
kong/config/server.key
kong/config/*.pem
```

---

## Timeline

- **5 min**: Generate certificates
- **5 min**: Update .env
- **10 min**: Update Docker/Kong config
- **10 min**: Update Laravel config
- **10 min**: Test & verify
- **Total**: ~40 minutes for all fixes

---

## Support

If you get stuck:

1. **"Certificate not found"**
   → Run: `ls -la kong/config/server.* && chmod 644 kong/config/server.*`

2. **"Port 8443 refused"**
   → Run: `docker compose logs kong | grep 8443`

3. **"Mixed content warning"**
   → Check: Form action in `<form>` tag points to HTTPS

4. **"Still says not secure"**
   → Make sure: `APP_URL=https://...` in `.env`

---

**Documentation Created**: April 30, 2026
**Status**: Ready for Implementation
**Estimated Effort**: 40 minutes
**Difficulty**: Low-Medium
