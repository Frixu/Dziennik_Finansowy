-- users
CREATE TABLE IF NOT EXISTS users (
  id            SERIAL PRIMARY KEY,
  email         VARCHAR(255) NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  created_at    TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

-- categories (per user)
CREATE TABLE IF NOT EXISTS categories (
  id         SERIAL PRIMARY KEY,
  user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  name       VARCHAR(50) NOT NULL,
  created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
  UNIQUE (user_id, name)
);

-- transactions
CREATE TABLE IF NOT EXISTS transactions (
  id          SERIAL PRIMARY KEY,
  user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  category_id INTEGER NOT NULL REFERENCES categories(id) ON DELETE RESTRICT,
  type        VARCHAR(10) NOT NULL CHECK (type IN ('income', 'expense')),
  amount      NUMERIC(12,2) NOT NULL CHECK (amount > 0),
  description VARCHAR(255),
  occurred_on DATE NOT NULL,
  created_at  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_transactions_user_date
  ON transactions(user_id, occurred_on);

CREATE INDEX IF NOT EXISTS idx_transactions_user_category
  ON transactions(user_id, category_id);
