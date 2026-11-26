CREATE TABLE IF NOT EXISTS user_order_cancellations (
    cancellation_id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    cancelled_at DATETIME NOT NULL,
    PRIMARY KEY (cancellation_id),
    UNIQUE KEY user_order_cancellations_order (order_id),
    KEY user_order_cancellations_week (user_id, cancelled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
