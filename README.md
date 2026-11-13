# CCAPDEV-N24-5-GreenDash
## Changelog
All significant changes to this project will be logged in this section

#### 2026-09-30
**Added**
- Initial files (landing page index.html, styles and js directories with respective .css and .js files)

## Task Distribution -- Frontend
### Shared / Group Features
- [ ] Light/Dark Mode Toggle
  - [ ] Toggle between light and dark themes
  - [ ] Apply theme consistently across all pages

- [ ] Toast Notifications
  - [ ] Success messages
  - [ ] Error messages
  - [ ] Warning/informational messages

- [ ] Quick Search
  - [ ] Search stalls from the homepage
  - [ ] Display matching stalls/results

- [ ] Responsive Design
  - [ ] Desktop layout
  - [ ] Mobile layout

- [ ] Centralized Styling
  - [ ] Shared `global.css` for background, typography, headers, containers, buttons, cards, shadows, borders, etc.
  - [ ] Page/role-specific CSS may be added when necessary


## Zoe — User: Account & Stalls
### Pages
- User Sign Up
- User Home
- Stalls Directory
- Stall Details
- Profile/account page

### Features
- Login/Logout
- DLSU email validation (mock)
- Stall cards
- Open/closed status
- Operating hours
- Stall search/filter
- Navigation between stalls

## Chelsea — User: Ordering & Checkout
### Pages
- Ordering page
- Checkout page
- Order confirmation

### Features
- Food selection
- Quantity
- Cart/order summary
- Pickup now
- Scheduled pickup
- Cash/digital payment selection
- Stall QR code
- Validation
- Success/error messages
- Remove/update cart items
- Compute total price
- Prevent scheduled pickup before current time/outside open hours
- Order note (OPTIONAL)

## Rinoa — Orders + Vendor Order Management
### User side
- Orders page
- Ongoing orders
- Past orders

### Vendor side
- Order Management
- Active orders
- Sort by pickup time/status
- Pick-up screen
- Enter Order ID
- Verify order
- Mark order as collected
- Rating only for completed order
- Order status progression (pending, preparing, ready, etc)

### Backend implementation
- User orders and reviews use the database; reviews are allowed only for the customer's completed orders.
- Vendor controllers load orders only for stalls assigned through `stalls.vendor_id`.
- Active orders support search, stall/status filters, and pickup-time/status sorting.
- The pickup page verifies the customer's order ID and records collection after payment confirmation.
- PHP enforces `pending` → `preparing` → `ready` → `completed`; completed/cancelled orders appear in transaction history with actual customer ratings.
- See [vendor controller setup and routes](controller-vendor/README.md). No additional tables are required for this workflow.


## Raph — Vendor Store + Admin
### Vendor
- Vendor Sign Up
- Account Details
- Manage Store
- Request to Add New Store
- Edit menu/inventory
- Edit operating hours
- Open/closed toggle
- Vendor dashboard
- Add menu item toggle, not only edit menu

### Admin
- Admin Home
- Manage Stalls
- Stall Requests
- Approve/deny requests
- Admin can request details before approval/denial

---
## Task Distribution -- Backend
AS OF 2026-09-30: No backend needed yet

### Phase 2 database submission

The schema and sample-data export is [database/CCAPDEV-N24-5-GreenDash.sql](database/CCAPDEV-N24-5-GreenDash.sql). Its filename matches the required project submission name. This replaces `database/schema_and_data.sql`.

The script targets an **empty database**. It creates the tables without dropping existing ones, keeps foreign-key checks enabled, and inserts the sample records in dependency order within one transaction. It is a full submission dataset, not an incremental update for a database already in use.

Every entity contains at least five distinct sample records:

| Table | Sample rows |
| --- | ---: |
| `users` | 9 |
| `stalls` | 5 |
| `categories` | 10 |
| `products` | 10 |
| `product_option_groups` | 7 |
| `product_option_choices` | 20 |
| `orders` | 13 |
| `order_items` | 15 |
| `order_item_options` | 15 |
| `reviews` | 5 |
| `stall_operating_hours` | 35 |
| `store_requests` | 5 |

The samples cover all order statuses, cash and online payment records, immediate and scheduled pickup, multiple-item receipts, product-specific choices, unavailable items/choices, open and closed stalls, rating-only and written reviews, and pending/approved/denied store requests. Each review belongs to a different completed order. Sample schedules use `0 = Sunday` through `6 = Saturday`.

Original account records and order headers are retained. Sample stall ownership now points to active vendor accounts. The previously empty completed `TEST001` receipt has a historical item matching its original total, and a deleted catalogue item's reference is now `NULL` while its receipt snapshot remains intact. Historical receipts can retain prices or options that differ from the current menu.

Additional accounts with `example.invalid` email addresses are fictional data samples, not Google sign-in credentials. Existing account records remain available for the application's login flow. Pending and denied request proposals do not represent additional approved stalls; one pending removal request also illustrates the existing protection against removing a stall that still has products.

#### Adding samples to an existing database

Use [database/additional_sample_data.sql](database/additional_sample_data.sql) for a populated MariaDB database. The full schema-and-data file above is for an empty database and should not be sourced into existing tables.

The additional-data script preserves existing rows, user roles, menu settings, and IDs. It looks up active vendor/customer/admin accounts, adds two demo stalls and their menus, supplies customization groups/choices, and adds five completed reviewed orders, two active orders, and five store requests. New IDs are allocated by the database. A repeat run skips matching samples; an SQL error rolls back the batch. It requires all twelve project tables to use InnoDB and uses MariaDB's anonymous-block syntax, without creating stored procedures or changing table definitions.

The file must be accessible on the machine running the MariaDB client. From that client, select the intended database and use `SOURCE` with the file's absolute path. The script prints exact counts for every table at the end and rejects the batch if any entity still has fewer than five rows. The final submission export should reflect the updated database actually used for the demonstration.

## Phase 1 Implementation Notes (historical)
- Phase 1 focuses exclusively on frontend implementation.
- Authentication, database operations, payment processing, and backend APIs are simulated.
- Mock data may be implemented using JavaScript arrays, JSON files, or placeholder data.
- All pages must be accessible through the application's navigation/buttons.
- All pages must be responsive for desktop and mobile devices.
- External libraries and assets must be hosted locally, except for Chart.js if used.
