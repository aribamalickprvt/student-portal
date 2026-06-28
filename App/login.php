<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['token']) && !empty($_SESSION['token'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        // Call API for login
        $api_url = 'http://localhost/student-portal/api/v1/auth?action=login';
        
        $data = json_encode([
            'email' => $email,
            'password' => $password
        ]);
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($http_code === 200 && $result['status'] === 'success') {
            // Store token and user info in session
            $_SESSION['token'] = $result['data']['token'];
            $_SESSION['student_id'] = $result['data']['user']['id'];
            $_SESSION['student_name'] = $result['data']['user']['name'];
            $_SESSION['student_email'] = $result['data']['user']['email'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = $result['message'] ?? "Invalid email or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Wellbeing Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>🎓 Student Wellbeing Portal</h1>
                <p>Your success is our priority</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="student@example.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="login-footer">
                <p>Demo Account: demo@student.com / student123</p>
                <p><a href="register.php">Don't have an account? Register here</a></p>
            </div>
        </div>
    </div>
</body>
</html>