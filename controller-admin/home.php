<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/stall-model.php';
require_once __DIR__ . '/../app/models/user-model.php';
require_once __DIR__ . '/../app/models/store-request-model.php';

$stats = [
    'pendingRequests' => get_pending_request_count($pdo),
    'stalls'          => get_stall_counts($pdo),
    'activeUsers'     => get_active_user_count($pdo),
];

$page = 'home';
define('GREENDASH_ADMIN_VIEW', true);
require __DIR__ . '/../app/views/admin/home.php';