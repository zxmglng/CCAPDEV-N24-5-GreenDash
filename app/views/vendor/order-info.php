<?php
// This placeholder uses the existing management screen until a dedicated view is implemented.
$queryString = $_SERVER['QUERY_STRING'] ?? '';
header('Location: orders.php' . ($queryString !== '' ? '?' . $queryString : ''), true, 302);
exit;
