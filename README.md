# Laravel User CRUD with MySQL and Kong

This project provides a complete CRUD API for a User resource using Laravel and MySQL, exposed through Kong API Gateway.

## Repository Branches
- [FastAPI-with-Kong](https://github.com/niketchandra/api-gateway-testing/tree/FastAPI-with-Kong) - FastAPI implementation
- [Laravel-with-Kong](https://github.com/niketchandra/api-gateway-testing/tree/Laravel-with-Kong) - Laravel implementation
- [Redis-Integration](https://github.com/niketchandra/api-gateway-testing/tree/Redis-Integration) - Redis caching layer integration

## Features

### ✨ System Management & Validation
- **System Registration**: Register systems/devices with PAT token authentication  
- **Validation Hash Support**: Track system validation via optional `validation_hash` field  
- **System Deregistration**: Change system status from `active` to `inactive` (deregister)
- **Query by User/Token**: List registered systems filtered by user or PAT token
- **Status Tracking**: Monitor active/inactive system states

### ✨ Resilience & High Availability
This project implements production-ready resilience patterns:

- **Circuit Breaker**: Automatically detects database failures and prevents cascading errors
- **Message Queue**: Buffers write operations when database is unavailable
- **Automatic Retry**: Failed operations retry with exponential backoff (30s, 60s, 120s, 300s, 600s)
- **Graceful Degradation**: Returns meaningful responses even when services are down

**How it works:**
1. When database fails 3 times, circuit breaker opens
2. Write operations are queued in Redis
3. Queue worker processes jobs when database recovers
4. Read operations return 503 with circuit state information

See [IMPLEMENTATION.md](IMPLEMENTATION.md) for complete guide and testing instructions.

## Docs
- Main README: [README.md](README.md)
- **API Documentation**: [API.md](API.md) - Complete API endpoint reference (Auth, Users, Products, PAT Tokens, System Registration, System Deregistration, Configuration Files, File Operations)
- Implementation Guide (Circuit Breaker + Queue): [IMPLEMENTATION.md](IMPLEMENTATION.md)
- Laravel API details: [LARAVEL.md](LARAVEL.md)
- Kong config and routing: [KONG.md](KONG.md)
- Resilience patterns (Circuit Breakers & Queues): [resilience.md](resilience.md)
- Circuit breaker details: [CircuitBreak.md](CircuitBreak.md)
- Queue system details: [QUEUE.md](QUEUE.md)
- Scenario notes: [scenerio.md](scenerio.md)
- Redis branch: https://github.com/niketchandra/api-gateway-testing/tree/Redis-Integration
- Redis docs (branch): https://github.com/niketchandra/api-gateway-testing/blob/Redis-Integration/redis.md
- Composer app README: [composer/README.md](composer/README.md)
- Copilot instructions: [.github/copilot-instructions.md](.github/copilot-instructions.md)

## End-to-end workflow (Laravel + Kong + MySQL + Redis + Circuit Breaker)
High-level flow for a typical request:

1. Client calls Kong (proxy port 8002).
2. Kong routes the request to the Laravel API service.
3. Laravel checks the circuit breaker state.
4. If breaker is closed, Laravel runs the DB call.
5. If the DB call fails, the breaker records failures and may open.
6. If breaker is open:
   - Reads return 503 with `circuit_state`.
   - Writes are queued in Redis and return 202.
7. Queue worker retries writes with backoff until MySQL is back.
8. On recovery, queued jobs succeed and the breaker closes after successful calls.

Workflow diagram:

```
Client
  |
  v
Kong (8002) -> Laravel API (8000) -> Circuit Breaker
                                     |          |
                                     |          +-- open --> 503 (read) / 202 + Redis queue (write)
                                     |
                                     +-- closed --> MySQL
                                                       |
                                                       +-- success -> response
                                                       +-- failure -> breaker counts failure
```

```mermaid
flowchart LR
  A[Client] --> B[Kong :8002]
  B --> C[Laravel API :8000]
  C --> D{Circuit Breaker}
  D -->|open| E[503 for reads]
  D -->|open| F[202 + queue write]
  F --> G[Redis]
  G --> H[Queue Worker]
  H --> I[MySQL]
  D -->|closed| I[MySQL]
  I --> J[Response]
```

## Docker Compose (API + MySQL + Redis + Kong + Queue Worker)
1. Start everything:

```bash
docker compose -f docker-compose.yml -f docker-compose-kong.yml up -d
```

2. Build containers when code changes:

```bash
docker compose -f docker-compose.yml -f docker-compose-kong.yml up -d --build
```

3. Run migrations:

```bash
docker compose exec api php artisan migrate --force
```

4. Restart Kong after any kong/kong.yml change:

```bash
docker compose -f docker-compose.yml -f docker-compose-kong.yml restart kong
```

2. Services:
- **api**: Laravel application
- **mysql**: MySQL 8.0 database
- **redis**: Redis 7 for caching and queue
- **queue-worker**: Laravel queue worker for background jobs
- **kong**: Kong API Gateway 3.6
- **phpmyadmin**: Database admin interface

3. Endpoints:
- API (direct): http://localhost:8000
- Kong proxy: http://localhost:8002
- Kong admin: http://localhost:8001
- phpMyAdmin: http://localhost:8080 (user root, password empty)
- Redis: localhost:6379

The Laravel application lives in composer/ and is served by the api container.

## Kong API description
Kong runs in DB-less mode and loads kong/kong.yml at startup. The config defines:
- A users service with a /users route for all CRUD methods.
- An auth service with /auth/login and /auth/logout.
- A rate-limiting plugin on the users service.

Details: [KONG.md](KONG.md)

## CRUD commands (via Kong)
These examples use a default test user. If it does not exist, create it first.

Default test credentials:
- Email: user001@example.com
- Password: Secret123!

Create user:

```bash
curl -X POST http://localhost:8002/users \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"User001\",\"email\":\"user001@example.com\",\"password\":\"Secret123!\"}"
```

List users:

```bash
curl http://localhost:8002/users
```

Get user:

```bash
curl http://localhost:8002/users/1
```

Update user:

```bash
curl -X PUT http://localhost:8002/users/1 \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"User001 Updated\"}"
```

Delete user:

```bash
curl -X DELETE http://localhost:8002/users/1
```

## Products CRUD (via Kong)
Create product:

```bash
curl -X POST http://localhost:8002/products \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Widget\",\"sku\":\"WID-001\",\"price_cents\":1200}"
```

List products:

```bash
curl http://localhost:8002/products
```

Get product:

```bash
curl http://localhost:8002/products/1
```

Update product:

```bash
curl -X PUT http://localhost:8002/products/1 \
  -H "Content-Type: application/json" \
  -d "{\"price_cents\":1500}"
```

Delete product:

```bash
curl -X DELETE http://localhost:8002/products/1
```

## Login and logout (via Kong)
Register:

```bash
curl -X POST http://localhost:8002/auth/register \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"User001\",\"email\":\"user001@example.com\",\"password\":\"Secret123!\",\"password_confirmation\":\"Secret123!\"}"
```

Login (returns a temporary session token):

```bash
curl -X POST http://localhost:8002/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"user001@example.com\",\"password\":\"Secret123!\"}"
```

Retrieve session token from login response:

```bash
curl -s -X POST http://localhost:8002/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"user001@example.com\",\"password\":\"Secret123!\"}" | python -c "import sys, json; print(json.load(sys.stdin)['access_token'])"
```

Create PAT token (permanent, format atgla-xxxxxxxxxxxxxxxxxxx):

```bash
curl -X POST "http://localhost:8002/auth/pat-tokens?name=my_pat_1&expires_at=2099-12-31" \
  -H "Authorization: Bearer <session_token>"
```

View all PAT tokens for the current user:

```bash
curl -X GET http://localhost:8002/auth/pat-tokens \
  -H "Authorization: Bearer <session_token>"
```

Logout (invalidates the session token):

```bash
curl -X POST http://localhost:8002/auth/logout \
  -H "Authorization: Bearer <token>"
```

## File upload and download (via Kong)
Upload (requires PAT token):

```bash
curl -X POST http://localhost:8002/files/upload \
  -H "Authorization: Bearer <pat_token>" \
  -H "Content-Type: application/json" \
  -d "{\"file_name\":\"sample.txt\",\"file_data\":\"<raw-or-base64>\"}"
```

Download (requires PAT token, use file id from upload response):

```bash
curl -X GET http://localhost:8002/files/<file_id> \
  -H "Authorization: Bearer <pat_token>"
```

## System register (via Kong)
Register a system using a PAT token (CLI tool passes PAT only):

```bash
curl -X POST http://localhost:8002/system-register \
  -H "Authorization: Bearer <pat_token>" \
  -H "Content-Type: application/json" \
  -d "{\"system_name\":\"dev\",\"os_type\":\"Windows\",\"ip_address\":\"192.168.1.10\",\"org_id\":null,\"tags\":\"cli,dev\",\"metadata\":\"{\\\"cpu\\\":\\\"i7\\\"}\"}"
```

## Auth flow diagram (Session + PAT)

```mermaid
flowchart LR
  A[add_user] --> B[login]
  B --> C[Using Session Create PAT]
  C --> D[view all PAT]
  D --> E[Logout]
```

## Laravel migrations
Run migrations inside the api container:

```bash
docker compose exec api php artisan migrate --force
```

## Database tables (core)
- users: application users (name, email, password_hash, dob)
- personal_access_tokens: PAT tokens (user_id, token, abilities, expires_at, last_used_at)
- sessions: temporary session tokens for login (user_id, token, expires_at, last_used_at)
- system_register: registered systems (pat_token_id, user_id, org_id nullable, system_name, os_type, ip_address, tags, metadata)
- configuration_files: uploaded file metadata (user_id, file_name, file_location)
- raw_data: uploaded file contents (file_id, user_id, file_data)

## Troubleshooting
- Kong says "no Route matched": restart Kong after editing kong/kong.yml.
  - docker compose -f docker-compose.yml -f docker-compose-kong.yml restart kong
- 500 error for "Unknown column users.password_hash": run the migration.
- Docker network not found: bring stack down and up again.
