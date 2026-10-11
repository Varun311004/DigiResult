<?php
require_once __DIR__ . '/../includes/bootstrap.php';
logout_all();
header('Location: ' . dr_url('index.php'));
exit;
