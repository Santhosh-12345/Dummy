<?php
require_once '../config/db.php'; // Adjust the path if needed

// CORS Headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "status" => "fail", 
        "message" => "Email and password are required"
    ]);
    exit();
}

// Get DB connection
$conn = getDBConnection();

// Prepare SQL statement (MySQLi style)
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
if ($stmt) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            "success" => true,
            "status" => "success",
            "user" => [
                "id" => $user['id'],
                "full_name" => $user['full_name'],
                "email" => $user['email'],
                "role" => $user['role']
            ],
            "message" => "Login successful"
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "status" => "fail",
            "message" => "Invalid email or password"
        ]);
    }

    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "status" => "error",
        "message" => "Query preparation failed: " . $conn->error
    ]);
}

$conn->close();
