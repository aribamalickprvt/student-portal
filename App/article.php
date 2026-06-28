<?php
session_start();
require_once 'includes/api_helper.php';
requireLogin();

$student = getCurrentStudent();
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($article_id == 0) {
    header("Location: articles.php");
    exit();
}

$result = fetchArticle($article_id);

if (!$result['success']) {
    header("Location: articles.php");
    exit();
}

$article = $result['data'];
$related = $article['related_articles'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Student Wellbeing Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="article-detail">
            <div class="article-header">
                <a href="articles.php" class="back-link">← Back to Articles</a>
                <span class="category-badge-lg"><?php echo htmlspecialchars($article['category_name']); ?></span>
                <h1><?php echo htmlspecialchars($article['title']); ?></h1>
                <div class="article-info">
                    <span>By <?php echo htmlspecialchars($article['author']); ?></span>
                    <span>•</span>
                    <span><?php echo date('F j, Y', strtotime($article['created_at'])); ?></span>
                    <span>•</span>
                    <span>👁️ <?php echo $article['views']; ?> views</span>
                </div>
            </div>
            
            <div class="article-content">
                <?php 
                $content = htmlspecialchars($article['content']);
                $paragraphs = explode("\n", $content);
                foreach ($paragraphs as $para) {
                    if (!empty(trim($para))) {
                        echo "<p>" . nl2br($para) . "</p>";
                    }
                }
                ?>
            </div>
            
            <div class="article-actions">
                <a href="feedback.php" class="btn btn-primary">Share Your Feedback</a>
                <a href="articles.php?category=<?php echo $article['category_id']; ?>" class="btn btn-secondary">More from this Category</a>
            </div>
        </div>
        
        <?php if (!empty($related)): ?>
            <div class="related-articles">
                <h2>Related Articles</h2>
                <div class="articles-grid">
                    <?php foreach ($related as $rel): ?>
                        <div class="article-card-small">
                            <h3><?php echo htmlspecialchars($rel['title']); ?></h3>
                            <a href="article.php?id=<?php echo $rel['id']; ?>" class="btn btn-secondary">Read More</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>