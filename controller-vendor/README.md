# Vendor order controllers

Browser entry points for Rinoa's vendor order workflow:

- `orders.php`: active orders for stalls assigned to the logged-in vendor; search, status/stall filters, and pickup/status sorting.
- `pickup.php`: exact customer order-number lookup, payment confirmation, and collection.
- `transaction-history.php`: completed/cancelled orders and actual customer reviews.
- `order.php` (GET): retrieve one owned order using `orderId` (database ID) or `orderNumber` (customer-facing ID).
- `update-order.php` (POST): CSRF-protected status changes with `orderId`, `expectedStatus`, `status`, and `csrfToken`. Collection additionally requires `paymentConfirmed=1`. Cancellation also submits `cancellationReason` and `cancellationDetails`.
- `logout.php`: GET returns a CSRF token for the existing profile logout button; POST ends the PHP session.
- `product-options.php`: GET loads products and their customization groups for an owned store; CSRF-protected POST replaces a product's single-choice groups and choices.

`_bootstrap.php` starts the session, checks the active database vendor account, and handles request errors. Controllers load database queries from `app/models/` and order workflow rules from `app/core/vendor-orders.php`, then render `app/views/vendor/`.

## Deployment and account setup

Use the existing Google login endpoint/dependency installation. Vendor login now establishes a PHP session in addition to keeping browser profile information. The account must have `users.user_role = 'vendor'` and `users.acc_status = 'active'`; assign its `user_id` to the relevant `stalls.vendor_id` through admin Manage Stalls. Unassigned stalls and other vendors' orders are never returned.

Apply the vendor order cancellation migration after creating or importing the `users`, `stalls`, and `orders` tables. Use the same migration on existing databases. Deploy the controllers, models, core helpers, views, JavaScript, and CSS together. Old vendor view URLs redirect to the controllers. [Vendor order cancellation migration](../database/add_vendor_order_cancellations.sql)

## Status and payment rules

- Normal progression: `pending` → `preparing` → `ready` → `completed`.
- Only ready orders can be collected. The vendor explicitly confirms that cash was received or online payment was verified. This records `payment_status = 'paid'`; it does not contact a payment gateway or generate a payment reference.
- A transaction locks the owned order and checks its expected status, preventing skipped/backward transitions and duplicate collection.
- Completed and cancelled orders automatically appear in history. Dates in history are labeled **Ordered** and use `created_at`.
- The existing user Orders page reads these same records. Reload it to see vendor updates; completed orders become eligible for the existing review endpoint.

## Vendor cancellations

Vendors can cancel `pending`, `preparing`, or `ready` orders with `payment_status = 'pending'` at their assigned stalls. Successful cancellation records the reason and any explanation and sets the payment status to `not_charged`. Open the cancelled order in **Transaction History** to see its reason and details.

Each vendor can make three successful standard cancellations across their stalls per Monday–Sunday week in `Asia/Manila`. The allowance renews on Monday. Exceptional cancellations are exempt from the weekly limit. The order update and cancellation record are saved in one transaction. Customer cancellations follow their own policy.

Administrators can review vendor cancellations and weekly allowance usage from the **Vendor Cancellations** page. [Admin controller documentation](../controller-admin/README.md#vendor-cancellations)

Use one of these `cancellationReason` values when posting `status=cancelled`. The `cancellationDetails` field accepts up to 255 characters.

| Reason | Explanation |
| --- | --- |
| `standard` | Optional |
| `out_of_stock` | Required |
| `utility_outage` | Required |
| `emergency_closure` | Required |
