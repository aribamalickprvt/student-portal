<?php
// API Configuration and Database Connection

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to client
ini_set('log_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'ariba900');
define('DB_NAME', 'student_portal');

// API Configuration
define('API_VERSION', 'v1');
define('API_BASE_URL', '/student-portal/api/v1/');

// CORS Headers for API access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database Connection Class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
             
            
            $this->conn->set_charset("utf8mb4");
            
        } catch(Exception $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        }

        return $this->conn;
    }

    public function disconnect() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Base API Response Class
class ApiResponse {
    public static function success($data = null, $message = "Success", $code = 200) {
        http_response_code($code);
        echo json_encode([
            'status' => 'success',
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ]);
        exit();
    }

    public static function error($message = "Error occurred", $code = 400, $errors = null) {
        http_response_code($code);
        echo json_encode([
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => time()
        ]);
        exit();
    }

    public static function unauthorized($message = "Unauthorized access") {
        self::error($message, 401);
    }

    public static function notFound($message = "Resource not found") {
        self::error($message, 404);
    }

    public static function badRequest($message = "Bad request", $errors = null) {
        self::error($message, 400, $errors);
    }

    public static function serverError($message = "Internal server error") {
        self::error($message, 500);
    }
}

// Input Validation and Sanitization
class Validator {
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validateRequired($fields, $data) {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missing[] = $field;
            }
        }
        return empty($missing) ? true : $missing;
    }

    public static function validateLength($string, $min = 0, $max = PHP_INT_MAX) {
        $length = strlen($string);
        return $length >= $min && $length <= $max;
    }
}

// JWT Token Handler (Simple implementation)
class JWT {
    private static $secret_key = "your_secret_key_here_change_in_production";
    
    public static function encode($data) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(array_merge($data, ['exp' => time() + 86400])); // 24 hour expiry
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public static function decode($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header, $payload, $signature) = $parts;
        
        $signatureCheck = hash_hmac('sha256', $header . "." . $payload, self::$secret_key, true);
        $base64UrlSignatureCheck = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signatureCheck));
        
        if ($base64UrlSignatureCheck !== $signature) {
            return false;
        }
        
        $payloadData = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
        
        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
            return false; // Token expired
        }
        
        return $payloadData;
    }
    
    public static function getAuthToken() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}

// Request Parser
class Request {
    public static function getBody() {
        return json_decode(file_get_contents('php://input'), true);
    }

    public static function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function getParam($key, $default = null) {
        return isset($_GET[$key]) ? Validator::sanitize($_GET[$key]) : $default;
    }
}
?>