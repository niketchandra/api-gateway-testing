# API Gateway Documentation

**Base URL**: `http://localhost:8002`  
**API Gateway**: Kong (Proxy)  
**Backend API**: Laravel (Port 8000)

---

## Table of Contents

1. [Authentication](#authentication)
2. [User Management](#user-management)
<!-- 3. [Product Management](#product-management) -->
3. [PAT Token Management](#pat-token-management)
4. [System Registration](#system-registration)
5. [Configuration File Management](#configuration-file-management)
6. [File Operations](#file-operations)

---

## Authentication

### Register User

**Endpoint**: `POST /auth/register`

**Description**: Create a new user account

**Headers**:
```
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePassword@123",
  "dob": "1990-05-15"
}
```

**Response** (201):
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "dob": "1990-05-15T00:00:00.000000Z",
  "created_at": "2026-02-27T20:01:05.000000Z",
  "updated_at": "2026-02-27T20:01:05.000000Z",
  "id": 1
}
```

**Example**:
```bash
curl -X POST http://localhost:8002/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePassword@123",
    "dob": "1990-05-15"
  }'
```

---

### Login User

**Endpoint**: `POST /auth/login`

**Description**: Login with email and password to get session token

**Headers**:
```
Content-Type: application/json
```

**Request Body**:
```json
{
  "email": "john@example.com",
  "password": "SecurePassword@123"
}
```

**Response** (200):
```json
{
  "message": "Login successful",
  "session_token": "bearer_token_value",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Example**:
```bash
curl -X POST http://localhost:8002/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePassword@123"
  }'
```

---

### Logout User

**Endpoint**: `POST /auth/logout`

**Description**: Logout and invalidate session token

**Headers**:
```
Authorization: Bearer {session_token}
```

**Response** (200):
```json
{
  "message": "Logged out successfully"
}
```

**Example**:
```bash
curl -X POST http://localhost:8002/auth/logout \
  -H "Authorization: Bearer {session_token}"
```

---

## User Management

### Get All Users

**Endpoint**: `GET /users`

**Description**: Retrieve all users (requires session token)

**Headers**:
```
Authorization: Bearer {session_token}
```

**Response** (200):
```json
[
  {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "dob": "1990-05-15T00:00:00.000000Z",
    "created_at": "2026-02-27T20:01:05.000000Z",
    "updated_at": "2026-02-27T20:01:05.000000Z"
  }
]
```

**Example**:
```bash
curl -X GET http://localhost:8002/users \
  -H "Authorization: Bearer {session_token}"
```

---

### Get Single User

**Endpoint**: `GET /users/{userId}`

**Description**: Retrieve a specific user by ID

**Headers**:
```
Authorization: Bearer {session_token}
```

**Response** (200):
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "dob": "1990-05-15T00:00:00.000000Z",
  "created_at": "2026-02-27T20:01:05.000000Z",
  "updated_at": "2026-02-27T20:01:05.000000Z"
}
```

**Example**:
```bash
curl -X GET http://localhost:8002/users/1 \
  -H "Authorization: Bearer {session_token}"
```

---

### Create User

**Endpoint**: `POST /users`

**Description**: Create a new user (with session token)

**Headers**:
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "SecurePassword@456",
  "dob": "1992-08-20"
}
```

**Response** (201):
```json
{
  "id": 2,
  "name": "Jane Smith",
  "email": "jane@example.com",
  "dob": "1992-08-20T00:00:00.000000Z",
  "created_at": "2026-02-27T21:00:00.000000Z",
  "updated_at": "2026-02-27T21:00:00.000000Z"
}
```

**Example**:
```bash
curl -X POST http://localhost:8002/users \
  -H "Authorization: Bearer {session_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "SecurePassword@456",
    "dob": "1992-08-20"
  }'
```

---

### Update User

**Endpoint**: `PUT /users/{userId}`

**Description**: Update user information

**Headers**:
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "John Updated",
  "email": "john.updated@example.com",
  "dob": "1990-05-15"
}
```

**Response** (200):
```json
{
  "message": "User updated successfully",
  "user": {
    "id": 1,
    "name": "John Updated",
    "email": "john.updated@example.com",
    "dob": "1990-05-15T00:00:00.000000Z"
  }
}
```

**Example**:
```bash
curl -X PUT http://localhost:8002/users/1 \
  -H "Authorization: Bearer {session_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Updated",
    "email": "john.updated@example.com",
    "dob": "1990-05-15"
  }'
```

---

### Delete User

**Endpoint**: `DELETE /users/{userId}`

**Description**: Delete a user account

**Headers**:
```
Authorization: Bearer {session_token}
```

**Response** (204):
```
No content
```

**Example**:
```bash
curl -X DELETE http://localhost:8002/users/1 \
  -H "Authorization: Bearer {session_token}"
```

<!-- ---

## Product Management

### Get All Products

**Endpoint**: `GET /products`

**Description**: Retrieve all products

**Headers**:
```
Authorization: Bearer {session_token}
```

**Response** (200):
```json
[
  {
    "id": 1,
    "name": "Product A",
    "description": "Description of Product A",
    "price": 99.99,
    "created_at": "2026-02-27T20:05:00.000000Z",
    "updated_at": "2026-02-27T20:05:00.000000Z"
  }
]
```

**Example**:
```bash
curl -X GET http://localhost:8002/products \
  -H "Authorization: Bearer {session_token}"
```

---

### Get Single Product

**Endpoint**: `GET /products/{productId}`

**Description**: Retrieve a specific product by ID

**Headers**:
```
Authorization: Bearer {session_token}
```

**Response** (200):
```json
{
  "id": 1,
  "name": "Product A",
  "description": "Description of Product A",
  "price": 99.99,
  "created_at": "2026-02-27T20:05:00.000000Z",
  "updated_at": "2026-02-27T20:05:00.000000Z"
}
```

**Example**:
```bash
curl -X GET http://localhost:8002/products/1 \
  -H "Authorization: Bearer {session_token}"
```

---

### Create Product

**Endpoint**: `POST /products`

**Description**: Create a new product

**Headers**:
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "New Product",
  "description": "Product description",
  "price": 149.99
}
```

**Response** (201):
```json
{
  "id": 2,
  "name": "New Product",
  "description": "Product description",
  "price": 149.99,
  "created_at": "2026-02-27T21:10:00.000000Z",
  "updated_at": "2026-02-27T21:10:00.000000Z"
}
```

**Example**:
```bash
curl -X POST http://localhost:8002/products \
  -H "Authorization: Bearer {session_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Product",
    "description": "Product description",
    "price": 149.99
  }'
```

 ---

### Update Product

**Endpoint**: `PUT /products/{productId}`

**Description**: Update product information

**Headers**:
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "Updated Product",
  "description": "Updated description",
  "price": 199.99
}
```

**Response** (200):
```json
{
  "id": 1,
  "name": "Updated Product",
  "description": "Updated description",
  "price": 199.99,
  "created_at": "2026-02-27T20:05:00.000000Z",
  "updated_at": "2026-02-27T21:15:00.000000Z"
}
```

**Example**:
```bash
curl -X PUT http://localhost:8002/products/1 \
  -H "Authorization: Bearer {session_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Product",
    "description": "Updated description",
    "price": 199.99
  }'
```

---

### Delete Product

**Endpoint**: `DELETE /products/{productId}`

**Description**: Delete a product

**Headers**:
```
Authorization: Bearer {session_token}
```

**Response** (204):
```
No content
```

**Example**:
```bash
curl -X DELETE http://localhost:8002/products/1 \
  -H "Authorization: Bearer {session_token}"
``` -->

---

## PAT Token Management

### Create PAT Token

**Endpoint**: `POST /auth/pat-tokens`

**Description**: Generate a new Personal Access Token (requires session token)

**Headers**:
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "name": "API Token",
  "abilities": ["*"],
  "expires_at": "2026-12-31"
}
```

**Response** (201):
```json
{
  "message": "PAT token created successfully",
  "token": "atgla-xPyt2TeLn3TbbalkBMN",
  "token_details": {
    "id": 1,
    "name": "API Token",
    "abilities": ["*"],
    "expires_at": "2026-12-31",
    "created_at": "2026-02-27T21:20:00.000000Z"
  }
}
```

**Example**:
```bash
curl -X POST http://localhost:8002/auth/pat-tokens \
  -H "Authorization: Bearer {session_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "API Token",
    "abilities": ["*"],
    "expires_at": "2026-12-31"
  }'
```

---

### List PAT Tokens

**Endpoint**: `GET /auth/pat-tokens`

**Description**: Retrieve all PAT tokens for authenticated user

**Headers**:
```
Authorization: Bearer {session_token}
```

**Response** (200):
```json
{
  "total": 1,
  "tokens": [
    {
      "id": 1,
      "name": "API Token",
      "abilities": ["*"],
      "last_used_at": null,
      "expires_at": "2026-12-31",
      "created_at": "2026-02-27T21:20:00.000000Z"
    }
  ]
}
```

**Example**:
```bash
curl -X GET http://localhost:8002/auth/pat-tokens \
  -H "Authorization: Bearer {session_token}"
```

---

## System Registration

### Register System

**Endpoint**: `POST /system-register`

**Description**: Register a system/device with PAT token

**Headers**:
```
Authorization: Bearer {pat_token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "system_name": "Production Server",
  "os_type": "Linux",
  "ip_address": "192.168.1.100",
  "org_id": 1,
  "tags": "prod, critical, backend",
  "metadata": "{\"cpu\": \"x86_64\", \"hostname\": \"prod-server\"}"
}
```

**Response** (201):
```json
{
  "message": "System registered successfully",
  "system": {
    "id": 1,
    "pat_token_id": 1,
    "user_id": 1,
    "system_name": "Production Server",
    "os_type": "Linux",
    "ip_address": "192.168.1.100",
    "org_id": 1,
    "tags": "prod, critical, backend",
    "metadata": "{\"cpu\": \"x86_64\", \"hostname\": \"prod-server\"}",
    "created_at": "2026-02-27T21:25:00.000000Z"
  }
}
```

**Example**:
```bash
curl -X POST http://localhost:8002/system-register \
  -H "Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN" \
  -H "Content-Type: application/json" \
  -d '{
    "system_name": "Production Server",
    "os_type": "Linux",
    "ip_address": "192.168.1.100",
    "org_id": 1,
    "tags": "prod, critical, backend",
    "metadata": "{\"cpu\": \"x86_64\", \"hostname\": \"prod-server\"}"
  }'
```

---

### List All Registered Systems

**Endpoint**: `GET /system-register`

**Description**: Get all systems registered by authenticated user

**Headers**:
```
Authorization: Bearer {pat_token}
```

**Response** (200):
```json
{
  "total": 2,
  "systems": [
    {
      "id": 2,
      "pat_token_id": 1,
      "user_id": 1,
      "system_name": "Server 2",
      "os_type": "Linux",
      "ip_address": "192.168.1.200",
      "tags": "master, ubuntu",
      "created_at": "2026-02-27T21:25:30.000000Z"
    },
    {
      "id": 1,
      "pat_token_id": 1,
      "user_id": 1,
      "system_name": "Server 1",
      "os_type": "Windows",
      "ip_address": "192.168.1.100",
      "tags": "prod, critical",
      "created_at": "2026-02-27T21:25:00.000000Z"
    }
  ]
}
```

**Example**:
```bash
curl -X GET http://localhost:8002/system-register \
  -H "Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN"
```

---

### Get Systems by PAT Token

**Endpoint**: `GET /system-register/pat/{patTokenId}`

**Description**: Get all systems registered with a specific PAT token

**Headers**:
```
Authorization: Bearer {pat_token}
```

**Response** (200):
```json
{
  "pat_token_id": 1,
  "pat_token_name": "API Token",
  "total_systems": 2,
  "systems": [
    {
      "id": 2,
      "pat_token_id": 1,
      "user_id": 1,
      "system_name": "Server 2",
      "os_type": "Linux",
      "ip_address": "192.168.1.200"
    },
    {
      "id": 1,
      "pat_token_id": 1,
      "user_id": 1,
      "system_name": "Server 1",
      "os_type": "Windows",
      "ip_address": "192.168.1.100"
    }
  ]
}
```

**Example**:
```bash
curl -X GET http://localhost:8002/system-register/pat/1 \
  -H "Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN"
```

---

### Get Systems by User

**Endpoint**: `GET /system-register/user/{userId}`

**Description**: Get all systems registered by a specific user (with breakdown by PAT token)

**Headers**:
```
Authorization: Bearer {pat_token}
```

**Response** (200):
```json
{
  "user_id": 1,
  "total_systems": 2,
  "systems_by_pat_token": [
    {
      "pat_token_id": 1,
      "pat_token_name": "API Token",
      "count": 2
    }
  ],
  "systems": [
    {
      "id": 2,
      "pat_token_id": 1,
      "user_id": 1,
      "system_name": "Server 2",
      "os_type": "Linux",
      "ip_address": "192.168.1.200"
    }
  ]
}
```

**Example**:
```bash
curl -X GET http://localhost:8002/system-register/user/1 \
  -H "Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN"
```

---

## Configuration File Management

### Upload Configuration File

**Endpoint**: `POST /config-files/upload`

**Description**: Upload a configuration file (text, .config, .conf, .cfg)

**Headers**:
```
Authorization: Bearer {pat_token}
Content-Type: multipart/form-data
```

**Request Body**:
- Form field: `file` (multipart file, max 10MB)

**Response** (201):
```json
{
  "message": "Configuration file uploaded successfully",
  "file": {
    "id": 1,
    "file_name": "app.config",
    "original_name": "app.config",
    "file_location": "config_files/1/uuid-app.config",
    "file_size": 512,
    "created_at": "2026-02-27T21:30:00.000000Z"
  }
}
```

**Example**:
```bash
curl -X POST http://localhost:8002/config-files/upload \
  -H "Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN" \
  -F "file=@app.config"
```

---

### List Configuration Files

**Endpoint**: `GET /config-files`

**Description**: List all active configuration files for authenticated user

**Headers**:
```
Authorization: Bearer {pat_token}
```

**Response** (200):
```json
{
  "total": 1,
  "files": [
    {
      "id": 1,
      "file_name": "app.config",
      "file_location": "config_files/1/uuid-app.config",
      "status": "active",
      "created_at": "2026-02-27T21:30:00.000000Z",
      "updated_at": "2026-02-27T21:30:00.000000Z"
    }
  ]
}
```

**Example**:
```bash
curl -X GET http://localhost:8002/config-files \
  -H "Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN"
```

---

### Download Configuration File

**Endpoint**: `GET /config-files/{fileId}`

**Description**: Download a configuration file from file system

**Headers**:
```
Authorization: Bearer {pat_token}
```

**Response** (200):
- Returns the file with appropriate Content-Type header
- File is downloaded with original filename

**Example**:
```bash
curl -X GET http://localhost:8002/config-files/1 \
  -H "Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN" \
  -o downloaded-app.config
```

---

### Get Configuration File Raw Data

**Endpoint**: `GET /config-files/{fileId}/raw-data`

**Description**: Get the raw file data stored in database for a configuration file

**Headers**:
```
Authorization: Bearer {pat_token}
```

**Response** (200):
```json
{
  "file_id": 1,
  "file_name": "app.config",
  "raw_data": {
    "id": 1,
    "file_data": "[database]\nhost=localhost\nport=3306\n...",
    "status": "active",
    "created_at": "2026-02-27T21:30:00.000000Z",
    "updated_at": "2026-02-27T21:30:00.000000Z"
  }
}
```

**Example**:
```bash
curl -X GET http://localhost:8002/config-files/1/raw-data \
  -H "Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN"
```

---

### Delete Configuration File

**Endpoint**: `DELETE /config-files/{fileId}`

**Description**: Soft delete a configuration file (marks as inactive, data preserved)

**Headers**:
```
Authorization: Bearer {pat_token}
```

**Response** (200):
```json
{
  "message": "Configuration file marked as inactive successfully",
  "file": {
    "id": 1,
    "file_name": "app.config",
    "status": "inactive",
    "updated_at": "2026-02-27T21:35:00.000000Z"
  }
}
```

**Behavior**:
- Marks both `configuration_files` and `raw_data` records as "inactive"
- Updates `updated_at` timestamp on both records
- File remains in file system and database
- File is no longer visible in list/read operations
- Returns 404 when trying to access deleted files

**Example**:
```bash
curl -X DELETE http://localhost:8002/config-files/1 \
  -H "Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN"
```

---

## File Operations

### Upload File (Generic)

**Endpoint**: `POST /files/upload`

**Description**: Upload a generic file with base64 or raw text data

**Headers**:
```
Authorization: Bearer {pat_token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "file_name": "data.txt",
  "file_data": "Base64 encoded content or raw text"
}
```

**Response** (201):
```json
{
  "message": "File uploaded successfully",
  "file": {
    "id": 1,
    "file_name": "data.txt",
    "file_location": "uploads/1/uuid_data.txt",
    "created_at": "2026-02-27T21:40:00.000000Z"
  }
}
```

**Example**:
```bash
curl -X POST http://localhost:8002/files/upload \
  -H "Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN" \
  -H "Content-Type: application/json" \
  -d '{
    "file_name": "data.txt",
    "file_data": "Sample file content"
  }'
```

---

### Download File (Generic)

**Endpoint**: `GET /files/{fileId}`

**Description**: Retrieve file data by ID

**Headers**:
```
Authorization: Bearer {pat_token}
```

**Response** (200):
```json
{
  "file": {
    "id": 1,
    "file_name": "data.txt",
    "file_location": "uploads/1/uuid_data.txt",
    "file_data": "Sample file content",
    "created_at": "2026-02-27T21:40:00.000000Z",
    "updated_at": "2026-02-27T21:40:00.000000Z"
  }
}
```

**Example**:
```bash
curl -X GET http://localhost:8002/files/1 \
  -H "Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN"
```

---

## Error Responses

### 400 Bad Request
```json
{
  "message": "Validation error",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "message": "Unauthorized to perform this action"
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 429 Too Many Requests
```json
{
  "message": "Rate limit exceeded",
  "retry_after": 60
}
```

### 500 Internal Server Error
```json
{
  "error": "Internal server error",
  "message": "Error message"
}
```

---

## Authentication Notes

### Session Token (Temporary)
- Used for user authentication (login)
- Obtained via `POST /auth/login`
- Used with session-based endpoints
- Example: `Authorization: Bearer {session_token}`

### PAT Token (Permanent)
- Personal Access Token created via `POST /auth/pat-tokens`
- Prefixed with `atgla-` (e.g., `atgla-xPyt2TeLn3TbbalkBMN`)
- Used for API operations (files, system registration, etc.)
- Can have expiration date
- Example: `Authorization: Bearer atgla-xPyt2TeLn3TbbalkBMN`

---

## Rate Limiting

- **Default Limit**: 4 requests per minute
- **Applied To**: User and Product endpoints
- **Exceeded**: Returns 429 status code
- **Reset**: After 1 minute

---

## Data Storage

### Configuration Files
- **File System**: `storage/app/config_files/{user_id}/`
- **Database**: `configuration_files` table (metadata)
- **Raw Data**: `raw_data` table (file content)
- **Default Status**: `active`
- **Soft Delete**: Marked as `inactive` instead of permanent deletion

---

## Changelog

**Version 1.0** - February 28, 2026
- Initial API release
- All endpoints documented and tested
- Soft delete functionality for configuration files
- PAT token support
- System registration
- Configuration file management
