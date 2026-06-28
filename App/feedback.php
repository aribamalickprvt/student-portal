<?php
session_start();
require_once 'includes/api_helper.php';
requireLogin();

$student = getCurrentStudent();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    
    if (empty($subject) || empty($message)) {
        $error = "Please fill in all required fields";
    } elseif ($rating < 1 || $rating > 5) {
        $error = "Please select a rating";
    } else {
        $result = submitFeedback($subject, $message, $rating);
        
        if ($result['success']) {
            $success = "Thank you for your feedback! We appreciate your input.";
            $_POST = array();
        } else {
            $error = $result['message'] ?? "Failed to submit feedback. Please try again.";
        }
    }
}

// Get feedback history
$feedbackResult = fetchUserFeedback(5);
$user_feedback = $feedbackResult['data']['feedback'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Student Wellbeing Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>💬 Share Your Feedback</h1>
            <p>Your opinions help us improve the portal and better serve student needs</p>
        </div>
        
        <div class="feedback-layout">
            <div class="feedback-form-section">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" class="feedback-form">
                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <input type="text" id="subject" name="subject" required 
                               value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>"
                               placeholder="What is your feedback about?">
                    </div>
                    
                    <div class="form-group">
                        <label for="rating">Overall Rating *</label>
                        <div class="rating-input">
                            <input type="radio" id="star5" name="rating" value="5" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 5) ? 'checked' : ''; ?>>
                            <label for="star5" class="star">⭐</label>
                            
                            <input type="radio" id="star4" name="rating" value="4" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 4) ? 'checked' : ''; ?>>
                            <label for="star4" class="star">⭐</label>
                            
                            <input type="radio" id="star3" name="rating" value="3" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 3) ? 'checked' : ''; ?>>
                            <label for="star3" class="star">⭐</label>
                            
                            <input type="radio" id="star2" name="rating" value="2" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 2) ? 'checked' : ''; ?>>
                            <label for="star2" class="star">⭐</label>
                            
                            <input type="radio" id="star1" name="rating" value="1" <?php echo (isset($_POST['rating']) && $_POST['rating'] == 1) ? 'checked' : ''; ?>>
                            <label for="star1" class="star">⭐</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Feedback *</label>
                        <textarea id="message" name="message" rows="6" required 
                                  placeholder="Share your thoughts, suggestions, or concerns..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                </form>
            </div>
            
            <div class="feedback-info">
                <div class="info-card">
                    <h3>Why Your Feedback Matters</h3>
                    <ul>
                        <li>Help us improve content quality</li>
                        <li>Suggest new topics and features</li>
                        <li>Report technical issues</li>
                        <li>Share success stories</li>
                    </ul>
                </div>
                
                <?php if (!empty($user_feedback)): ?>
                    <div class="info-card">
                        <h3>Your Recent Feedback</h3>
                        <?php foreach ($user_feedback as $fb): ?>
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <strong><?php echo htmlspecialchars($fb['subject']); ?></strong>
                                    <span class="feedback-status status-<?php echo $fb['status']; ?>">
                                        <?php echo ucfirst($fb['status']); ?>
                                    </span>
                                </div>
                                <p><?php echo substr(htmlspecialchars($fb['message']), 0, 100) . '...'; ?></p>
                                <small><?php echo date('M j, Y', strtotime($fb['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>