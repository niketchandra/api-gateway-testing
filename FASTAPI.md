# FastAPI module guide

This file explains the purpose of each module under src/app, how requests flow through the system, and how to add new APIs (example: products).

## High-level flow (request lifecycle)
1. A client sends an HTTP request to FastAPI.
2. FastAPI parses the path, query parameters, and body.
3. Pydantic schemas validate and coerce inputs into Python types.
4. FastAPI injects dependencies (like database sessions).
5. Route handlers call CRUD helpers to read or write data.
6. SQLAlchemy commits changes and returns ORM objects.
7. Pydantic response models serialize the output.

## Module breakdown (detailed)

## Module breakdown

### src/app/main.py
- Creates the FastAPI app instance.
- Wires HTTP routes for users and auth.
- Uses Depends(get_db) to inject a DB session into each route handler.
- Uses HTTPException to return consistent errors and status codes.
- On startup, creates tables for local development.

Routes:
- POST /users: create a user
- GET /users: list users
- GET /users/{user_id}: fetch a user
- PUT /users/{user_id}: update a user
- DELETE /users/{user_id}: delete a user
- POST /auth/login: return a JWT token
- POST /auth/logout: validate token and return 204

### src/app/auth.py
- Loads SECRET_KEY and token expiry settings from environment.
- Uses passlib to hash and verify passwords.
- Creates JWT access tokens and validates them for protected endpoints.
- Provides get_current_user dependency to validate tokens and load the user.

Environment variables:
- SECRET_KEY: required, used to sign tokens
- ACCESS_TOKEN_EXPIRE_MINUTES: optional, defaults to 60

### src/app/crud.py
- Encapsulates database access for the User model.
- get_user/get_users are read queries.
- create_user hashes the password before saving.
- update_user updates fields if provided and re-hashes password.
- delete_user removes the row.

### src/app/models.py
- SQLAlchemy ORM model for users.
- Fields: id, name, email, password_hash, created_at, updated_at.
- Mapped columns define table schema and constraints.

### src/app/schemas.py
- Pydantic models for request and response payloads.
- UserCreate includes password; UserOut excludes it.
- UserUpdate supports partial updates.
- LoginRequest and Token define auth request/response shapes.

### src/app/db.py
- Reads DATABASE_URL from environment.
- Creates SQLAlchemy engine, session factory, and Base.
- Engine options like pool_pre_ping improve stability.

### src/app/deps.py
- Defines get_db dependency that yields a session per request.
- Ensures the session is closed after the request completes.

## Adding a new API (example: products)
Use the same pattern as users: model -> schema -> CRUD -> routes -> migration -> Kong route.

### 1) Add a Product model
Create a new class in src/app/models.py:

```python
class Product(Base):
	__tablename__ = "products"

	id: Mapped[int] = mapped_column(Integer, primary_key=True, index=True)
	name: Mapped[str] = mapped_column(String(200), nullable=False)
	sku: Mapped[str] = mapped_column(String(100), unique=True, index=True, nullable=False)
	price_cents: Mapped[int] = mapped_column(Integer, nullable=False)
	created_at: Mapped[DateTime] = mapped_column(DateTime(timezone=True), server_default=func.now())
	updated_at: Mapped[DateTime] = mapped_column(
		DateTime(timezone=True),
		server_default=func.now(),
		onupdate=func.now(),
	)
```

### 2) Add Product schemas
Add models in src/app/schemas.py:

```python
class ProductBase(BaseModel):
	name: str = Field(..., min_length=1, max_length=200)
	sku: str = Field(..., min_length=1, max_length=100)
	price_cents: int = Field(..., ge=0)


class ProductCreate(ProductBase):
	pass


class ProductUpdate(BaseModel):
	name: Optional[str] = Field(None, min_length=1, max_length=200)
	sku: Optional[str] = Field(None, min_length=1, max_length=100)
	price_cents: Optional[int] = Field(None, ge=0)


class ProductOut(ProductBase):
	model_config = ConfigDict(from_attributes=True)

	id: int
	created_at: datetime
	updated_at: datetime
```

### 3) Add CRUD helpers
Add to src/app/crud.py:

```python
def get_product(db: Session, product_id: int) -> models.Product | None:
	return db.query(models.Product).filter(models.Product.id == product_id).first()


def get_product_by_sku(db: Session, sku: str) -> models.Product | None:
	return db.query(models.Product).filter(models.Product.sku == sku).first()


def get_products(db: Session, skip: int = 0, limit: int = 100) -> list[models.Product]:
	return db.query(models.Product).offset(skip).limit(limit).all()


def create_product(db: Session, product_in: schemas.ProductCreate) -> models.Product:
	product = models.Product(
		name=product_in.name,
		sku=product_in.sku,
		price_cents=product_in.price_cents,
	)
	db.add(product)
	db.commit()
	db.refresh(product)
	return product


def update_product(db: Session, product: models.Product, product_in: schemas.ProductUpdate) -> models.Product:
	if product_in.name is not None:
		product.name = product_in.name
	if product_in.sku is not None:
		product.sku = product_in.sku
	if product_in.price_cents is not None:
		product.price_cents = product_in.price_cents
	db.commit()
	db.refresh(product)
	return product


def delete_product(db: Session, product: models.Product) -> None:
	db.delete(product)
	db.commit()
```

### 4) Add routes
Add to src/app/main.py:

```python
@app.post("/products", response_model=schemas.ProductOut, status_code=status.HTTP_201_CREATED)
def create_product(product_in: schemas.ProductCreate, db: Session = Depends(get_db)) -> schemas.ProductOut:
	existing = crud.get_product_by_sku(db, product_in.sku)
	if existing:
		raise HTTPException(status_code=409, detail="SKU already exists")
	return crud.create_product(db, product_in)


@app.get("/products", response_model=list[schemas.ProductOut])
def list_products(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)) -> list[schemas.ProductOut]:
	return crud.get_products(db, skip=skip, limit=limit)


@app.get("/products/{product_id}", response_model=schemas.ProductOut)
def get_product(product_id: int, db: Session = Depends(get_db)) -> schemas.ProductOut:
	product = crud.get_product(db, product_id)
	if not product:
		raise HTTPException(status_code=404, detail="Product not found")
	return product


@app.put("/products/{product_id}", response_model=schemas.ProductOut)
def update_product(
	product_id: int,
	product_in: schemas.ProductUpdate,
	db: Session = Depends(get_db),
) -> schemas.ProductOut:
	product = crud.get_product(db, product_id)
	if not product:
		raise HTTPException(status_code=404, detail="Product not found")
	return crud.update_product(db, product, product_in)


@app.delete("/products/{product_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_product(product_id: int, db: Session = Depends(get_db)) -> None:
	product = crud.get_product(db, product_id)
	if not product:
		raise HTTPException(status_code=404, detail="Product not found")
	crud.delete_product(db, product)
	return None
```

### 5) Create and apply migrations
Generate a migration and apply it:

```bash
alembic revision --autogenerate -m "add products"
alembic upgrade head
```

### 6) Expose through Kong
Add product routes to kong/kong.yml or add a separate service and restart Kong:

```bash
docker compose -f docker-compose.yml -f docker-compose-kong.yml restart kong
```

## Adding a file upload and download API (example)
This example shows how to accept a file upload, store it locally, and retrieve it later.

### 1) Add a storage folder
Create a folder in the project root named storage. The API will save files under storage/uploads.

### 2) Add routes
Add the following to src/app/main.py:

```python
from pathlib import Path

from fastapi import File
from fastapi import UploadFile
from fastapi.responses import FileResponse
from fastapi.responses import JSONResponse
from uuid import uuid4

UPLOAD_DIR = Path("storage/uploads")
UPLOAD_DIR.mkdir(parents=True, exist_ok=True)


@app.post("/files/upload")
def upload_file(file: UploadFile = File(...)) -> dict:
	file_id = uuid4().hex
	safe_name = file.filename or "upload.bin"
	target = UPLOAD_DIR / f"{file_id}_{safe_name}"

	with target.open("wb") as out_file:
		out_file.write(file.file.read())

	return {"file_id": file_id, "filename": safe_name}


@app.get("/files/{file_id}")
def download_file(file_id: str):
	matches = list(UPLOAD_DIR.glob(f"{file_id}_*"))
	if not matches:
		return JSONResponse(status_code=404, content={"detail": "File not found"})
	return FileResponse(matches[0])
```

Notes:
- For large files, replace file.file.read() with a streaming copy to avoid memory usage spikes.
- This example stores files on the API container filesystem. For production, use object storage.

### 3) Add Kong routes
Add /files/upload and /files/{file_id} routes to kong/kong.yml, then restart Kong.

## Local run (without Docker)
```bash
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
setx DATABASE_URL "mysql+pymysql://root@localhost:3306/users_db"
setx SECRET_KEY "change-me"
uvicorn src.app.main:app --reload --host 0.0.0.0 --port 8000
```
