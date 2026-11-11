<?php
// Compatibility route for the old view URL; preserve POST requests.
header('Location: ../../../controller-admin/logout.php', true, 307);
exit;
