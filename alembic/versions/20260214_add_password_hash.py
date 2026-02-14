"""add password hash

Revision ID: 20260214_add_password_hash
Revises: 
Create Date: 2026-02-14 00:00:00.000000
"""

from alembic import op
import sqlalchemy as sa

# revision identifiers, used by Alembic.
revision = "20260214_add_password_hash"
down_revision = None
branch_labels = None
depends_on = None


def upgrade() -> None:
    op.add_column("users", sa.Column("password_hash", sa.String(length=255), nullable=False))


def downgrade() -> None:
    op.drop_column("users", "password_hash")
