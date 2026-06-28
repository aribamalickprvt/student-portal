<?php
session_start();
require_once 'includes/api_helper.php';
requireLogin();

$student = getCurrentStudent();
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

$result = fetchArticles($category_filter);
$articles = $result['data']['articles'] ?? [];

$categories_result = fetchCategories();
$categories = $categories_result['data']['categories'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles - Student Wellbeing Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>📚 Information Articles</h1>
            <p>Explore our collection of articles covering various aspects of student wellbeing and success</p>
        </div>
        
        <div class="filter-section">
            <h3>Filter by Category:</h3>
            <div class="category-filter">
                <a href="articles.php" class="filter-btn <?php echo $category_filter == 0 ? 'active' : ''; ?>">All</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="articles.php?category=<?php echo $cat['id']; ?>" 
                       class="filter-btn <?php echo $category_filter == $cat['id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="articles-grid">
            <?php if (!empty($articles)): ?>
                <?php foreach ($articles as $article): ?>
                    <div class="article-card">
                        <div class="article-meta">
                            <span class="category-badge"><?php echo htmlspecialchars($article['category_name']); ?></span>
                            <span class="article-views">👁️ <?php echo $article['views']; ?> views</span>
                        </div>
                        <h2><?php echo htmlspecialchars($article['title']); ?></h2>
                        <p class="article-excerpt"><?php echo substr(htmlspecialchars($article['content']), 0, 200) . '...'; ?></p>
                        <div class="article-footer">
                            <span class="article-author">By <?php echo htmlspecialchars($article['author']); ?></span>
                            <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">Read Article</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-content">
                    <p>No articles found in this category.</p>
                    <a href="articles.php" class="btn btn-secondary">View All Articles</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>