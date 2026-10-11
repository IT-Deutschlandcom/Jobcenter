-- Einmalig nach migration_euro.sql auf bestehenden Installationen ausführen.
-- Vorher Backup erstellen. Bestehende Dienstzeit gilt zunächst als unbezahlt.
ALTER TABLE users ADD paid_seconds BIGINT UNSIGNED NOT NULL DEFAULT 0;

CREATE TABLE payouts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    actor_id BIGINT UNSIGNED NOT NULL,
    paid_seconds BIGINT UNSIGNED NOT NULL,
    taler BIGINT UNSIGNED NOT NULL,
    paid_at BIGINT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (actor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
