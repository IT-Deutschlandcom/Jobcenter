CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    avatar TEXT NOT NULL,
    signup_date BIGINT NOT NULL,
    rank_id INT NOT NULL DEFAULT 1,
    rank_step TINYINT UNSIGNED NOT NULL DEFAULT 0,
    total_seconds BIGINT UNSIGNED NOT NULL DEFAULT 0,
    promotion_seconds BIGINT UNSIGNED NOT NULL DEFAULT 0,
    paid_seconds BIGINT UNSIGNED NOT NULL DEFAULT 0,
    active_since BIGINT DEFAULT NULL,
    active_by BIGINT UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payroll_periods (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    rank_id INT NOT NULL,
    started_by BIGINT UNSIGNED NOT NULL,
    started_at BIGINT NOT NULL,
    ended_by BIGINT UNSIGNED DEFAULT NULL,
    ended_at BIGINT DEFAULT NULL,
    active_rank_id INT GENERATED ALWAYS AS (IF(ended_at IS NULL, rank_id, NULL)) STORED,
    UNIQUE KEY one_active_period_per_rank (active_rank_id),
    FOREIGN KEY (started_by) REFERENCES users(id),
    FOREIGN KEY (ended_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payroll_period_members (
    period_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    baseline_seconds BIGINT UNSIGNED NOT NULL,
    final_total_seconds BIGINT UNSIGNED DEFAULT NULL,
    paid_seconds BIGINT UNSIGNED NOT NULL DEFAULT 0,
    adjustment_seconds BIGINT NOT NULL DEFAULT 0,
    PRIMARY KEY (period_id, user_id),
    FOREIGN KEY (period_id) REFERENCES payroll_periods(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payroll_adjustments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    period_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    actor_id BIGINT UNSIGNED NOT NULL,
    adjustment_seconds INT NOT NULL,
    created_at BIGINT NOT NULL,
    FOREIGN KEY (period_id, user_id) REFERENCES payroll_period_members(period_id, user_id),
    FOREIGN KEY (actor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payouts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    period_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    actor_id BIGINT UNSIGNED NOT NULL,
    paid_seconds BIGINT UNSIGNED NOT NULL,
    taler DECIMAL(12,2) NOT NULL,
    paid_at BIGINT NOT NULL,
    FOREIGN KEY (period_id) REFERENCES payroll_periods(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (actor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
