<?php
$queryString = $_SERVER['QUERY_STRING'] ?? '';
header('Location: ../../../controller-vendor/orders.php' . ($queryString !== '' ? '?' . $queryString : ''), true, 302);
exit;
