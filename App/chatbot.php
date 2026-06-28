<?php
require_once 'config.php';
require_login();

$student = get_current_student($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant - Student Wellbeing Portal</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/chatbot.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="chatbot-page-header">
            <h1>🤖 AI Student Assistant</h1>
            <p>Ask me anything about student wellbeing, study tips, career advice, or technology topics!</p>
        </div>

        <div class="chatbot-layout">
            <!-- Sidebar with suggestions -->
            <div class="chatbot-sidebar">
                <div class="sidebar-section">
                    <h3>💡 Quick Questions</h3>
                    <button class="suggestion-btn" onclick="sendSuggestion('How can I manage exam stress?')">
                        How can I manage exam stress?
                    </button>
                    <button class="suggestion-btn" onclick="sendSuggestion('Give me study tips for better focus')">
                        Give me study tips for better focus
                    </button>
                    <button class="suggestion-btn" onclick="sendSuggestion('How do I stay motivated?')">
                        How do I stay motivated?
                    </button>
                    <button class="suggestion-btn" onclick="sendSuggestion('What are good career paths in tech?')">
                        What are good career paths in tech?
                    </button>
                    <button class="suggestion-btn" onclick="sendSuggestion('Explain machine learning basics')">
                        Explain machine learning basics
                    </button>
                </div>

                <div class="sidebar-section">
                    <h3>ℹ️ About Assistant</h3>
                    <ul class="info-list">
                        <li>Powered by Ollama AI</li>
                        <li>Trained on wellbeing resources</li>
                        <li>Available 24/7</li>
                        <li>Confidential conversations</li>
                    </ul>
                </div>

                <div class="sidebar-section">
                    <h3>⚠️ Important Note</h3>
                    <p class="warning-text">
                        This AI assistant provides general guidance. For serious mental health concerns, please contact professional counseling services.
                    </p>
                </div>
            </div>

            <!-- Main chat area -->
            <div class="chatbot-main">
                <div class="chat-container">
                    <div id="chatMessages" class="chat-messages">
                        <div class="message bot-message">
                            <div class="message-avatar">🤖</div>
                            <div class="message-content">
                                <p>Hello <?php echo htmlspecialchars($student['name']); ?>! 👋</p>
                                <p>I'm your AI Student Assistant. I'm here to help you with:</p>
                                <ul>
                                    <li>Mental health and stress management</li>
                                    <li>Study techniques and academic tips</li>
                                    <li>Career guidance and development</li>
                                    <li>Technology topics (AI, ML, Web Dev, Data Science)</li>
                                    <li>General student wellbeing advice</li>
                                </ul>
                                <p>What would you like to know today?</p>
                            </div>
                        </div>
                    </div>

                    <div class="chat-input-area">
                        <form id="chatForm" class="chat-form">
                            <textarea 
                                id="userMessage" 
                                placeholder="Type your message here..." 
                                rows="3"
                                required></textarea>
                            <div class="chat-actions">
                                <button type="button" id="clearBtn" class="btn btn-secondary">Clear Chat</button>
                                <button type="submit" id="sendBtn" class="btn btn-primary">
                                    <span id="sendBtnText">Send</span>
                                    <span id="sendBtnLoader" style="display: none;">⏳ Thinking...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="js/chatbot.js"></script>
</body>
</html>