# CCAPDEV-N24-5-GreenDash

GreenDash is a campus food ordering application for DLSU customers, food vendors, and administrators. Customers browse stalls, customize items, save carts, place pickup orders, and review completed orders. Vendors manage their menus and fulfill orders. Administrators manage users, stalls, and store requests and track vendor cancellations.

- **Repository:** [CCAPDEV-N24-5-GreenDash](https://github.com/zxmglng/CCAPDEV-N24-5-GreenDash)
- **Phase 2 branch:** `backend`
- **Security phase and PHP deployment branch:** `security`
- **Stack:** Apache, PHP, MariaDB, JavaScript/jQuery, Bootstrap, and Google Identity Services.
- **Homepage:** the root `index.php`, served at `/GreenDash/` on the demonstration server.

Deploy the PHP application from `security`. Keep the Phase 1 GitHub Pages deployment and the earlier HTML prototypes in `pages/` available for comparison.

## MVC structure

| Location | Responsibility |
| --- | --- |
| `index.php` | Landing page with customer, vendor, and admin sign-in links. |
| `controller-user/` | Customer requests, Google login, role selection, carts, checkout, orders, profiles, and reviews. |
| `controller-vendor/` | Vendor requests, store/menu management, uploads, order progression, pickup, and history. |
| `controller-admin/` | Admin login, dashboard, user/stall management, request decisions, and vendor cancellation tracking. |
| `app/models/` | SQL queries and database persistence for accounts, stalls, menus, carts, orders, reviews, requests, and vendor cancellations. |
| `app/views/` | PHP templates and shared navigation that render the page data. |
| `app/core/` | PDO connection, session/authorization helpers, and validation/workflow rules. |
| `js/`, `style/`, `assets/`, `bootstrap/`, `bootstrap-icons/` | Browser behavior, presentation, and static assets. |
| `database/` | Schema snapshots, migrations, and sample-data scripts. |
| `pages/` | Phase 1 HTML prototypes. |

A request normally reaches a controller, which checks the request/session, calls core helpers and models, and renders a view or returns JSON. At checkout, `controller-user/submit-order.php` calls `app/core/checkout.php` to validate and price the saved cart. The order, items, options, and cart reset are committed together before the browser displays the receipt.

Use the controller routes for application features. The current vendor sign-in page is a public template at `app/views/vendor/login.php`, linked from the homepage; protected vendor pages use their controllers.

### Escape values when rendering HTML

Use `htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')` for dynamic text and quoted HTML attributes in PHP templates. Keep the original values in the database so each output can encode them for its own context.

```php
<h2><?= htmlspecialchars($stall['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
```

Cast numeric IDs to integers. Validate dynamic URL destinations before escaping them as HTML attributes. Use `json_encode()` for API responses and the `JSON_HEX_*` flags when embedding JSON in an HTML script element. Use `textContent` to display API text in the browser.

## Set up and run on the CCS Cloud server

These instructions use the existing project layout: a source checkout at `~/GreenDash-src` and an Apache site at `/var/www/html/GreenDash`. Run Bash commands in the Linux terminal and SQL commands inside MariaDB. Replace example database credentials with the account configured on your server. Use the server address and port assigned to your group when opening the site.

### 1. Check the required software

The application and its locked Composer dependencies need PHP 8.1 or later. The server also needs MariaDB with InnoDB, Apache with PHP enabled, Git, rsync, and Composer. PHP needs PDO MySQL, mbstring, fileinfo, GD, and sessions. The incremental sample script requires MariaDB 10.1 or later.

On an Ubuntu server where these components are not installed, an administrator can install them with:

```bash
sudo apt update
sudo apt install apache2 mariadb-server mariadb-client libapache2-mod-php php php-cli php-mysql php-mbstring php-gd php-curl php-xml php-zip git rsync unzip composer
sudo systemctl enable --now apache2 mariadb
```

Check the installation:

```bash
php -v
php -m
php -r 'print_r(gd_info());'
mariadb --version
composer --version
sudo systemctl status apache2 mariadb --no-pager
```

Check that GD supports JPEG, PNG, WebP, and GIF and that Apache’s PHP runtime loads both GD and fileinfo.

Keep the existing Apache site and assigned listening port. The project uses actual `.php` files as routes, so it does not require URL rewriting or a project `.htaccess` file. Apache must serve `index.php` as a directory index and execute PHP.

### 2. Retrieve the security branch

For a new checkout:

```bash
git clone --branch security --single-branch https://github.com/zxmglng/CCAPDEV-N24-5-GreenDash.git ~/GreenDash-src
```

If the checkout already exists, use the update commands below instead of cloning again.

### 3. Copy the application into the site directory

```bash
sudo mkdir -p /var/www/html/GreenDash
sudo rsync -rltv \
  --exclude='/.git/' \
  --exclude='/pages/' \
  --exclude='/tests/' \
  --exclude='/database/' \
  --exclude='/vendor/' \
  --exclude='/app/core/db.php' \
  --exclude='/assets/uploads/' \
  --exclude='/assets/stalls/pending/' \
  --exclude='.DS_Store' \
  ~/GreenDash-src/ /var/www/html/GreenDash/
```

The trailing slashes copy the checkout's contents into `GreenDash`. The top-level `vendor/` exclusion preserves installed dependencies; it does not exclude `app/views/vendor/`. Database scripts are imported from the source checkout. Existing connection settings and uploaded files are preserved, and this command does not delete destination files.

On the **first installation only**, initialize the connection file:

```bash
sudo cp ~/GreenDash-src/app/core/db.php /var/www/html/GreenDash/app/core/db.php
```

Edit `/var/www/html/GreenDash/app/core/db.php` so these variables match the database account you will use:

```php
$host = 'localhost';
$dbname = 'greenDash';
$username = 'greendash_app';
$password = 'REPLACE_WITH_DATABASE_PASSWORD';
```

The existing file creates `$pdo` and sets exception handling. Update its connection values while retaining that behavior. Database names are case-sensitive on this deployment; use `greenDash` consistently. This connection file currently uses PHP variables, not a `.env` loader.

### 4. Prepare the database

**Fresh database:** open the MariaDB client using an administrator account:

```bash
sudo mariadb
```

Run the following SQL only when setting up a new database/account. Replace the example password with the same value configured in `db.php`:

```sql
CREATE DATABASE greenDash CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'greendash_app'@'localhost' IDENTIFIED BY 'REPLACE_WITH_DATABASE_PASSWORD';
GRANT ALL PRIVILEGES ON greenDash.* TO 'greendash_app'@'localhost';
EXIT;
```

Then import the full schema and sample data from the **Linux terminal**:

```bash
mariadb -u greendash_app -p greenDash < ~/GreenDash-src/database/CCAPDEV-N24-5-GreenDash.sql
```

**Existing populated database:** keep its current tables and account settings. To add the demonstration samples, run this in the **Linux terminal**, replacing `YOUR_DATABASE_USER` with the configured database username:

```bash
mariadb -u YOUR_DATABASE_USER -p greenDash < ~/GreenDash-src/database/additional_sample_data.sql
```

The incremental script requires existing active customer, vendor, and admin rows. It preserves existing rows and prints counts for the twelve sample tables. More details and expected snapshot counts appear below.

For either setup, apply the cart migration after the `users` and `stalls` tables exist. Use a database account with permission to create tables. [Cart migration](database/add_user_carts.sql)

```bash
mariadb -u YOUR_DATABASE_USER -p greenDash < ~/GreenDash-src/database/add_user_carts.sql
```

The app creates cart rows as customers shop. A fresh cart table starts empty.

Apply the customer order cancellation migration to support the Orders page and
record cancellations of preparing orders. It creates the tracking table when
missing and preserves existing cancellation records. [Customer cancellation migration](database/add_user_order_cancellations.sql)

```bash
mariadb -u YOUR_DATABASE_USER -p greenDash < ~/GreenDash-src/database/add_user_order_cancellations.sql
```

Apply the vendor order cancellation migration after the `users`, `stalls`, and `orders` tables exist. The vendor workflow and the admin Vendor Cancellations report use the same records to track reasons and each vendor's weekly allowance. [Vendor order cancellation migration](database/add_vendor_order_cancellations.sql)

```bash
mariadb -u YOUR_DATABASE_USER -p greenDash < ~/GreenDash-src/database/add_vendor_order_cancellations.sql
```

### 5. Install and configure Google sign-in

`controller-user/google-login.php` loads the Google PHP client through `vendor/autoload.php`. Install dependencies from the tracked `composer.lock` using an account with write access to the site directory.

```bash
cd /var/www/html/GreenDash
composer install --no-dev --prefer-dist
php -r 'require "vendor/autoload.php"; echo class_exists("Google\\Client") ? "Google client ready\n" : "Google client missing\n";'
```

Use one Google OAuth web client ID consistently in:

- `controller-user/google-login.php` (`$googleClientId`);
- `js/user.js` (`client_id`);
- `js/vendor-auth.js` (`client_id`).

The OAuth project's owner must register the site's origin, including its scheme, hostname, and non-default port, in **Authorized JavaScript origins**. The application uses a JavaScript callback and posts the credential to PHP. Follow Google's [sign-in setup and origin requirements](https://developers.google.com/identity/gsi/web/guides/get-google-api-clientid) for the hosting origin.

Customer and vendor sign-in require an active DLSU Google account. PHP verifies the token and `dlsu.edu.ph` hosted domain. Sample accounts using `example.invalid` addresses cannot sign in through Google. The browser and PHP server need internet access for Google sign-in; some views also load external icon/chart resources.

### 6. Allow application uploads

On the Ubuntu Apache configuration using `www-data`, create the writable directories:

```bash
sudo install -d -o www-data -g www-data -m 0775 \
  /var/www/html/GreenDash/assets/stalls \
  /var/www/html/GreenDash/assets/stalls/pending \
  /var/www/html/GreenDash/assets/uploads \
  /var/www/html/GreenDash/assets/uploads/products \
  /var/www/html/GreenDash/assets/uploads/profiles
```

The request approval flow moves an uploaded logo from `assets/stalls/pending/` into `assets/stalls/`, so both directories must be writable by PHP. Product images are stored by stall under `assets/uploads/products/`. Customer avatars are stored in `assets/uploads/profiles/`. Uploads use the existing image path columns.

PHP checks the file type with fileinfo, verifies size and dimensions, and decodes the image with GD before saving its original bytes under a random filename.

| Upload | Formats | File size | Max width and height | Max total pixels |
| --- | --- | ---: | ---: | ---: |
| Vendor product image | JPEG, PNG, WebP, GIF | 5 MiB | 4,096 px each | 8,000,000 |
| Vendor store logo | PNG | 2 MiB | 4,096 px each | 8,000,000 |
| Customer avatar | JPEG, PNG, WebP, GIF | 2 MiB | 2,048 px each | 4,000,000 |

In Apache's PHP configuration, enable file uploads and set `upload_max_filesize = 5M` or higher. Set `post_max_size = 8M` or higher so the body can include both the file and the other form fields. Restart Apache after changing its PHP configuration.

```bash
sudo systemctl restart apache2
```

### 7. Open the homepage and prepare accounts

Open your assigned server origin followed by `/GreenDash/`. For example, the path is `/GreenDash/index.php`, with the actual host and port supplied by the group. The `php/` directory used in earlier versions is no longer the deployment entry point.

| Role | Sign-in and setup |
| --- | --- |
| Customer | From the homepage, select **Continue with Google**. A new account chooses Student, Faculty, or Staff before entering the directory. Existing accounts use their database role. |
| Vendor | From the homepage, select **Sign in as a food vendor**. An administrator must set the account's role to `vendor`, keep it active, and assign its `user_id` to the relevant stall's `vendor_id`. Sign in again after changing the role. |
| Admin | From the homepage, select **Sign in as an admin**. The current default demo key is `greendashN24`; `GREENDASH_ADMIN_KEY` in the PHP process environment can override it. |

Use admin **Users → Manage** to set up the vendor account and **Stalls → Manage** to assign its stalls. Admin request decisions also need an active `admin` row in `users`; the helper uses that row or the configured `GREENDASH_ADMIN_USER_ID`. The admin key establishes the admin session independently of Google sign-in.

PHP sessions are already used for customer/vendor identity and admin access. The Phase 2 specification does not require password hashing; Google sign-in does not use a locally entered password.

## Deploy later changes

Check the source checkout on the server before deploying pushed changes.

```bash
git -C ~/GreenDash-src status --short
git -C ~/GreenDash-src branch --show-current
```

If this checkout tracks only the old `backend` branch, add `security` to the branches it fetches.

```bash
git -C ~/GreenDash-src remote set-branches --add origin security
```

If the checkout is on another branch, run `git -C ~/GreenDash-src fetch origin security`. Then run `git -C ~/GreenDash-src switch security` if the local branch already exists, or `git -C ~/GreenDash-src switch --create security --track origin/security` to create it.

```bash
git -C ~/GreenDash-src pull --ff-only origin security
```

Resolve any local changes or divergence before continuing if Git refuses the pull. After a successful pull, apply any pending migrations in setup step 4 and repeat the **rsync command in setup step 3** to copy all changed files to the live site. Hard-refresh the browser after deploying changed JavaScript or CSS.

The rsync command preserves the live connection file and installed dependencies. If `app/core/db.php` changes in the repository, merge its code changes into the site's copy while retaining the configured connection values. Rerun `composer install --no-dev --prefer-dist` in the site directory when the Composer dependencies change.

## Navigation and demonstration

Begin at the homepage and use links and buttons for every feature. The route paths below identify the implementation for reviewers; the demonstration should follow the navigation rather than entering feature URLs manually.

| Workflow | Navigation from the homepage | Main controller routes |
| --- | --- | --- |
| Browse and order | Customer Google sign-in → Stalls Directory → stall card → menu/cart → checkout | `controller-user/stalls-directory.php`, `stall-info.php`, `cart.php`, `checkout.php`, `submit-order.php` |
| Customer orders and reviews | Customer navigation → Orders → ongoing/past order → available cancellation/review actions; stall pages also show reviews | `controller-user/orders.php`, `cancel-order.php`, `reviews.php` |
| Customer profile | Customer navigation → Profile → edit account details | `controller-user/profile.php` |
| Vendor menus | Vendor sign-in → Profile → Manage store → assigned stall → menu/category/customization controls | `controller-vendor/profile.php`, `manage-menu.php`, `manage-store.php`, `menu.php`, `product-options.php`, `upload-image.php` |
| Request a stall | Vendor Profile sidebar → Request to add a new store → submit | `controller-vendor/request-store.php` |
| Fulfill and collect | Vendor navigation → Order Requests → progress order → Pickup → verify order number/payment → collect → Transaction History | `controller-vendor/orders.php`, `order.php`, `update-order.php`, `pickup.php`, `transaction-history.php` |
| Cancel an unpaid vendor order | Vendor navigation → Order Requests → order details → Cancel order → choose reason and confirm | `controller-vendor/orders.php`, `order.php`, `update-order.php` |
| Admin management | Admin sign-in → Dashboard; sidebar → Stalls → Manage/Requests or Users → Manage | `controller-admin/home.php`, `manage-stores.php`, `store-requests.php`, `manage-users.php` |
| Track vendor cancellations | Admin sign-in → Vendor Cancellations → review weekly usage or filter cancellation records | `controller-admin/vendor-cancellations.php` |

Page/data reads use GET. Google sign-in posts JSON to PHP. Cart saves, checkout, profile edits, role selection, reviews, menu edits, request decisions, and order status changes use POST. Directory filtering also runs in the browser without changing database data.

Each signed-in customer has a saved cart per stall. After signing back in, choose **Resume cart** on the stall card to reopen its items. Successfully placing an order empties that stall's cart.

For the demonstration, create a new order with a valid pickup time, progress it through `pending` → `preparing` → `ready` → `completed`, and leave a review from the purchasing account. Refresh customer and vendor pages to show the saved changes. Vendors see only their assigned stalls' orders, and customers can review only their own completed orders.

Cash/online choices and payment references are recorded in the database. The vendor confirms receipt of payment at collection; this application does not charge a payment gateway. Ensure the selected stall is open and has available products, valid operating hours, and available choices for required customization groups. Scheduling uses today's date in `Asia/Manila`.

## Verify the deployment

- Open the root homepage and complete customer, vendor, and admin navigation using buttons. Check that pages, images, styles, and scripts load.
- Confirm the database import and migrations completed without SQL errors. The incremental sample script prints record counts for the tables listed below.
- Save an item with a customization, reload the stall, and confirm it persists after signing out and back in. Sign into a second account in the same browser and confirm it sees only its own cart.
- Place an order, reload the customer's Orders page, and verify its items, options, and total. Confirm the assigned vendor can find the same order.
- Complete collection with payment confirmation, then create, edit, and delete a review. Refresh the stall review list and transaction history to verify persistence.
- With a vendor account that has a fresh weekly allowance, cancel three unpaid orders using the standard reason. Confirm a fourth standard cancellation is blocked and an exception with an explanation goes through. Reopen the cancelled order in **Transaction History** to check its reason and explanation.
- As an admin, open **Vendor Cancellations** and confirm that vendor's weekly usage shows **3 / 3** standard cancellations and **1** exception. Find the recorded cancellation by filtering history by vendor, reason, and period (**Current week** or **All time**).
- Upload a product image, save a menu change, and reload the stall page. Submit a store request with a PNG logo and check its appearance in the admin request list before deciding on it.
- Change a customer avatar from Profile and reload the page. Confirm a corrupt image, a renamed non-image, and an image above the dimension limit are rejected.
- Check invalid input and access from an account without the required role/ownership; the server should reject unauthorized changes.

If a page fails, inspect that request's status and response in browser Developer Tools. On the server, inspect the Apache/PHP error log (normally `/var/log/apache2/error.log` on Ubuntu). A missing Google autoloader needs step 5; database connection errors need matching step 3 credentials and a running MariaDB server; upload errors need step 6 permissions and limits. A 404 requires checking the deployed file's path and exact filename case.

## Database scripts and sample data

The populated schema and sample-data export is [database/CCAPDEV-N24-5-GreenDash.sql](database/CCAPDEV-N24-5-GreenDash.sql). Its filename matches the required project submission name. The original [database/schema_and_data.sql](database/schema_and_data.sql) is also retained for reference.

The script targets an **empty database**. It creates the tables without dropping existing ones, keeps foreign-key checks enabled, and inserts the sample records in dependency order within one transaction. It is a full submission dataset, not an incremental update for a database already in use.

The full snapshot contains the following sample counts. Counts in an existing database can differ after incremental additions or application use. Each table listed here must still have at least five distinct records.

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

### Adding samples to an existing database

Use [database/additional_sample_data.sql](database/additional_sample_data.sql) for a populated MariaDB database. The full schema-and-data file above is for an empty database and should not be sourced into existing tables.

The additional-data script preserves existing rows, user roles, menu settings, and IDs. It looks up active vendor/customer/admin accounts, adds two demo stalls and their menus, supplies customization groups/choices, and adds five completed reviewed orders, two active orders, and five store requests. New IDs are allocated by the database. A repeat run skips matching samples; an SQL error rolls back the batch. It requires all twelve project tables to use InnoDB and uses MariaDB's anonymous-block syntax, without creating stored procedures or changing table definitions.

The file must be accessible on the machine running the MariaDB client. From that client, select the intended database and use `SOURCE` with the file's absolute path. The script prints exact counts for every table at the end and rejects the batch if any entity still has fewer than five rows. The final submission export should reflect the updated database actually used for the demonstration.

To check all table counts again, run the following inside MariaDB after `USE greenDash;`. Every `sample_rows` value should be at least 5:

```sql
SELECT 'users' AS table_name, COUNT(*) AS sample_rows FROM users
UNION ALL SELECT 'stalls', COUNT(*) FROM stalls
UNION ALL SELECT 'categories', COUNT(*) FROM categories
UNION ALL SELECT 'products', COUNT(*) FROM products
UNION ALL SELECT 'product_option_groups', COUNT(*) FROM product_option_groups
UNION ALL SELECT 'product_option_choices', COUNT(*) FROM product_option_choices
UNION ALL SELECT 'orders', COUNT(*) FROM orders
UNION ALL SELECT 'order_items', COUNT(*) FROM order_items
UNION ALL SELECT 'order_item_options', COUNT(*) FROM order_item_options
UNION ALL SELECT 'reviews', COUNT(*) FROM reviews
UNION ALL SELECT 'stall_operating_hours', COUNT(*) FROM stall_operating_hours
UNION ALL SELECT 'store_requests', COUNT(*) FROM store_requests
ORDER BY table_name;
```

### Export the final demonstration database

The supplied full snapshot predates some live database changes, such as the optional `stalls.qr_code_path` field. Before submission, regenerate the full SQL file from the database actually used for the demonstration so its schema and sample data match the application and final ERD. Run this in the **Linux terminal** after the data checks, replacing `YOUR_DATABASE_USER`:

```bash
mariadb-dump -u YOUR_DATABASE_USER -p --single-transaction --default-character-set=utf8mb4 greenDash > ~/GreenDash-src/database/CCAPDEV-N24-5-GreenDash.sql
```

This replaces the local submission snapshot with the full schema and data from that database. Commit and push the updated SQL file before downloading the final project ZIP. The incremental script and original schema dump serve different purposes and do not replace this final export.

## Phase 2 submission checklist

Submit these four items in the order required by the specification:

1. **Repository link:** [CCAPDEV-N24-5-GreenDash](https://github.com/zxmglng/CCAPDEV-N24-5-GreenDash), the same repository used for Phase 1. Identify `backend` as the Phase 2 branch. Verify at least two commits per week from different members; keep the Phase 1 GitHub Pages deployment available for comparison.
2. **Final ERD image:** `CCAPDEV-N24-5-GreenDash.png`, matching the actual demonstration database. This file still needs to be added; no ERD image with the required filename is present in this checkout.
3. **Full database script with sample data:** `CCAPDEV-N24-5-GreenDash.sql`, maintained in `database/`. Export the final demonstration database, verify at least five distinct samples per entity, and submit that file.
4. **Project ZIP:** `CCAPDEV-N24-5-GreenDash.zip`, containing the final backend project and this root README. Download the latest `backend` branch from GitHub using **Code → Download ZIP**, then rename the download to the required filename.

In the submission form, enumerate the items above: insert the repository URL as an external link, upload the ERD as an image, and upload the SQL and ZIP as documents. Each member should also retain their AI-assistance conversations for the separate individual submission described in the specification.

## Team responsibilities

The original frontend task allocation is retained below as historical planning notes.

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
- PHP enforces `pending` → `preparing` → `ready` → `completed`. Completed and cancelled orders appear in transaction history with actual customer ratings where available.
- Vendors can cancel unpaid active orders. Each vendor has three standard cancellations per Manila calendar week across their stalls, with exceptions for stock shortages, utility outages, and emergency closures. [Vendor controller setup and routes](controller-vendor/README.md)


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

## Phase 1 implementation notes (historical)

- Phase 1 focused exclusively on frontend implementation; authentication, database operations, payment processing, and backend APIs were simulated.
- Mock data could use JavaScript arrays, JSON files, or placeholder data.
- Pages were required to be responsive and accessible through application navigation/buttons.
- The original frontend specification required local external-library/assets hosting, except Chart.js. Current Phase 2 Google sign-in requires Google's hosted identity library.

## Changelog

### 2026-11-13

- Documented Phase 2 MVC structure, database setup, Google sign-in dependencies, server deployment, account preparation, navigation, and submission requirements.
- Retained the original schema dump alongside the fresh-database snapshot and incremental sample-data script.

### 2026-09-30

- Added the initial frontend files, landing page, styles, and JavaScript.
