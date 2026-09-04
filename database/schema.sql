CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS servers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    owner_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    address VARCHAR(255) NOT NULL,
    minecraft_version VARCHAR(32) NOT NULL,
    loader VARCHAR(32) NOT NULL DEFAULT 'vanilla',
    systemd_unit VARCHAR(255) NOT NULL,
    daemon_url VARCHAR(255) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'unknown',
    memory_mb INT UNSIGNED NOT NULL,
    port SMALLINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY servers_systemd_unit_unique (systemd_unit),
    CONSTRAINT servers_owner_fk FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mods (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    server_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(32) NOT NULL DEFAULT 'curseforge',
    external_id VARCHAR(128) NOT NULL,
    name VARCHAR(255) NOT NULL,
    version VARCHAR(64) NOT NULL,
    file_url VARCHAR(2048) NOT NULL,
    installed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY mods_server_external_version_unique (server_id, external_id, version),
    CONSTRAINT mods_server_fk FOREIGN KEY (server_id) REFERENCES servers (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS server_metrics (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    server_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(32) NOT NULL,
    cpu_percent DECIMAL(5,2) NULL,
    memory_bytes BIGINT UNSIGNED NULL,
    recorded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY server_metrics_server_recorded_idx (server_id, recorded_at),
    CONSTRAINT server_metrics_server_fk FOREIGN KEY (server_id) REFERENCES servers (id) ON DELETE CASCADE
) ENGINE=InnoDB;
