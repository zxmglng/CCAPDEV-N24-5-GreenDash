CREATE TABLE IF NOT EXISTS vendor_order_cancellations (
    order_id INT NOT NULL,
    vendor_id INT NOT NULL,
    reason ENUM('standard', 'out_of_stock', 'utility_outage', 'emergency_closure') NOT NULL,
    details VARCHAR(255) DEFAULT NULL,
    cancelled_at DATETIME NOT NULL,
    PRIMARY KEY (order_id),
    KEY vendor_order_cancellations_week (vendor_id, reason, cancelled_at),
    CONSTRAINT vendor_order_cancellations_order_fk FOREIGN KEY (order_id) REFERENCES orders (order_id),
    CONSTRAINT vendor_order_cancellations_vendor_fk FOREIGN KEY (vendor_id) REFERENCES users (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
