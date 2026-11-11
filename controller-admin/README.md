# Admin Controllers

Folder for php files that users visit in the browser. These files contain the logic for each browser page

No raw HTML or SQL belongs here.
Should contain:
1. `session_start()` annd security checks e.g., `if (!isset($_SESSION['admin_id']))`
2. loading of database and required model phps
3. handle `$_POST` request (CRUD)
4. fetch data via model phps
5. include a `require_once` of the matching view php (html only php found at projectfolder/app/view/..)

## Files
* **`login.php`** - Admin login, set `$_SESSION` and redirect to dashboard. Loads `views/admin/login.php`
* **`logout.php`** - Destroys session and redirects to index.php. Loads `views/admin/logout.php`
* **`home.php`** - Home page. Includes some statistics (counts). Loads `views/admin/home.php`
* **`manage-stroes.php`** - CRUD for stalls. Loads `views/admin/manage-stores.php`
* **`manage-users.php`** - CRUD for users. Loads `views/admin/manage-users.php`
* **`store-requests.php`** - Handles requests from vendors to add/remove stalls. Loads `views/admin/store-requests.php`