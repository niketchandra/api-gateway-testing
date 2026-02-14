from fastapi import Depends
from fastapi import FastAPI
from fastapi import HTTPException
from fastapi import status
from sqlalchemy.orm import Session

from . import auth
from . import crud
from . import models
from . import schemas
from .db import Base
from .db import engine
from .deps import get_db

app = FastAPI(title="User CRUD API")


@app.on_event("startup")
def on_startup() -> None:
    # For local development convenience; use Alembic for production.
    Base.metadata.create_all(bind=engine)


@app.post("/users", response_model=schemas.UserOut, status_code=status.HTTP_201_CREATED)
def create_user(user_in: schemas.UserCreate, db: Session = Depends(get_db)) -> schemas.UserOut:
    existing = crud.get_user_by_email(db, user_in.email)
    if existing:
        raise HTTPException(status_code=409, detail="Email already exists")
    return crud.create_user(db, user_in)


@app.post("/auth/login", response_model=schemas.Token)
def login(payload: schemas.LoginRequest, db: Session = Depends(get_db)) -> schemas.Token:
    user = crud.get_user_by_email(db, payload.email)
    if not user or not auth.verify_password(payload.password, user.password_hash):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid credentials")

    token = auth.create_access_token(str(user.id))
    return schemas.Token(access_token=token)


@app.post("/auth/logout", status_code=status.HTTP_204_NO_CONTENT)
def logout(_: models.User = Depends(auth.get_current_user)) -> None:
    return None


@app.get("/users", response_model=list[schemas.UserOut])
def list_users(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)) -> list[schemas.UserOut]:
    return crud.get_users(db, skip=skip, limit=limit)


@app.get("/users/{user_id}", response_model=schemas.UserOut)
def get_user(user_id: int, db: Session = Depends(get_db)) -> schemas.UserOut:
    user = crud.get_user(db, user_id)
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    return user


@app.put("/users/{user_id}", response_model=schemas.UserOut)
def update_user(
    user_id: int,
    user_in: schemas.UserUpdate,
    db: Session = Depends(get_db),
) -> schemas.UserOut:
    user = crud.get_user(db, user_id)
    if not user:
        raise HTTPException(status_code=404, detail="User not found")

    if user_in.email is not None:
        existing = crud.get_user_by_email(db, user_in.email)
        if existing and existing.id != user_id:
            raise HTTPException(status_code=409, detail="Email already exists")

    return crud.update_user(db, user, user_in)


@app.delete("/users/{user_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_user(user_id: int, db: Session = Depends(get_db)) -> None:
    user = crud.get_user(db, user_id)
    if not user:
        raise HTTPException(status_code=404, detail="User not found")
    crud.delete_user(db, user)
    return None
