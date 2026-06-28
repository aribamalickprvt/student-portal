<?php
require_once '../config.php';

// Verify authentication
$token = JWT::getAuthToken();
if (!$token || !JWT::decode($token)) {
    ApiResponse::unauthorized("Authentication required");
}

$method = Request::getMethod();
$database = new Database();
$conn = $database->connect();

if (!$conn) {
    ApiResponse::serverError("Database connection failed");
}

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getTipById($_GET['id'], $conn);
        } else {
            getAllTips($conn);
        }
        break;
        
    case 'POST':
        createTip($conn);
        break;
        
    case 'PUT':
        updateTip($conn);
        break;
        
    case 'DELETE':
        deleteTip($conn);
        break;
        
    default:
        ApiResponse::badRequest("Method not allowed");
}

$database->disconnect();

// Get all tips
function getAllTips($conn) {
    $category_id = Request::getParam('category');
    $priority = Request::getParam('priority');
    $limit = Request::getParam('limit', 20);
    $random = Request::getParam('random', 'false');
    
    $sql = "SELECT t.*, c.name as category_name 
            FROM success_tips t 
            LEFT JOIN categories c ON t.category_id = c.id 
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($category_id) {
        $sql .= " AND t.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }
    
    if ($priority) {
        $sql .= " AND t.priority = ?";
        $params[] = $priority;
        $types .= "i";
    }
    
    if ($random === 'true') {
        $sql .= " ORDER BY RAND()";
    } else {
        $sql .= " ORDER BY t.priority DESC, t.created_at DESC";
    }
    
    $sql .= " LIMIT ?";
    $params[] = (int)$limit;
    $types .= "i";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tips = [];
    while ($row = $result->fetch_assoc()) {
        $tips[] = $row;
    }
    
    ApiResponse::success(['tips' => $tips, 'count' => count($tips)]);
}

// Get tip by ID
function getTipById($id, $conn) {
    $id = Validator::sanitize($id);
    
    $stmt = $conn->prepare("SELECT t.*, c.name as category_name 
                           FROM success_tips t 
                           LEFT JOIN categories c ON t.category_id = c.id 
                           WHERE t.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        ApiResponse::notFound("Tip not found");
    }
    
    $tip = $result->fetch_assoc();
    ApiResponse::success($tip);
}

// Create tip
function createTip($conn) {
    $data = Request::getBody();
    
    $required = ['title', 'tip_content', 'category_id'];
    $validation = Validator::validateRequired($required, $data);
    
    if ($validation !== true) {
        ApiResponse::badRequest("Missing required fields", $validation);
    }
    
    $title = Validator::sanitize($data['title']);
    $tip_content = Validator::sanitize($data['tip_content']);
    $category_id = (int)$data['category_id'];
    $priority = isset($data['priority']) ? (int)$data['priority'] : 0;
    
    $stmt = $conn->prepare("INSERT INTO success_tips (title, tip_content, category_id, priority) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $title, $tip_content, $category_id, $priority);
    
    if ($stmt->execute()) {
        $tip_id = $conn->insert_id;
        ApiResponse::success(['id' => $tip_id], "Tip created successfully", 201);
    } else {
        ApiResponse::serverError("Failed to create tip");
    }
}

// Update tip
function updateTip($conn) {
    $data = Request::getBody();
    
    if (!isset($data['id'])) {
        ApiResponse::badRequest("Tip ID required");
    }
    
    $id = (int)$data['id'];
    $updates = [];
    $params = [];
    $types = "";
    
    if (isset($data['title'])) {
        $updates[] = "title = ?";
        $params[] = Validator::sanitize($data['title']);
        $types .= "s";
    }
    
    if (isset($data['tip_content'])) {
        $updates[] = "tip_content = ?";
        $params[] = Validator::sanitize($data['tip_content']);
        $types .= "s";
    }
    
    if (isset($data['priority'])) {
        $updates[] = "priority = ?";
        $params[] = (int)$data['priority'];
        $types .= "i";
    }
    
    if (empty($updates)) {
        ApiResponse::badRequest("No fields to update");
    }
    
    $params[] = $id;
    $types .= "i";
    
    $sql = "UPDATE success_tips SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        ApiResponse::success(null, "Tip updated successfully");
    } else {
        ApiResponse::serverError("Failed to update tip");
    }
}

// Delete tip
function deleteTip($conn) {
    $id = Request::getParam('id');
    
    if (!$id) {
        ApiResponse::badRequest("Tip ID required");
    }
    
    $id = Validator::sanitize($id);
    
    $stmt = $conn->prepare("DELETE FROM success_tips WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        ApiResponse::success(null, "Tip deleted successfully");
    } else {
        ApiResponse::serverError("Failed to delete tip");
    }
}
?>