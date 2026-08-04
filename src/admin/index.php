<?php
require_once __DIR__ . '/../lib/admin_guard.php';
require_admin_role('s');
header('Location: /src/admin/stats.php');
exit;
