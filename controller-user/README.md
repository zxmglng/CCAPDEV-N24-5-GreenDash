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

- `google-login-token.php` issues a pre-login CSRF token for the current browser session.
- `google-login.php` validates the pre-login token before verifying the Google ID token.
- `profile.php` checks the signed-in account and requires a CSRF token for profile edits, including an optional avatar upload.
- `select-role.php` checks the signed-in account and requires a CSRF token when assigning its first role.
- `logout.php` accepts only a POST with a valid customer CSRF token.
- `cart.php` loads and updates a signed-in customer's saved cart for a stall.
- `checkout.php` loads the signed-in customer's saved cart for the selected stall, creates the checkout CSRF token, and renders `app/views/user/checkout.php`.
- `submit-order.php` accepts checkout POST requests and returns JSON. It validates the saved cart, saves the order, and empties the purchased cart in one transaction.
- `orders.php` requires a signed-in active user and renders only that user's saved orders, items, options, and reviews.
- `reviews.php` provides public stall review lists on GET and owner-authorized review saves/deletes on POST.
- `cancel-order.php` handles customer cancellations of their own pending and preparing orders.

## Account CSRF protection

Customer profile edits, role selection, and logout submit a session-bound
`csrfToken` form field. PHP rejects invalid tokens with HTTP 403 before
making a change.

The customer and vendor sign-in callbacks both request a pre-login token
through `js/google-login-request.js`. They submit it alongside `credential`
in the JSON login request. Successful sign-in rotates the session ID and
clears the previous account's tokens. Deploy the PHP and JavaScript files
together so both callbacks can send the required token.

## Profile photo uploads

The multipart profile edit form includes an optional `profileImage` file field.
It accepts JPEG, PNG, WebP, and GIF up to 2 MiB, 2,048 px on each edge, and
4,000,000 total pixels. PHP validates and decodes the upload before assigning a
randomly named file in `assets/uploads/profiles/` to the signed-in account.
[Server upload setup](../README.md#6-allow-application-uploads)

## Saved carts

Apply the cart migration before using the PHP stall directory, menu, and checkout
pages. [Cart migration](../database/add_user_carts.sql)

The `user_carts` table stores one cart per account and stall. The cart icon opens
the current stall's cart on its menu page. From other customer pages, the icon
opens the only saved cart directly or lets the customer pick from multiple saved
carts. Customers can also choose **Resume cart** on a stall card.

`GET cart.php?stallId=<id>` returns the current cart's `version` and `items`.
A cart that has never been saved starts at version `0` with no items.
POST to the same URL with `csrfToken`, `version`, and `items` (a JSON array)
to save additions, edits, or removals. Each item carries `productId`, `quantity`,
`options` (a list of `groupId` and `optionId` pairs), and optional
`specialInstructions`. The PHP session supplies the user ID for both methods.
HTTP 409 signals that the cart changed and the browser should reload it.

## Checkout request and persistence

`checkout.php?stallId=<id>` loads the saved cart and current menu details. The
form submits `stallId`, `cartVersion`, `pickupTime`, `paymentMethod`,
`referenceNumber`, `csrfToken`, and `requestId`. The server checks the cart version,
uses the saved item IDs and quantities, and calculates prices from the current menu.

`app/core/checkout.php` coordinates validation and pricing. The model files contain
the SQL for the supplied `orders`, `order_items`, `order_item_options`, `products`,
`product_option_groups`, `product_option_choices`, `users`, `stalls`, and
`stall_operating_hours` tables. The option group/choice tables must exist and
contain choices for products that require customization. Use transactional tables
(InnoDB) so order inserts and the cart reset can roll back together.

Product IDs are saved as `order_items.menu_item_id`; database names/prices and
stall name/location are snapshotted. Category names remain NULL until the category
schema is integrated. Customer customization groups and available choices are
loaded from `product_option_groups` and `product_option_choices`. Each group is
single-choice; required groups default to their first available choice by
`display_order`, while optional groups default to no selection. Checkout verifies
that each selected choice belongs to the product, calculates its price from the
database, and saves its ID plus name and price snapshots in `order_item_options`.
Populate these tables for each product that needs customization. The current
vendor Manage Store customization editor loads vendor-owned database products
and reads/writes these rows. Other product edits in that page (such as product
name, base price, and categories) still use the existing demo editor.
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

## Customer order cancellations

Apply the migration before deploying the Orders page and cancellation controller.
The Orders page queries `user_order_cancellations` to calculate the customer's
weekly allowance. [Customer cancellation migration](../database/add_user_order_cancellations.sql)

The cancellation endpoint checks account status, order ownership, and the CSRF
token. Pending cancellations do not use the allowance. Customers can cancel up
to three preparing orders per Monday–Sunday week in Asia/Manila. The order
update and the cancellation record commit together. Any recorded payment
stays paid.

## Local verification

On MariaDB, add a customized item to a stall cart, sign out and back in, and
open the cart from Profile or Orders. Edit its quantity and reload the stall.
Save a cart at another stall and confirm the cart icon lets you choose either
cart. Place an order and reopen the stall to confirm its cart is empty. Use a
second account in the same browser to verify that the cart follows the account.

When deploying, retain the VM's connection credentials in `app/core/db.php`.
Merge its exception-based failure handling rather than replacing those settings;
the submit controller catches connection failures and returns a JSON error.
