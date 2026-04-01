# API Documentation

## 1. Base URL

- Gateway URL: http://localhost:8002

## 2. Authentication Types

- Session Bearer Token (auth.session)
- PAT Bearer Token (auth.pat)
- Public endpoints (no auth middleware)

## 3. Indexed Endpoints

### 3.1 Auth

1. POST /auth/register
2. POST /auth/login
3. POST /auth/logout

### 3.2 Token Validation

4. POST /auth/validate-token
5. GET /auth/validate-token

### 3.3 PAT Tokens

6. POST /auth/pat-tokens
7. GET /auth/pat-tokens

### 3.4 Users

8. GET /users
9. POST /users
10. GET /users/{user}
11. PUT /users/{user}
12. PATCH /users/{user}
13. DELETE /users/{user}

### 3.5 Products

14. GET /products
15. POST /products
16. GET /products/{product}
17. PUT /products/{product}
18. PATCH /products/{product}
19. DELETE /products/{product}

### 3.6 File APIs

20. POST /files/upload
21. GET /files/{fileId}

### 3.7 Config File APIs

22. POST /config-files/upload
23. GET /config-files
24. GET /config-files/filter
25. GET /config-files/{fileId}
26. GET /config-files/download/{id}
27. GET /config-files/{fileId}/raw-data
28. DELETE /config-files/{fileId}

### 3.8 Service APIs

29. POST /services
30. GET /services
31. GET /services/{serviceId}

### 3.9 System Register APIs

32. POST /system-register
33. GET /system-register
34. GET /system-register/pat/{patTokenId}
35. GET /system-register/user/{userId}

### 3.10 System State APIs

36. POST /system-deregister
37. POST /system-reactive
38. GET /system-reactive
39. POST /system-deregister-force
40. GET /system-deregister-force
41. POST /system-reactivate-force
42. GET /system-reactivate-force

---

## 4. Endpoint Details With Variables and Curl

### 4.1 POST /auth/register

Variables:
- name (required)
- email (required)
- password (required)
- password_confirmation (required)
- dob (optional)

Curl:

```bash
curl -X POST "http://localhost:8002/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePassword@123",
    "password_confirmation": "SecurePassword@123",
    "dob": "1990-05-15"
  }'
```

### 4.2 POST /auth/login

Variables:
- email (required)
- password (required)

Curl:

```bash
curl -X POST "http://localhost:8002/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePassword@123"
  }'
```

### 4.3 POST /auth/logout

Headers:
- Authorization: Bearer SESSION_TOKEN

Curl:

```bash
curl -X POST "http://localhost:8002/auth/logout" \
  -H "Authorization: Bearer SESSION_TOKEN"
```

### 4.4 POST /auth/validate-token

Variables:
- token (required)

Curl:

```bash
curl -X POST "http://localhost:8002/auth/validate-token" \
  -H "Content-Type: application/json" \
  -d '{
    "token": "atgla-your-pat-token"
  }'
```

### 4.5 GET /auth/validate-token

Headers:
- Authorization: Bearer PAT_TOKEN

Curl:

```bash
curl -X GET "http://localhost:8002/auth/validate-token" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.6 POST /auth/pat-tokens

Headers:
- Authorization: Bearer SESSION_TOKEN

Variables:
- name (required)
- abilities (optional array)
- expires_at (optional)

Curl:

```bash
curl -X POST "http://localhost:8002/auth/pat-tokens" \
  -H "Authorization: Bearer SESSION_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "agent-pat",
    "abilities": ["*"],
    "expires_at": "2099-12-31 23:59:59"
  }'
```

### 4.7 GET /auth/pat-tokens

Headers:
- Authorization: Bearer SESSION_TOKEN

Curl:

```bash
curl -X GET "http://localhost:8002/auth/pat-tokens" \
  -H "Authorization: Bearer SESSION_TOKEN"
```

### 4.8 Users Resource

#### GET /users

```bash
curl -X GET "http://localhost:8002/users" \
  -H "Authorization: Bearer SESSION_TOKEN"
```

#### POST /users

Variables:
- name, email, dob, password

```bash
curl -X POST "http://localhost:8002/users" \
  -H "Authorization: Bearer SESSION_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "dob": "1992-08-20",
    "password": "SecurePassword@456"
  }'
```

#### GET /users/{user}

```bash
curl -X GET "http://localhost:8002/users/1" \
  -H "Authorization: Bearer SESSION_TOKEN"
```

#### PUT/PATCH /users/{user}

Variables:
- name (optional)
- email (optional)
- dob (optional)
- password (optional)

```bash
curl -X PUT "http://localhost:8002/users/1" \
  -H "Authorization: Bearer SESSION_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Updated"
  }'
```

#### DELETE /users/{user}

```bash
curl -X DELETE "http://localhost:8002/users/1" \
  -H "Authorization: Bearer SESSION_TOKEN"
```

### 4.9 Products Resource

#### GET /products

```bash
curl -X GET "http://localhost:8002/products" \
  -H "Authorization: Bearer SESSION_TOKEN"
```

#### POST /products

Variables:
- name (required)
- sku (required)
- price_cents (required)

```bash
curl -X POST "http://localhost:8002/products" \
  -H "Authorization: Bearer SESSION_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Product A",
    "sku": "SKU-001",
    "price_cents": 1299
  }'
```

#### GET /products/{product}

```bash
curl -X GET "http://localhost:8002/products/1" \
  -H "Authorization: Bearer SESSION_TOKEN"
```

#### PUT/PATCH /products/{product}

```bash
curl -X PATCH "http://localhost:8002/products/1" \
  -H "Authorization: Bearer SESSION_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "price_cents": 1499
  }'
```

#### DELETE /products/{product}

```bash
curl -X DELETE "http://localhost:8002/products/1" \
  -H "Authorization: Bearer SESSION_TOKEN"
```

### 4.10 POST /files/upload

Headers:
- Authorization: Bearer PAT_TOKEN

Variables:
- file_name (required)
- file_data (required)

```bash
curl -X POST "http://localhost:8002/files/upload" \
  -H "Authorization: Bearer PAT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "file_name": "nginx.conf",
    "file_data": "server { listen 80; }"
  }'
```

### 4.11 GET /files/{fileId}

```bash
curl -X GET "http://localhost:8002/files/1" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.12 POST /config-files/upload

Headers:
- Authorization: Bearer PAT_TOKEN
- Content-Type: multipart/form-data

Variables:
- file (required)
- service_id (optional)
- service_name (optional)
- system_id (optional)
- system_register_id (optional)
- system_hash (optional)
- org_id (optional)
- share_with (optional)
- validation_hash (optional)
- version (optional; server resolves next version)

```bash
curl -X POST "http://localhost:8002/config-files/upload" \
  -H "Authorization: Bearer PAT_TOKEN" \
  -F "file=@./nginx.conf" \
  -F "service_id=100" \
  -F "validation_hash=abc123"
```

### 4.13 GET /config-files

```bash
curl -X GET "http://localhost:8002/config-files" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.14 GET /config-files/filter

Variables:
- system_id (required)
- validation_hash (required)

```bash
curl -X GET "http://localhost:8002/config-files/filter?system_id=10&validation_hash=abc123" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.15 GET /config-files/{fileId}

```bash
curl -X GET "http://localhost:8002/config-files/1" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.16 GET /config-files/download/{id}

Variables:
- system_id (required)

```bash
curl -X GET "http://localhost:8002/config-files/download/1?system_id=10" \
  -H "Authorization: Bearer PAT_TOKEN" \
  -o downloaded.conf
```

### 4.17 GET /config-files/{fileId}/raw-data

```bash
curl -X GET "http://localhost:8002/config-files/1/raw-data" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.18 DELETE /config-files/{fileId}

```bash
curl -X DELETE "http://localhost:8002/config-files/1" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.19 POST /services

Variables:
- service_name (required)
- system_id (required)
- system_hash (optional)
- org_id (optional)
- share_with (optional)
- status (optional)

```bash
curl -X POST "http://localhost:8002/services" \
  -H "Authorization: Bearer PAT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "service_name": "nginx",
    "system_id": 10,
    "status": "active"
  }'
```

### 4.20 GET /services

Query variables:
- system_id (optional)

```bash
curl -X GET "http://localhost:8002/services?system_id=10" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.21 GET /services/{serviceId}

```bash
curl -X GET "http://localhost:8002/services/100" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.22 POST /system-register

Variables:
- system_name (required)
- os_type (required)
- ip_address (required)
- tags (optional)
- metadata (optional)
- validation_hash (optional)

```bash
curl -X POST "http://localhost:8002/system-register" \
  -H "Authorization: Bearer PAT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "system_name": "srv-01",
    "os_type": "ubuntu",
    "ip_address": "10.0.0.5",
    "tags": "prod,web",
    "validation_hash": "abc123"
  }'
```

### 4.23 GET /system-register

```bash
curl -X GET "http://localhost:8002/system-register" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.24 GET /system-register/pat/{patTokenId}

```bash
curl -X GET "http://localhost:8002/system-register/pat/1" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.25 GET /system-register/user/{userId}

```bash
curl -X GET "http://localhost:8002/system-register/user/1" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.26 POST /system-deregister

Variables:
- systemId or system_id (required by behavior)
- password or pin (at least one required)

```bash
curl -X POST "http://localhost:8002/system-deregister" \
  -H "Authorization: Bearer PAT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "system_id": 10,
    "pin": "12345"
  }'
```

### 4.27 POST /system-reactive

Variables:
- systemId or system_id
- password or pin

```bash
curl -X POST "http://localhost:8002/system-reactive" \
  -H "Authorization: Bearer PAT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "systemId": 10,
    "password": "SecurePassword@123"
  }'
```

### 4.28 GET /system-reactive

```bash
curl -X GET "http://localhost:8002/system-reactive?system_id=10&pin=12345" \
  -H "Authorization: Bearer PAT_TOKEN"
```

### 4.29 POST /system-deregister-force

Headers:
- Authorization: Bearer SESSION_TOKEN

Variables:
- systemId or system_id
- password or pin

```bash
curl -X POST "http://localhost:8002/system-deregister-force" \
  -H "Authorization: Bearer SESSION_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "system_id": 10,
    "pin": "12345"
  }'
```

### 4.30 GET /system-deregister-force

```bash
curl -X GET "http://localhost:8002/system-deregister-force?systemId=10&password=SecurePassword@123" \
  -H "Authorization: Bearer SESSION_TOKEN"
```

### 4.31 POST /system-reactivate-force

Headers:
- Authorization: Bearer SESSION_TOKEN

Variables:
- systemId or system_id
- password or pin

```bash
curl -X POST "http://localhost:8002/system-reactivate-force" \
  -H "Authorization: Bearer SESSION_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "system_id": 10,
    "pin": "12345"
  }'
```

### 4.32 GET /system-reactivate-force

```bash
curl -X GET "http://localhost:8002/system-reactivate-force?systemId=10&password=SecurePassword@123" \
  -H "Authorization: Bearer SESSION_TOKEN"
```

---

## 5. Variable Name Master List

- name
- email
- password
- password_confirmation
- dob
- token
- abilities
- expires_at
- sku
- price_cents
- file_name
- file_data
- file
- system_register_id
- system_id
- systemId
- service_id
- service_name
- system_hash
- org_id
- share_with
- validation_hash
- version
- tags
- metadata
- os_type
- ip_address
- pin
- status
