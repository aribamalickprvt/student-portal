<?php
require_once '../config.php';

// Authentication API Endpoint
// Handles: Login, Register, Logout

$method = Request::getMethod();

switch ($method) {
    case 'POST':
        $data = Request::getBody();
        
        // Determine action based on endpoint
        $action = Request::getParam('action', 'login');
        
        if ($action === 'login') {
            handleLogin($data);
        } elseif ($action === 'register') {
            handleRegister($data);
        } else {
            ApiResponse::badRequest("Invalid action");
        }
        break;
        
    case 'DELETE':
        handleLogout();
        break;
        
    case 'GET':
        // Verify token
        verifyToken();
        break;
        
    default:
        ApiResponse::badRequest("Method not allowed");
}

// Login Handler
function handleLogin($data) {
    // Validate required fields
    $required = ['email', 'password'];
    $validation = Validator::validateRequired($required, $data);
    
    if ($validation !== true) {
        ApiResponse::badRequest("Missing required fields", $validation);
    }
    
    $email = Validator::sanitize($data['email']);
    $password = $data['password'];
    
    // Validate email format
    if (!Validator::validateEmail($email)) {
        ApiResponse::badRequest("Invalid email format");
    }
    
    // Database connection
    $database = new Database();
    $conn = $database->connect();
    
    if (!$conn) {
        ApiResponse::serverError("Database connection failed");
    }
    
    // Query user
    $stmt = $conn->prepare("SELECT id, student_id, name, email, password FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        ApiResponse::unauthorized("Invalid credentials");
    }
    
    $user = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        ApiResponse::unauthorized("Invalid credentials");
    }
    
    // Generate JWT token
    $token = JWT::encode([
        'student_id' => $user['id'],
        'email' => $user['email'],
        'name' => $user['name']
    ]);
    
    // Return success with token
    ApiResponse::success([
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'student_id' => $user['student_id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ], "Login successful");
    
    $database->disconnect();
}

// Register Handler
function handleRegister($data) {
    // Validate required fields
    $required = ['student_id', 'name', 'email', 'password'];
    $validation = Validator::validateRequired($required, $data);
    
    if ($validation !== true) {
        ApiResponse::badRequest("Missing required fields", $validation);
    }
    
    $student_id = Validator::sanitize($data['student_id']);
    $name = Validator::sanitize($data['name']);
    $email = Validator::sanitize($data['email']);
    $password = $data['password'];
    
    // Validate email
    if (!Validator::validateEmail($email)) {
        ApiResponse::badRequest("Invalid email format");
    }
    
    // Validate password length
    if (!Validator::validateLength($password, 6)) {
        ApiResponse::badRequest("Password must be at least 6 characters");
    }
    
    // Database connection
    $database = new Database();
    $conn = $database->connect();
    
    if (!$conn) {
        ApiResponse::serverError("Database connection failed");
    }
    
    // Check if email or student_id already exists
    $stmt = $conn->prepare("SELECT id FROM students WHERE email = ? OR student_id = ?");
    $stmt->bind_param("ss", $email, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        ApiResponse::badRequest("Email or Student ID already registered");
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO students (student_id, name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $student_id, $name, $email, $hashed_password);
    
    if ($stmt->execute()) {
        $new_user_id = $conn->insert_id;
        
        // Generate token
        $token = JWT::encode([
            'student_id' => $new_user_id,
            'email' => $email,
            'name' => $name
        ]);
        
        ApiResponse::success([
            'token' => $token,
            'user' => [
                'id' => $new_user_id,
                'student_id' => $student_id,
                'name' => $name,
                'email' => $email
            ]
        ], "Registration successful", 201);
    } else {
        ApiResponse::serverError("Registration failed");
    }
    
    $database->disconnect();
}

// Logout Handler
function handleLogout() {
    $token = JWT::getAuthToken();
    
    if (!$token) {
        ApiResponse::unauthorized("No token provided");
    }
    
    // In a production system, you would blacklist the token here
    // For now, just return success
    ApiResponse::success(null, "Logout successful");
}

// Verify Token
function verifyToken() {
    $token = JWT::getAuthToken();
    
    if (!$token) {
        ApiResponse::unauthorized("No token provided");
    }
    
    $decoded = JWT::decode($token);
    
    if (!$decoded) {
        ApiResponse::unauthorized("Invalid or expired token");
    }
    
    ApiResponse::success($decoded, "Token is valid");
}
?>