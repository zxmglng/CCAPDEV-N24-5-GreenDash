<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();

$csrfToken = adminCsrfToken();
define('GREENDASH_ADMIN_VIEW', true);
require __DIR__ . '/../app/views/admin/store-requests.php';
