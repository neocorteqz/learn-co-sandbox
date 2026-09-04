CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS servers (
    id BIGSERIAL PRIMARY KEY,
    owner_id BIGINT NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    name VARCHAR(120) NOT NULL,
    address VARCHAR(255) NOT NULL,
    minecraft_version VARCHAR(32) NOT NULL,
    loader VARCHAR(32) NOT NULL DEFAULT 'vanilla',
    systemd_unit VARCHAR(255) NOT NULL UNIQUE,
    daemon_url VARCHAR(255) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'unknown',
    memory_mb INTEGER NOT NULL CHECK (memory_mb >= 0),
    port INTEGER NOT NULL CHECK (port BETWEEN 1 AND 65535),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS mods (
    id BIGSERIAL PRIMARY KEY,
    server_id BIGINT NOT NULL REFERENCES servers (id) ON DELETE CASCADE,
    provider VARCHAR(32) NOT NULL DEFAULT 'curseforge',
    external_id VARCHAR(128) NOT NULL,
    name VARCHAR(255) NOT NULL,
    version VARCHAR(64) NOT NULL,
    file_url VARCHAR(2048) NOT NULL,
    installed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (server_id, external_id, version)
);

CREATE TABLE IF NOT EXISTS server_metrics (
    id BIGSERIAL PRIMARY KEY,
    server_id BIGINT NOT NULL REFERENCES servers (id) ON DELETE CASCADE,
    status VARCHAR(32) NOT NULL,
    cpu_percent NUMERIC(5, 2),
    memory_bytes BIGINT,
    recorded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS server_metrics_server_recorded_idx ON server_metrics (server_id, recorded_at);
