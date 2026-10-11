<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$adminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id']: null;
if ($adminId) audit('admin_logout', $adminId, 'Administrator logout');
logout_all();
header('Location: ' . dr_url('index.php'));
exit;
