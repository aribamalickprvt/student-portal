<?php
if (!isset($student)) {
    $student = getCurrentStudent();
}
?>
<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="dashboard.php">🎓 Student Wellbeing Portal</a>
        </div>
        <nav class="main-nav">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="articles.php" class="nav-link">Articles</a>
            <a href="tips.php" class="nav-link">Success Tips</a>
            <a href="categories.php" class="nav-link">Categories</a>
            <a href="feedback.php" class="nav-link">Feedback</a>
            <a href="search.php" class="nav-link">Search</a>
            <a href="chatbot.php" class="nav-link">🤖 AI Assistant</a>
        </nav>
        <div class="user-menu">
            <div class="user-info">
                <span class="user-icon">👤</span>
                <span class="user-name"><?php echo htmlspecialchars($student['name']); ?></span>
            </div>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
    </div>
</header>