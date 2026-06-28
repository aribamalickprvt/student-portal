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
            getArticleById($_GET['id'], $conn);
        } else {
            getAllArticles($conn);
        }
        break;
        
    case 'POST':
        createArticle($conn);
        break;
        
    case 'PUT':
        updateArticle($conn);
        break;
        
    case 'DELETE':
        deleteArticle($conn);
        break;
        
    default:
        ApiResponse::badRequest("Method not allowed");
}

$database->disconnect();

// Get all articles
function getAllArticles($conn) {
    $category_id = Request::getParam('category');
    $search = Request::getParam('search');
    $limit = Request::getParam('limit', 10);
    $offset = Request::getParam('offset', 0);
    
    $sql = "SELECT a.*, c.name as category_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($category_id) {
        $sql .= " AND a.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }
    
    if ($search) {
        $sql .= " AND (a.title LIKE ? OR a.content LIKE ? OR a.author LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }
    
    $sql .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
    $params[] = (int)$limit;
    $params[] = (int)$offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $articles = [];
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM articles WHERE 1=1";
    if ($category_id) {
        $countSql .= " AND category_id = {$category_id}";
    }
    $totalResult = $conn->query($countSql);
    $total = $totalResult->fetch_assoc()['total'];
    
    ApiResponse::success([
        'articles' => $articles,
        'pagination' => [
            'total' => (int)$total,
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'has_more' => ($offset + $limit) < $total
        ]
    ]);
}

// Get article by ID
function getArticleById($id, $conn) {
    $id = Validator::sanitize($id);
    
    // Increment view count
    $updateStmt = $conn->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
    $updateStmt->bind_param("i", $id);
    $updateStmt->execute();
    
    // Get article
    $stmt = $conn->prepare("SELECT a.*, c.name as category_name 
                           FROM articles a 
                           LEFT JOIN categories c ON a.category_id = c.id 
                           WHERE a.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        ApiResponse::notFound("Article not found");
    }
    
    $article = $result->fetch_assoc();
    
    // Get related articles
    $relatedStmt = $conn->prepare("SELECT id, title, category_id 
                                   FROM articles 
                                   WHERE category_id = ? AND id != ? 
                                   ORDER BY RAND() 
                                   LIMIT 3");
    $relatedStmt->bind_param("ii", $article['category_id'], $id);
    $relatedStmt->execute();
    $relatedResult = $relatedStmt->get_result();
    
    $related = [];
    while ($row = $relatedResult->fetch_assoc()) {
        $related[] = $row;
    }
    
    $article['related_articles'] = $related;
    
    ApiResponse::success($article);
}

// Create article (Admin only - simplified for demo)
function createArticle($conn) {
    $data = Request::getBody();
    
    $required = ['title', 'content', 'category_id', 'author'];
    $validation = Validator::validateRequired($required, $data);
    
    if ($validation !== true) {
        ApiResponse::badRequest("Missing required fields", $validation);
    }
    
    $title = Validator::sanitize($data['title']);
    $content = Validator::sanitize($data['content']);
    $category_id = (int)$data['category_id'];
    $author = Validator::sanitize($data['author']);
    
    $stmt = $conn->prepare("INSERT INTO articles (title, content, category_id, author) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $title, $content, $category_id, $author);
    
    if ($stmt->execute()) {
        $article_id = $conn->insert_id;
        ApiResponse::success(['id' => $article_id], "Article created successfully", 201);
    } else {
        ApiResponse::serverError("Failed to create article");
    }
}

// Update article
function updateArticle($conn) {
    $data = Request::getBody();
    
    if (!isset($data['id'])) {
        ApiResponse::badRequest("Article ID required");
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
    
    if (isset($data['content'])) {
        $updates[] = "content = ?";
        $params[] = Validator::sanitize($data['content']);
        $types .= "s";
    }
    
    if (isset($data['category_id'])) {
        $updates[] = "category_id = ?";
        $params[] = (int)$data['category_id'];
        $types .= "i";
    }
    
    if (empty($updates)) {
        ApiResponse::badRequest("No fields to update");
    }
    
    $params[] = $id;
    $types .= "i";
    
    $sql = "UPDATE articles SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        ApiResponse::success(null, "Article updated successfully");
    } else {
        ApiResponse::serverError("Failed to update article");
    }
}

// Delete article
function deleteArticle($conn) {
    $id = Request::getParam('id');
    
    if (!$id) {
        ApiResponse::badRequest("Article ID required");
    }
    
    $id = Validator::sanitize($id);
    
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        ApiResponse::success(null, "Article deleted successfully");
    } else {
        ApiResponse::serverError("Failed to delete article");
    }
}
?>