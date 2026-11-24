CREATE TABLE IF NOT EXISTS user_carts (
    user_id INT NOT NULL,
    stall_id INT NOT NULL,
    items_json JSON NOT NULL,
    version BIGINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (user_id, stall_id),
    CONSTRAINT user_carts_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE,
    CONSTRAINT user_carts_stall_fk FOREIGN KEY (stall_id) REFERENCES stalls (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
