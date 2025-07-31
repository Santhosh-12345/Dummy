<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database configuration
require_once '../config/db.php';

// Get database connection (MySQLi OOP)
$conn = getMySQLiConnection(); // ⚠️ Make sure this function exists and returns a MySQLi object

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => $conn->connect_error
    ]);
    exit();
}

$sql = "SELECT * FROM jobs ORDER BY postedDate DESC";
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to execute query',
        'message' => $conn->error
    ]);
    exit();
}

$jobs = [];
while ($row = $result->fetch_assoc()) {
    // Format the date
    if (!empty($row['postedDate'])) {
        $date = new DateTime($row['postedDate']);
        $row['postedDate'] = $date->format('M d, Y');
    }
    $jobs[] = $row;
}

echo json_encode([
    'success' => true,
    'jobs' => $jobs,
    'count' => count($jobs)
]);

$conn->close();
