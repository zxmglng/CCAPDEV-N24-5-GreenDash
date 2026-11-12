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
- `orders.php` requires a signed-in active user and renders only that user's saved orders, items, options, and reviews.
- `reviews.php` provides public stall review lists on GET and owner-authorized review saves/deletes on POST.
- `cancel-order.php` handles the existing Orders page cancellation action for the owner's pending orders.

## Checkout request and persistence

The form submits `stallId`, `orderItems` (a JSON array), `pickupTime`,
`paymentMethod`, `referenceNumber`, `csrfToken`, and `requestId`. Each item carries
`productId`, `quantity`, `options` (a list of `groupId` and `optionId` pairs),
and optional `specialInstructions`.
Client-supplied prices, totals, names, and user IDs are not used to save the order.

`app/core/checkout.php` coordinates validation and pricing. The model files contain
the SQL for the supplied `orders`, `order_items`, `order_item_options`, `products`,
`product_option_groups`, `product_option_choices`, `users`, `stalls`, and
`stall_operating_hours` tables. The option group/choice tables must exist and
contain choices for products that require customization. Use transactional
tables (InnoDB) so orders, items, and options can all roll back on failure.

Product IDs are saved as `order_items.menu_item_id`; database names/prices and
stall name/location are snapshotted. Category names remain NULL until the category
schema is integrated. Customer customization groups and available choices are
loaded from `product_option_groups` and `product_option_choices`. Each group is
single-choice; required groups default to their first available choice by
`display_order`, while optional groups default to no selection. Checkout verifies
that each selected choice belongs to the product, calculates its price from the
database, and saves its ID plus name and price snapshots in `order_item_options`.
Populate these tables for each product that needs customization. The current
vendor Manage Store page is still demo-only and does not edit these rows.
Payment references do not mark an online payment as paid; new orders and payments
both start pending.

Scheduled pickup uses today's date in Asia/Manila and must fit a 15-minute window
inside the stall's operating hours. The returned `orderNumber` and `totalAmount`
are displayed only after the transaction commits. The cart is kept on errors.
Repeating a request ID with the same payload returns the saved receipt within
that session (the latest 20 receipts are retained); changed payloads are rejected.

The PHP stall menu loads product IDs/prices from the database. The PHP order-history
page now loads saved orders and reviews through `controller-user/orders.php`.
Vendor order-management simulations remain separate work.

## Reviews

Create the previously provided `reviews` table before deploying these endpoints:
`review_id`, unique `order_id`, `rating` (1–5), nullable `comment` (500 characters),
`created_at`, and `updated_at`, with the foreign key to `orders.order_id`.

`app/models/review-model.php` contains the SQL; `app/core/reviews.php` handles
validation, eligibility, and presentation. Saves and deletes require an active
user, a valid review CSRF token, and an order owned by that user with status
`completed`. The parent order is locked during the transaction. Saving again
updates that order's existing review rather than creating another row.
Comments are optional; rating-only reviews are supported. Order and stall review
forms share the same rows and API, so editing or deleting on either page is
reflected on the other after refresh.

`GET reviews.php?stallId=<id>` returns public reviews and, for the current user,
their eligible completed orders and a CSRF token. Other reviewers' order IDs are
not returned. `POST reviews.php` accepts `action=save` or `action=delete`, `orderId`,
and `csrfToken`; saves additionally accept `rating` and optional `comment`.
User identity and stall association always come from the server-side order.

The Orders view loads `js/user-orders.js` and `js/reviews.js`; it no longer uses
the sample orders/ratings/comments in the HTML prototype's `js/user.js`.
Direct requests to `app/views/user/orders.php` redirect to its controller.
Cancellation uses the same user/CSRF checks and an atomic update restricted to
pending orders. A paid payment stays paid; cancelling does not claim a refund.

## Local verification

Run `php tests/test_checkout_model.php`, `python3 tests/test_checkout_http.py`,
and `node tests/test_checkout_browser.js` from the repository root. The PHP tests
need PDO SQLite and mbstring. HTTP tests start a temporary PHP server and use an
isolated SQLite fixture mirroring the provided columns; they do not access the
VM database. MariaDB deployment testing is still required.

When deploying, retain the VM's connection credentials in `app/core/db.php`.
Merge its exception-based failure handling rather than replacing those settings;
the submit controller catches connection failures and returns a JSON error.
