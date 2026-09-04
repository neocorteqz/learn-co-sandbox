PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS servers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    owner_id INTEGER NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    address TEXT NOT NULL,
    minecraft_version TEXT NOT NULL,
    loader TEXT NOT NULL DEFAULT 'vanilla',
    systemd_unit TEXT NOT NULL UNIQUE,
    daemon_url TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'unknown',
    memory_mb INTEGER NOT NULL CHECK (memory_mb >= 0),
    port INTEGER NOT NULL CHECK (port BETWEEN 1 AND 65535),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS mods (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    server_id INTEGER NOT NULL REFERENCES servers (id) ON DELETE CASCADE,
    provider TEXT NOT NULL DEFAULT 'curseforge',
    external_id TEXT NOT NULL,
    name TEXT NOT NULL,
    version TEXT NOT NULL,
    file_url TEXT NOT NULL,
    installed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (server_id, external_id, version)
);

CREATE TABLE IF NOT EXISTS server_metrics (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    server_id INTEGER NOT NULL REFERENCES servers (id) ON DELETE CASCADE,
    status TEXT NOT NULL,
    cpu_percent NUMERIC,
    memory_bytes INTEGER,
    recorded_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS server_metrics_server_recorded_idx ON server_metrics (server_id, recorded_at);
