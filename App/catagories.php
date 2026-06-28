<?php
session_start();
require_once 'includes/api_helper.php';
requireLogin();

$student = getCurrentStudent();

$result = fetchCategories();
$categories = $result['data']['categories'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Student Wellbeing Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>🗂️ Content Categories</h1>
            <p>Browse our content organized by topic areas</p>
        </div>
        
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <div class="category-card">
                    <div class="category-icon"><?php echo $category['icon']; ?></div>
                    <h2><?php echo htmlspecialchars($category['name']); ?></h2>
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                    <div class="category-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $category['article_count']; ?></span>
                            <span class="stat-label">Articles</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $category['tip_count']; ?></span>
                            <span class="stat-label">Tips</span>
                        </div>
                    </div>
                    <div class="category-actions">
                        <a href="articles.php?category=<?php echo $category['id']; ?>" class="btn btn-secondary">View Articles</a>
                        <a href="tips.php?category=<?php echo $category['id']; ?>" class="btn btn-secondary">View Tips</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>