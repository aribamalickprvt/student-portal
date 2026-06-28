<?php
require_once '../config.php';
require_login();

header('Content-Type: application/json');

// Get the request data
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message']) || empty(trim($input['message']))) {
    echo json_encode([
        'success' => false,
        'error' => 'Message is required'
    ]);
    exit();
}

$user_message = trim($input['message']);

// Ollama API configuration
$ollama_url = 'http://localhost:11434/api/generate';
$model = 'tinyllama'; // CHANGED TO FASTER MODEL - you can also use 'llama2' or 'mistral'

// Shortened, more concise system prompt for faster responses
$system_prompt = "You are a helpful student assistant. Provide brief, practical advice on student wellbeing, study tips, stress management, career guidance, and technology topics. Keep answers under 150 words and be encouraging.";

// Build concise prompt
$full_prompt = $system_prompt . "\n\nQuestion: " . $user_message . "\n\nAnswer:";

// Optimized Ollama request with faster settings
$ollama_data = [
    'model' => $model,
    'prompt' => $full_prompt,
    'stream' => false,
    'options' => [
        'temperature' => 0.7,      // Balanced creativity
        'top_p' => 0.9,
        'top_k' => 40,             // Added for faster sampling
        'num_predict' => 250,      // REDUCED from 500 for faster response
        'num_ctx' => 1024,         // REDUCED context window for speed
        'repeat_penalty' => 1.1,
        'stop' => ['\n\n', 'Question:', 'User:'] // Stop tokens for efficiency
    ]
];

// Optimized CURL request with shorter timeout
$ch = curl_init($ollama_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ollama_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // REDUCED from 60 seconds
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Connection timeout

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Check for errors
if ($curl_error) {
    echo json_encode([
        'success' => false,
        'error' => 'Connection error: ' . $curl_error,
        'suggestion' => 'Make sure Ollama is running. Start it with: ollama serve'
    ]);
    exit();
}

if ($http_code !== 200) {
    echo json_encode([
        'success' => false,
        'error' => 'Ollama API error (HTTP ' . $http_code . ')',
        'suggestion' => 'Check if Ollama is running and the model is installed. Try: ollama pull tinyllama'
    ]);
    exit();
}

// Parse Ollama response
$ollama_response = json_decode($response, true);

if (!isset($ollama_response['response'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid response from Ollama',
        'raw_response' => $response
    ]);
    exit();
}

$bot_response = trim($ollama_response['response']);

// Save conversation to database (optional - async to not slow down response)
$student_id = $_SESSION['student_id'];
try {
    $stmt = $conn->prepare("INSERT INTO chat_history (student_id, user_message, bot_response) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $student_id, $user_message, $bot_response);
    $stmt->execute();
} catch (Exception $e) {
    // Log error but don't fail the request
    error_log("Failed to save chat history: " . $e->getMessage());
}

// Return success response
echo json_encode([
    'success' => true,
    'response' => $bot_response,
    'model' => $model,
    'timestamp' => time()
]);
?>