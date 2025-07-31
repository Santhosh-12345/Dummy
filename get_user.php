<?php
include_once('../config/db.php');
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$userId = $_GET['user_id'] ?? null;

if (!$userId || !is_numeric($userId)) {
    echo json_encode(['error' => 'Missing or invalid user_id']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'User not found']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
}

$conn->close();
