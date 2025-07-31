<?php
// Disable error reporting in production
ini_set('display_errors', 0);
error_reporting(0);

// CORS Headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Max-Age: 3600");

// Preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database
require_once '../../config/db.php';

// Get input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit;
}

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract & clean values
    $title = $data['title'] ?? '';
    $company = $data['company'] ?? '';
    $location = $data['location'] ?? '';
    $type = $data['type'] ?? 'Full-time';
    $salary = $data['salary'] ?? '';
    $description = $data['description'] ?? '';
    $posted_by = $data['user_id'] ?? 0;
    $skills = isset($data['skills']) ? (is_array($data['skills']) ? implode(',', $data['skills']) : $data['skills']) : '';
    $category = $data['category'] ?? 'IT';

    $stmt = $conn->prepare("
        INSERT INTO jobs 
        (title, company, location, type, salary, description, posted_by, postedDate, skills, category)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
    ");

    if ($stmt) {
        $stmt->bind_param("ssssssiss", $title, $company, $location, $type, $salary, $description, $posted_by, $skills, $category);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Job posted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Execution failed',
                'message' => $stmt->error
            ]);
        }

        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Preparation failed',
            'message' => $conn->error
        ]);
    }
}

$conn->close();
