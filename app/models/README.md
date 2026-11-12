# Models

This folder contains the database logic.

A model file is strictly for SQL and shouldn't contain HTML, `$_POST handling` and `$_SESSION checks`

Functions must accept `$pdo` as the first parameter, execute queries, and return results.

## Files

- `review-model.php`: list stall reviews and completed eligible orders, lock the owned order, and create/update/delete its review.
- `order-model.php`: save checkout records; load user/vendor orders, item snapshots, and options; cancel a user's pending order; and lock/update vendor-owned orders.
- `user-model.php`: retrieve account status for authenticated actions.
- `product-model.php`, `stall-model.php`, `stall-hours-model.php`: retrieve menu and stall data used by checkout; `stall-model.php` also lists stalls owned by a vendor.
- `category-model.php`: reserved for category integration.
