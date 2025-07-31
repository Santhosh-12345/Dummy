<?php
// Turn off all error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Set headers first
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
function getDBConnection() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'job_portal';

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'error' => 'Database connection failed',
            'message' => $conn->connect_error
        ]));
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

$conn = getDBConnection();

$userId = $_GET['user_id'] ?? null;
if (!$userId || !is_numeric($userId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
    exit;
}

// Get employer details
$stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE id = ? AND role = 'employer'");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Employer not found']);
        $conn->close();
        exit;
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Query error: ' . $conn->error]);
    $conn->close();
    exit;
}

// Get jobs posted by employer
$stmt = $conn->prepare("SELECT id, title, company, location, type, salary, postedDate FROM jobs WHERE posted_by = ?");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $jobs = [];

    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }

    echo json_encode([
        'success' => true,
        'user' => $user,
        'jobs' => $jobs,
        'stats' => [
            'total_jobs' => count($jobs)
        ]
    ]);

    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Query error: ' . $conn->error]);
}

$conn->close();
