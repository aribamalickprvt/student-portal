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
            getCategoryById($_GET['id'], $conn);
        } else {
            getAllCategories($conn);
        }
        break;
        
    case 'POST':
        createCategory($conn);
        break;
        
    default:
        ApiResponse::badRequest("Method not allowed");
}

$database->disconnect();

// Get all categories with counts
function getAllCategories($conn) {
    $sql = "SELECT c.*, 
           (SELECT COUNT(*) FROM articles WHERE category_id = c.id) as article_count,
           (SELECT COUNT(*) FROM success_tips WHERE category_id = c.id) as tip_count
           FROM categories c
           ORDER BY c.name";
    
    $result = $conn->query($sql);
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    ApiResponse::success([
        'categories' => $categories,
        'count' => count($categories)
    ]);
}

// Get category by ID with content
function getCategoryById($id, $conn) {
    $id = Validator::sanitize($id);
    
    // Get category
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        ApiResponse::notFound("Category not found");
    }
    
    $category = $result->fetch_assoc();
    
    // Get articles count
    $articleStmt = $conn->prepare("SELECT COUNT(*) as count FROM articles WHERE category_id = ?");
    $articleStmt->bind_param("i", $id);
    $articleStmt->execute();
    $category['article_count'] = $articleStmt->get_result()->fetch_assoc()['count'];
    
    // Get tips count
    $tipStmt = $conn->prepare("SELECT COUNT(*) as count FROM success_tips WHERE category_id = ?");
    $tipStmt->bind_param("i", $id);
    $tipStmt->execute();
    $category['tip_count'] = $tipStmt->get_result()->fetch_assoc()['count'];
    
    ApiResponse::success($category);
}

// Create category
function createCategory($conn) {
    $data = Request::getBody();
    
    $required = ['name', 'description'];
    $validation = Validator::validateRequired($required, $data);
    
    if ($validation !== true) {
        ApiResponse::badRequest("Missing required fields", $validation);
    }
    
    $name = Validator::sanitize($data['name']);
    $description = Validator::sanitize($data['description']);
    $icon = isset($data['icon']) ? Validator::sanitize($data['icon']) : '📁';
    
    $stmt = $conn->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $description, $icon);
    
    if ($stmt->execute()) {
        $category_id = $conn->insert_id;
        ApiResponse::success(['id' => $category_id], "Category created successfully", 201);
    } else {
        ApiResponse::serverError("Failed to create category");
    }
}
?>