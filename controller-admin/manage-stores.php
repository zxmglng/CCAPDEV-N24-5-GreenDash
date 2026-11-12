<?php
require_once __DIR__ . '/../app/core/admin-auth.php';
requireAdminLogin();
require_once __DIR__ . '/../app/core/db.php';
require_once __DIR__ . '/../app/models/stall-model.php';
require_once __DIR__ . '/../app/models/user-model.php';
require_once __DIR__ . '/../app/models/stall-hours-model.php';

$error = '';
$flashAction = '';

function build_hours_rows($hours_input) {
    if (!is_array($hours_input) || empty($hours_input)) {
        throw new InvalidArgumentException('Please select at least one operating day.');
    }

    $rows = [];
    $pattern = '/^([01]\d|2[0-3]):[0-5]\d$/';
    foreach ($hours_input as $day => $entry) {
        $day = (int) $day;

        if ($day < 0 || $day > 6) {
            throw new InvalidArgumentException('Invalid operating day selected.');
        }
        if (empty($entry['enabled'])) {
            continue;
        }

        $opens = trim($entry['opens'] ?? '');
        $closes = trim($entry['closes'] ?? '');

        if (!preg_match($pattern, $opens) || !preg_match($pattern, $closes)) {
            throw new InvalidArgumentException('Please enter a valid opening and closing time for every selected day.');
        }
        if ($opens >= $closes) {
            throw new InvalidArgumentException('Opening time must be earlier than closing time for every selected day.');
        }
        $rows[] = [
            'day_of_week' => $day,
            'opens_at' => $opens . ':00',
            'closes_at' => $closes . ':00',
        ];
    }
    if (empty($rows)) {
        throw new InvalidArgumentException('Please select at least one operating day.');
    }
    return $rows;
}

function validate_vendor_id($vendorId, array $vendors) {
    if ($vendorId === null) {
        return null;
    }
    foreach ($vendors as $v) {
        if ((int) $v['user_id'] === (int) $vendorId) {
            return (int) $vendorId;
        }
    }
    throw new InvalidArgumentException('The selected vendor does not exist.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $vendors = get_vendors();

    try {
        if ($action === 'add') {
            $hours_rows = build_hours_rows($_POST['hours'] ?? []);

            $name = trim($_POST['name'] ?? '');
            $vendorId = ($_POST['vendor_id'] ?? '') !== '' ? (int) $_POST['vendor_id'] : null;
            $location = trim($_POST['location'] ?? '');
            $isOpen = ($_POST['is_open'] ?? '1') === '1';

            if ($name === '' || $location === '') {
                throw new InvalidArgumentException('Please fill in all fields.');
            }
            $vendorId = validate_vendor_id($vendorId, $vendors);

            add_stall($name, $vendorId, $location, $isOpen, $hours_rows);
            header('Location: manage-stores.php');
            exit;

        } elseif ($action === 'edit') {
            $hours_rows = build_hours_rows($_POST['hours'] ?? []);
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $vendorId = ($_POST['vendor_id'] ?? '') !== '' ? (int) $_POST['vendor_id'] : null;
            $location = trim($_POST['location'] ?? '');
            $isOpen = ($_POST['is_open'] ?? '1') === '1';

            if ($id <= 0 || $name === '' || $location === '') {
                throw new InvalidArgumentException('Please fill in all required fields.');
            }
            $vendorId = validate_vendor_id($vendorId, $vendors);

            update_stall($id, $name, $vendorId, $location, $isOpen, $hours_rows);
            header('Location: manage-stores.php');
            exit;

        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new InvalidArgumentException('Invalid stall selected.');
            }
            delete_stall($id);
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