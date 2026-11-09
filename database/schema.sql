-- GreenPay schema (SQLite).
CREATE TABLE users (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  username      TEXT NOT NULL UNIQUE,
  full_name     TEXT NOT NULL,
  role          TEXT NOT NULL CHECK (role IN ('customer', 'admin', 'service')),
  password_hash TEXT NOT NULL,
  api_key       TEXT,
  status        TEXT NOT NULL DEFAULT 'active',
  created_at    TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now'))
);

CREATE TABLE accounts (
  id       INTEGER PRIMARY KEY AUTOINCREMENT,
  number   TEXT NOT NULL UNIQUE,
  user_id  INTEGER NOT NULL REFERENCES users(id),
  label    TEXT NOT NULL,
  balance  REAL NOT NULL DEFAULT 0
);

CREATE TABLE transactions (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  from_account TEXT NOT NULL,
  to_account   TEXT NOT NULL,
  amount       REAL NOT NULL CHECK (amount > 0),
  memo         TEXT,
  created_by   INTEGER REFERENCES users(id),
  created_at   TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ', 'now'))
);
