<?php
require_once '../config.php';

// Verify authentication
$token = JWT::getAuthToken();
$decoded = JWT::decode($token);

if (!$token || !$decoded) {
    ApiResponse::unauthorized("Authentication required");
}

$student_id = $decoded['student_id'];

$method = Request::getMethod();
$database = new Database();
$conn = $database->connect();

if (!$conn) {
    ApiResponse::serverError("Database connection failed");
}

switch ($method) {
    case 'GET':
        getUserFeedback($student_id, $conn);
        break;
        
    case 'POST':
        createFeedback($student_id, $conn);
        break;
        
    default:
        ApiResponse::badRequest("Method not allowed");
}

$database->disconnect();

// Get user's feedback history
function getUserFeedback($student_id, $conn) {
    $limit = Request::getParam('limit', 5);
    
    $stmt = $conn->prepare("SELECT * FROM feedback 
                           WHERE student_id = ? 
                           ORDER BY created_at DESC 
                           LIMIT ?");
    $stmt->bind_param("ii", $student_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $feedback = [];
    while ($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }
    
    ApiResponse::success([
        'feedback' => $feedback,
        'count' => count($feedback)
    ]);
}

// Create feedback
function createFeedback($student_id, $conn) {
    $data = Request::getBody();
    
    $required = ['subject', 'message', 'rating'];
    $validation = Validator::validateRequired($required, $data);
    
    if ($validation !== true) {
        ApiResponse::badRequest("Missing required fields", $validation);
    }
    
    $subject = Validator::sanitize($data['subject']);
    $message = Validator::sanitize($data['message']);
    $rating = (int)$data['rating'];
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        ApiResponse::badRequest("Rating must be between 1 and 5");
    }
    
    $stmt = $conn->prepare("INSERT INTO feedback (student_id, subject, message, rating) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $student_id, $subject, $message, $rating);
    
    if ($stmt->execute()) {
        $feedback_id = $conn->insert_id;
        ApiResponse::success(['id' => $feedback_id], "Feedback submitted successfully", 201);
    } else {
        ApiResponse::serverError("Failed to submit feedback");
    }
}
?>