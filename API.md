# API Documentation (Updated)

Base URL: http://localhost:8002

This document is synchronized with the current routes in `composer/routes/api.php` and current validation rules in API controllers.

## Authentication Type Summary

- `auth.session` (session bearer token):
  - POST `/auth/logout`
  - POST `/auth/pat-tokens`
  - GET `/auth/pat-tokens`
  - POST `/system-deregister-force`
  - GET `/system-deregister-force`
  - POST `/system-reactivate-force`
  - GET `/system-reactivate-force`

- `auth.pat` (PAT bearer token):
  - All file/config/service/system operational endpoints

- Public (no auth middleware):
  - POST `/auth/register`
  - POST `/auth/login`
  - POST `/auth/validate-token`
  - GET `/auth/validate-token`

---

## 1) Auth APIs

### POST /auth/register

Request body variables:
- `name` (required, string)
- `email` (required, email)
- `password` (required, string, min 8)
- `password_confirmation` (required because `password` is confirmed)
- `dob` (optional, date)

### POST /auth/login

Request body variables:
- `email` (required, email)
- `password` (required, string, min 8)

### POST /auth/logout

Headers:
- `Authorization: Bearer {session_token}`

Request body variables:
- none

---

## 2) Token Validation APIs

### POST /auth/validate-token

Request body variables:
- `token` (required, string)

### GET /auth/validate-token

Headers:
- `Authorization: Bearer {pat_token}`

Request body variables:
- none

Query variables:
- none

---

## 3) PAT Token APIs

### POST /auth/pat-tokens

Headers:
- `Authorization: Bearer {session_token}`

Request body variables:
- `name` (required, string)
- `abilities` (optional, array)
- `abilities[]` (optional, string values)
- `expires_at` (optional, nullable, date)

### GET /auth/pat-tokens

Headers:
- `Authorization: Bearer {session_token}`

Request body variables:
- none

Query variables:
- none

---

## 4) User APIs (resource)

### GET /users

Request variables:
- none

### POST /users

Request body variables:
- `name` (required, string)
- `email` (required, email)
- `dob` (required, date)
- `password` (required, string, min 8)

### GET /users/{user}

Path variables:
- `user` (required, route model id)

### PUT/PATCH /users/{user}

Path variables:
- `user` (required, route model id)

Request body variables:
- `name` (optional, string)
- `email` (optional, email)
- `dob` (optional, date)
- `password` (optional, string, min 8)

### DELETE /users/{user}

Path variables:
- `user` (required, route model id)

Request variables:
- none

---

## 5) Product APIs (resource)

### GET /products

Request variables:
- none

### POST /products

Request body variables:
- `name` (required, string)
- `sku` (required, string)
- `price_cents` (required, integer, min 0)

### GET /products/{product}

Path variables:
- `product` (required, route model id)

### PUT/PATCH /products/{product}

Path variables:
- `product` (required, route model id)

Request body variables:
- `name` (optional, string)
- `sku` (optional, string)
- `price_cents` (optional, integer, min 0)

### DELETE /products/{product}

Path variables:
- `product` (required, route model id)

Request variables:
- none

---

## 6) File APIs

### POST /files/upload

Headers:
- `Authorization: Bearer {pat_token}`

Request body variables:
- `file_name` (required, string)
- `file_data` (required, string)

### GET /files/{fileId}

Headers:
- `Authorization: Bearer {pat_token}`

Path variables:
- `fileId` (required)

---

## 7) Configuration File APIs

### POST /config-files/upload

Headers:
- `Authorization: Bearer {pat_token}`
- `Content-Type: multipart/form-data`

Request body variables:
- `file` (required, uploaded file)
- `system_register_id` (optional, integer)
- `system_id` (optional, integer)
- `service_id` (optional, integer)
- `service_name` (optional, string)
- `system_hash` (optional, string)
- `org_id` (optional, integer)
- `share_with` (optional, string)
- `validation_hash` (optional, string)
- `version` (optional, string; currently ignored for persistence because server resolves version)

Notes:
- If `service_id` is not provided, then `service_name` plus (`system_id` or `system_register_id`) is required.
- Stored version is resolved server-side as next label (`v1`, `v2`, ...).

### GET /config-files

Headers:
- `Authorization: Bearer {pat_token}`

Request/query variables:
- none

### GET /config-files/filter

Headers:
- `Authorization: Bearer {pat_token}`

Query variables:
- `system_id` (required, integer)
- `validation_hash` (required, string)

### GET /config-files/{fileId}

Headers:
- `Authorization: Bearer {pat_token}`

Path variables:
- `fileId` (required)

### GET /config-files/download/{id}

Headers:
- `Authorization: Bearer {pat_token}`

Path variables:
- `id` (required)

Query or body variables:
- `system_id` (required, integer)

### GET /config-files/{fileId}/raw-data

Headers:
- `Authorization: Bearer {pat_token}`

Path variables:
- `fileId` (required)

### DELETE /config-files/{fileId}

Headers:
- `Authorization: Bearer {pat_token}`

Path variables:
- `fileId` (required)

---

## 8) Services APIs

### POST /services

Headers:
- `Authorization: Bearer {pat_token}`

Request body variables:
- `service_name` (required, string)
- `system_id` (required, integer)
- `system_hash` (optional, string)
- `org_id` (optional, integer)
- `share_with` (optional, string)
- `status` (optional, string)

### GET /services

Headers:
- `Authorization: Bearer {pat_token}`

Query variables:
- `system_id` (optional, integer)

### GET /services/{serviceId}

Headers:
- `Authorization: Bearer {pat_token}`

Path variables:
- `serviceId` (required)

---

## 9) System Registration APIs

### POST /system-register

Headers:
- `Authorization: Bearer {pat_token}`

Request body variables:
- `system_name` (required, string)
- `os_type` (required, string)
- `ip_address` (required, string)
- `tags` (optional, string)
- `metadata` (optional, string)
- `validation_hash` (optional, string)

### GET /system-register

Headers:
- `Authorization: Bearer {pat_token}`

Request/query variables:
- none

### GET /system-register/pat/{patTokenId}

Headers:
- `Authorization: Bearer {pat_token}`

Path variables:
- `patTokenId` (required)

### GET /system-register/user/{userId}

Headers:
- `Authorization: Bearer {pat_token}`

Path variables:
- `userId` (required)

---

## 10) System Deregistration / Reactivation APIs

For all endpoints below, `systemId` and `system_id` are both accepted aliases.

Credential variables for state-change endpoints:
- `password` (optional)
- `pin` (optional)
- at least one of `password` or `pin` is required

### POST /system-deregister

Headers:
- `Authorization: Bearer {pat_token}`

Request body/query variables:
- `systemId` (optional alias)
- `system_id` (optional alias)
- `password` (optional)
- `pin` (optional)

### POST /system-reactive
### GET /system-reactive

Headers:
- `Authorization: Bearer {pat_token}`

Request body/query variables:
- `systemId` (optional alias)
- `system_id` (optional alias)
- `password` (optional)
- `pin` (optional)

### POST /system-deregister-force
### GET /system-deregister-force

Headers:
- `Authorization: Bearer {session_token}`

Request body/query variables:
- `systemId` (optional alias)
- `system_id` (optional alias)
- `password` (optional)
- `pin` (optional)

### POST /system-reactivate-force
### GET /system-reactivate-force

Headers:
- `Authorization: Bearer {session_token}`

Request body/query variables:
- `systemId` (optional alias)
- `system_id` (optional alias)
- `password` (optional)
- `pin` (optional)

---

## 11) Quick Variable Index

Core auth:
- `name`, `email`, `password`, `password_confirmation`, `dob`

Token management:
- `token`, `abilities`, `expires_at`

System/service/config:
- `system_name`, `os_type`, `ip_address`, `tags`, `metadata`
- `systemId`, `system_id`, `system_register_id`
- `service_id`, `service_name`, `system_hash`, `org_id`, `share_with`
- `validation_hash`, `version`
- `file`, `file_name`, `file_data`

Credential for sensitive operations:
- `password` or `pin`
