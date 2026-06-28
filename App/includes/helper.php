<?php
// API Helper Functions for UI Layer

define('API_BASE_URL', 'http://localhost/student-portal/api/v1/');

/**
 * Make API Request
 */
function apiRequest($endpoint, $method = 'GET', $data = null, $requireAuth = true) {
    $url = API_BASE_URL . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $headers = ['Content-Type: application/json'];
    
    // Add authorization token if required
    if ($requireAuth && isset($_SESSION['token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Set method and data
    switch ($method) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    // Handle unauthorized (token expired)
    if ($http_code === 401) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
    
    return [
        'success' => ($http_code >= 200 && $http_code < 300),
        'code' => $http_code,
        'data' => $result['data'] ?? null,
        'message' => $result['message'] ?? '',
        'response' => $result
    ];
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['token']) && !empty($_SESSION['token']);
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Get current student info from session
 */
function getCurrentStudent() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['student_id'] ?? null,
        'name' => $_SESSION['student_name'] ?? '',
        'email' => $_SESSION['student_email'] ?? ''
    ];
}

/**
 * Fetch Articles
 */
function fetchArticles($category = null, $limit = 10, $offset = 0) {
    $endpoint = 'articles';
    $params = [];
    
    if ($category) $params[] = "category={$category}";
    if ($limit) $params[] = "limit={$limit}";
    if ($offset) $params[] = "offset={$offset}";
    
    if (!empty($params)) {
        $endpoint .= '?' . implode('&', $params);
    }
    
    return apiRequest($endpoint);
}

/**
 * Fetch Single Article
 */
function fetchArticle($id) {
    return apiRequest("articles/{$id}");
}

/**
 * Fetch Tips
 */
function fetchTips($category = null, $limit = 20, $random = false) {
    $endpoint = 'tips';
    $params = [];
    
    if ($category) $params[] = "category={$category}";
    if ($limit) $params[] = "limit={$limit}";
    if ($random) $params[] = "random=true";
    
    if (!empty($params)) {
        $endpoint .= '?' . implode('&', $params);
    }
    
    return apiRequest($endpoint);
}

/**
 * Fetch Categories
 */
function fetchCategories() {
    return apiRequest('categories');
}

/**
 * Fetch Category by ID
 */
function fetchCategory($id) {
    return apiRequest("categories/{$id}");
}

/**
 * Fetch User Feedback
 */
function fetchUserFeedback($limit = 5) {
    return apiRequest("feedback?limit={$limit}");
}

/**
 * Submit Feedback
 */
function submitFeedback($subject, $message, $rating) {
    $data = [
        'subject' => $subject,
        'message' => $message,
        'rating' => $rating
    ];
    
    return apiRequest('feedback', 'POST', $data);
}

/**
 * Get Statistics
 */
function getStatistics() {
    // Fetch from API
    $articlesResult = apiRequest('articles?limit=1');
    $tipsResult = apiRequest('tips?limit=1');
    $categoriesResult = apiRequest('categories');
    
    return [
        'article_count' => $articlesResult['data']['pagination']['total'] ?? 0,
        'tip_count' => $tipsResult['data']['count'] ?? 0,
        'category_count' => $categoriesResult['data']['count'] ?? 0
    ];
}
?>