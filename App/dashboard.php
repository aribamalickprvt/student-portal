<?php
session_start();
require_once 'includes/api_helper.php';

requireLogin();
$student = getCurrentStudent();

// Fetch data from API
$stats = getStatistics();
$recentArticlesResult = fetchArticles(null, 3, 0);
$randomTipsResult = fetchTips(null, 4, true);

$recent_articles = $recentArticlesResult['data']['articles'] ?? [];
$random_tips = $randomTipsResult['data']['tips'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Wellbeing Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="welcome-banner">
            <h1>Welcome back, <?php echo htmlspecialchars($student['name']); ?>! 👋</h1>
            <p>Your wellbeing and success matter to us. Explore resources designed to support your journey.</p>
            
            <!-- Search Bar -->
            <div class="search-bar-container">
                <form method="GET" action="search.php" class="search-bar-form">
                    <div class="search-bar-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="q" placeholder="Search for articles, tips, or categories..." required>
                        <button type="submit" class="btn btn-search">Search</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-content">
                    <h3><?php echo $stats['article_count']; ?></h3>
                    <p>Information Articles</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💡</div>
                <div class="stat-content">
                    <h3><?php echo $stats['tip_count']; ?></h3>
                    <p>Success Tips</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🗂️</div>
                <div class="stat-content">
                    <h3><?php echo $stats['category_count']; ?></h3>
                    <p>Categories</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💬</div>
                <div class="stat-content">
                    <h3>24/7</h3>
                    <p>Support Available</p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="dashboard-section">
                <h2>📰 Recent Articles</h2>
                <div class="article-list">
                    <?php if (!empty($recent_articles)): ?>
                        <?php foreach ($recent_articles as $article): ?>
                            <div class="article-preview">
                                <div class="article-badge"><?php echo htmlspecialchars($article['category_name']); ?></div>
                                <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                                <p><?php echo substr(htmlspecialchars($article['content']), 0, 150) . '...'; ?></p>
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-secondary">Read More</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No articles available.</p>
                    <?php endif; ?>
                </div>
                <a href="articles.php" class="btn btn-primary" style="margin-top: 20px;">View All Articles</a>
            </div>
            
            <div class="dashboard-section">
                <h2>💡 Success Tips</h2>
                <div class="tips-list">
                    <?php if (!empty($random_tips)): ?>
                        <?php foreach ($random_tips as $tip): ?>
                            <div class="tip-card">
                                <div class="tip-header">
                                    <span class="tip-badge"><?php echo htmlspecialchars($tip['category_name']); ?></span>
                                </div>
                                <h4><?php echo htmlspecialchars($tip['title']); ?></h4>
                                <p><?php echo htmlspecialchars($tip['tip_content']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No tips available.</p>
                    <?php endif; ?>
                </div>
                <a href="tips.php" class="btn btn-primary" style="margin-top: 20px;">View All Tips</a>
            </div>
        </div>
        
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-grid">
                <a href="chatbot.php" class="action-card chatbot-card">
                    <div class="action-icon">🤖</div>
                    <h3>AI Assistant</h3>
                    <p>Chat with AI for instant help</p>
                </a>
                
                <a href="articles.php" class="action-card">
                    <div class="action-icon">📖</div>
                    <h3>Browse Articles</h3>
                    <p>Read informational content</p>
                </a>
                
                <a href="categories.php" class="action-card">
                    <div class="action-icon">🗂️</div>
                    <h3>Explore Categories</h3>
                    <p>Find content by topic</p>
                </a>
                
                <a href="tips.php" class="action-card">
                    <div class="action-icon">⭐</div>
                    <h3>Success Tips</h3>
                    <p>Get actionable advice</p>
                </a>
                
                <a href="feedback.php" class="action-card">
                    <div class="action-icon">💬</div>
                    <h3>Give Feedback</h3>
                    <p>Share your thoughts</p>
                </a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>