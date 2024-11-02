<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header('Content-Type: application/json');

if (isset($_POST['batch']) && isset($_POST['semester']) && isset($_POST['department']) && isset($_POST['new_status'])) {
    $batch = $_POST['batch'];
    $semester = $_POST['semester'];
    $department = $_POST['department'];
    $newStatus = $_POST['new_status'] == 1;
    
    $key = "{$batch}_{$semester}_{$department}";
    $_SESSION['results_live'][$key] = $newStatus;
    
    echo json_encode(['status' => 'success', 'message' => 'Live status updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
}
