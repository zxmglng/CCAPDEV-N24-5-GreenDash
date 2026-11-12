<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/stall-model.php';
require_once __DIR__ . '/../app/models/user-model.php';
require_once __DIR__ . '/../app/models/stall-hours-model.php';

$error = '';
$flashAction = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        $days = $_POST['days'] ?? [];
        $opens = trim($_POST['opens_at'] ?? '');
        $closes = trim($_POST['closes_at'] ?? '');
        $hours_rows = [];

        if (is_array($days) && $opens && $closes) {
            foreach ($days as $day) {
                if (is_numeric($day) && $day >= 0 && $day <= 6) {
                    $hours_rows[] = [
                        'day_of_week' => (int) $day,
                        'opens_at'    => $opens . ':00',
                        'closes_at'   => $closes . ':00'
                    ];
                }
            }
        }

        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $vendorId = ($_POST['vendor_id'] ?? '') !== '' ? (int) $_POST['vendor_id'] : null;
            $location = trim($_POST['location'] ?? '');
            $isOpen = ($_POST['is_open'] ?? '1') === '1';

            if ($name === '' || $location === '' || empty($hours_rows)) {
                throw new InvalidArgumentException('Please fill in all fields and select at least one operating day.');
            }

            add_stall($name, $vendorId, $location, $isOpen, $hours_rows);
            header('Location: manage-stores.php');
            exit;

        } elseif ($action === 'edit') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $vendorId = ($_POST['vendor_id'] ?? '') !== '' ? (int) $_POST['vendor_id'] : null;
            $location = trim($_POST['location'] ?? '');
            $isOpen = ($_POST['is_open'] ?? '1') === '1';

            if ($id <= 0 || $name === '' || $location === '' || empty($hours_rows)) {
                throw new InvalidArgumentException('Please fill in all required fields and select at least one operating day.');
            }

            update_stall($id, $name, $vendorId, $location, $isOpen, $hours_rows);
            header('Location: manage-stores.php');
            exit;

        } elseif ($action === 'delete') {
            delete_stall((int) ($_POST['id'] ?? 0));
            header('Location: manage-stores.php');
            exit;
        }
    } catch (Throwable $e) {
        error_log('Admin stall action failed: ' . $e->getMessage());
        $error = ($e instanceof InvalidArgumentException || $e instanceof RuntimeException)
            ? $e->getMessage()
            : 'Unable to complete that action. Please try again.';
        $flashAction = $action;
    }
}

$stalls = get_stalls();
$vendors = get_vendors();
$allHours = get_all_hours_grouped($pdo); 
$page = 'manage-stores';
define('GREENDASH_ADMIN_VIEW', true);
require __DIR__ . '/../app/views/admin/manage-stores.php';