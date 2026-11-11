# User Controllers

Folder for php files that users visit in the browser. These files contain the logic for each browser page

No raw HTML or SQL belongs here.
Should contain:
1. `session_start()` annd security checks e.g., `if (!isset($_SESSION['admin_id']))`
2. loading of database and required model phps
3. handle `$_POST` request (CRUD)
4. fetch data via model phps
5. include a `require_once` of the matching view php (html only php found at projectfolder/app/view/..)

## Files

- `checkout.php` requires a signed-in user, creates the checkout CSRF token, and renders `app/views/user/checkout.php`.
- `submit-order.php` accepts checkout POST requests and returns JSON. It uses the user session for `user_id`, validates the cart, and saves through the models in one transaction.

## Checkout request and persistence

The form submits `stallId`, `orderItems` (a JSON array), `pickupTime`,
`paymentMethod`, `referenceNumber`, `csrfToken`, and `requestId`. Each item carries
`productId`, `quantity`, `milk`, `size`, and optional `specialInstructions`.
Client-supplied prices, totals, names, and user IDs are not used to save the order.

`app/core/checkout.php` coordinates validation and pricing. The model files contain
the SQL for the supplied `orders`, `order_items`, `order_item_options`, `products`,
`users`, `stalls`, and `stall_operating_hours` tables. No schema migration is needed
for the supplied order/product definitions. Use transactional tables (InnoDB) so
orders, items, and options can all roll back on failure.

Product IDs are saved as `order_items.menu_item_id`; database names/prices and
stall name/location are snapshotted. Category names remain NULL until the category
schema is integrated. The existing fixed milk/size choices are defined once in
`checkoutOptionGroups()` and used by the PHP menu and server pricing. Their
snapshot `option_id` is NULL because no option catalog schema was supplied.
Omitted choices use the same defaults as the UI. Payment references do not mark
an online payment as paid; new orders and payments both start pending.

Scheduled pickup uses today's date in Asia/Manila and must fit a 15-minute window
inside the stall's operating hours. The returned `orderNumber` and `totalAmount`
are displayed only after the transaction commits. The cart is kept on errors.
Repeating a request ID with the same payload returns the saved receipt within
that session (the latest 20 receipts are retained); changed payloads are rejected.

The PHP stall menu now loads product IDs/prices from the database. The existing
order-history UI and vendor order-management simulations are separate work and
have not been converted to database-backed lists in this checkout change.

## Local verification

Run `php tests/test_checkout_model.php`, `python3 tests/test_checkout_http.py`,
and `node tests/test_checkout_browser.js` from the repository root. The PHP tests
need PDO SQLite and mbstring. HTTP tests start a temporary PHP server and use an
isolated SQLite fixture mirroring the provided columns; they do not access the
VM database. MariaDB deployment testing is still required.

When deploying, retain the VM's connection credentials in `app/core/db.php`.
Merge its exception-based failure handling rather than replacing those settings;
the submit controller catches connection failures and returns a JSON error.
