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

// Validate user_id input
$userId = $_GET['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

// Get DB connection
$conn = getDBConnection();

// SQL query
$sql = "SELECT j.id, j.title, j.company, j.location, j.category, a.applied_at 
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE a.seeker_id = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $userId);  // i = integer
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];

    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    echo json_encode($rows);
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Query preparation failed",
        "error" => $conn->error
    ]);
}

$conn->close();
