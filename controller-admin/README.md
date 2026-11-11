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
* **`logout.php`** - Accepts a POST with a valid CSRF token, clears admin authentication, and redirects to login. Other user-session values are preserved.
* **`home.php`** - Requires an admin session and renders `app/views/admin/home.php`.
* **`manage-stores.php`** - Requires an admin session and renders `app/views/admin/manage-stores.php`.
* **`manage-users.php`** - Requires an admin session and renders `app/views/admin/manage-users.php`.
* **`store-requests.php`** - Requires an admin session and renders `app/views/admin/store-requests.php`.

## Admin authentication

Open `/<project-folder>/controller-admin/login.php`, or use the admin link on the
root landing page. Old `app/views/admin/*.php` URLs redirect to these controllers.
Views are included by their controllers; their browser asset URLs are relative
to `controller-admin/`.

`app/core/admin-auth.php` verifies the key server-side and manages the
`admin_authenticated` and `admin_csrf_token` session values. The existing shared
key is preserved. To override it on the VM, configure the PHP process environment
variable `GREENDASH_ADMIN_KEY`. The key is not sent to JavaScript or browser
storage. Successful login rotates the session ID, then redirects to `home.php`.

Authentication does not require the database. The management screens still use
their existing frontend data; database CRUD is a separate task.
