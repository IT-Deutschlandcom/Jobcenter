-- Einmalig auf der bestehenden Datenbank ausführen. Vorher Backup erstellen.
-- Doppelte IDs/Benutzernamen müssen vor der Migration bereinigt werden.
ALTER TABLE users ENGINE=InnoDB;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE users
    MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ADD PRIMARY KEY (id),
    ADD UNIQUE KEY username_unique (username),
    ADD rank_id INT NOT NULL DEFAULT 1,
    ADD rank_step TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ADD total_seconds BIGINT UNSIGNED NOT NULL DEFAULT 0,
    ADD promotion_seconds BIGINT UNSIGNED NOT NULL DEFAULT 0,
    ADD active_since BIGINT DEFAULT NULL,
    ADD active_by BIGINT UNSIGNED DEFAULT NULL;

CREATE TABLE promotions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    from_rank INT NOT NULL,
    from_step TINYINT UNSIGNED NOT NULL,
    to_rank INT NOT NULL,
    to_step TINYINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at BIGINT NOT NULL,
    confirmed_by BIGINT UNSIGNED DEFAULT NULL,
    confirmed_at BIGINT DEFAULT NULL,
    INDEX pending (user_id, confirmed_at),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (confirmed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE duty_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    actor_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(20) NOT NULL,
    happened_at BIGINT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (actor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
