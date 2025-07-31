<?php
// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function getDBConnection() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'job_portal';

    // Create MySQLi object-oriented connection
    $conn = new mysqli($host, $user, $pass, $db);

    // Check connection
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed',
            'error' => $conn->connect_error
        ]);
        exit();
    }

    // Optional: Set charset (default utf8mb4 is fine for most cases)
    $conn->set_charset("utf8mb4");

    return $conn;
}

// 👇 ADD THIS LINE to initialize $conn (instead of $pdo)
$conn = getDBConnection();
