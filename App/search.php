<?php
require_once 'config.php';
require_login();

$student = get_current_student($conn);

$search_query = isset($_GET['q']) ? clean_input($_GET['q']) : '';
$search_type = isset($_GET['type']) ? clean_input($_GET['type']) : 'all';

$articles = [];
$tips = [];
$categories = [];

if (!empty($search_query)) {
    $search_term = "%{$search_query}%";
    
    // Search Articles
    if ($search_type == 'all' || $search_type == 'articles') {
        $stmt = $conn->prepare("
            SELECT a.*, c.name as category_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            WHERE a.title LIKE ? OR a.content LIKE ? OR a.author LIKE ?
            ORDER BY a.created_at DESC
        ");
        $stmt->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt->execute();
        $articles = $stmt->get_result();
    }
    
    // Search Tips
    if ($search_type == 'all' || $search_type == 'tips') {
        $stmt = $conn->prepare("
            SELECT t.*, c.name as category_name 
            FROM success_tips t 
            LEFT JOIN categories c ON t.category_id = c.id 
            WHERE t.title LIKE ? OR t.tip_content LIKE ?
            ORDER BY t.priority DESC, t.created_at DESC
        ");
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $tips = $stmt->get_result();
    }
    
    // Search Categories
    if ($search_type == 'all' || $search_type == 'categories') {
        $stmt = $conn->prepare("
            SELECT * FROM categories 
            WHERE name LIKE ? OR description LIKE ?
            ORDER BY name
        ");
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $categories = $stmt->get_result();
    }
}

$total_results = 0;
if (is_object($articles)) $total_results += $articles->num_rows;
if (is_object($tips)) $total_results += $tips->num_rows;
if (is_object($categories)) $total_results += $categories->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Student Wellbeing Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="search-page-header">
            <h1>🔍 Search Results</h1>
            
            <!-- Search Form -->
            <form method="GET" action="search.php" class="search-form-main">
                <div class="search-input-wrapper">
                    <input type="text" name="q" placeholder="Search articles, tips, categories..." 
                           value="<?php echo htmlspecialchars($search_query); ?>" required>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                
                <div class="search-filters">
                    <label>
                        <input type="radio" name="type" value="all" <?php echo $search_type == 'all' ? 'checked' : ''; ?>>
                        <span>All Content</span>
                    </label>
                    <label>
                        <input type="radio" name="type" value="articles" <?php echo $search_type == 'articles' ? 'checked' : ''; ?>>
                        <span>Articles Only</span>
                    </label>
                    <label>
                        <input type="radio" name="type" value="tips" <?php echo $search_type == 'tips' ? 'checked' : ''; ?>>
                        <span>Tips Only</span>
                    </label>
                    <label>
                        <input type="radio" name="type" value="categories" <?php echo $search_type == 'categories' ? 'checked' : ''; ?>>
                        <span>Categories Only</span>
                    </label>
                </div>
            </form>
            
            <?php if (!empty($search_query)): ?>
                <div class="search-info">
                    <p>Found <strong><?php echo $total_results; ?></strong> results for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($search_query)): ?>
            <div class="search-empty">
                <div class="empty-icon">🔍</div>
                <h2>Start Your Search</h2>
                <p>Enter keywords to search for articles, tips, or categories that can help you succeed.</p>
                <div class="search-suggestions">
                    <h3>Popular Topics:</h3>
                    <div class="suggestion-tags">
                        <a href="search.php?q=stress" class="suggestion-tag">Stress Management</a>
                        <a href="search.php?q=study" class="suggestion-tag">Study Tips</a>
                        <a href="search.php?q=mental" class="suggestion-tag">Mental Health</a>
                        <a href="search.php?q=career" class="suggestion-tag">Career Development</a>
                        <a href="search.php?q=wellness" class="suggestion-tag">Wellness</a>
                    </div>
                </div>
            </div>
        <?php elseif ($total_results == 0): ?>
            <div class="search-empty">
                <div class="empty-icon">😕</div>
                <h2>No Results Found</h2>
                <p>We couldn't find any content matching "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
                <div class="search-suggestions">
                    <h3>Try:</h3>
                    <ul>
                        <li>Using different keywords</li>
                        <li>Checking your spelling</li>
                        <li>Using more general terms</li>
                        <li>Browsing our <a href="categories.php">categories</a></li>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            
            <!-- Articles Results -->
            <?php if (is_object($articles) && $articles->num_rows > 0): ?>
                <div class="search-section">
                    <h2>📚 Articles (<?php echo $articles->num_rows; ?>)</h2>
                    <div class="search-results-grid">
                        <?php while ($article = $articles->fetch_assoc()): ?>
                            <div class="search-result-card">
                                <div class="result-type-badge">Article</div>
                                <span class="category-badge"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                                <p><?php echo substr(htmlspecialchars($article['content']), 0, 150) . '...'; ?></p>
                                <div class="result-meta">
                                    <span>By <?php echo htmlspecialchars($article['author']); ?></span>
                                    <span>•</span>
                                    <span>👁️ <?php echo $article['views']; ?> views</span>
                                </div>
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-secondary">Read Article</a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Tips Results -->
            <?php if (is_object($tips) && $tips->num_rows > 0): ?>
                <div class="search-section">
                    <h2>💡 Success Tips (<?php echo $tips->num_rows; ?>)</h2>
                    <div class="search-results-grid">
                        <?php while ($tip = $tips->fetch_assoc()): ?>
                            <div class="search-result-card tip-result">
                                <div class="result-type-badge tip-badge">Tip</div>
                                <span class="category-badge"><?php echo htmlspecialchars($tip['category_name']); ?></span>
                                <?php 
                                $priority = $tip['priority'];
                                if ($priority >= 5) {
                                    echo '<span class="priority-badge high">High Priority</span>';
                                } elseif ($priority >= 3) {
                                    echo '<span class="priority-badge medium">Medium Priority</span>';
                                } else {
                                    echo '<span class="priority-badge low">Low Priority</span>';
                                }
                                ?>
                                <h3><?php echo htmlspecialchars($tip['title']); ?></h3>
                                <p><?php echo htmlspecialchars($tip['tip_content']); ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Categories Results -->
            <?php if (is_object($categories) && $categories->num_rows > 0): ?>
                <div class="search-section">
                    <h2>🗂️ Categories (<?php echo $categories->num_rows; ?>)</h2>
                    <div class="search-results-grid">
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <div class="search-result-card category-result">
                                <div class="result-type-badge category-badge-type">Category</div>
                                <div class="category-icon-large"><?php echo $category['icon']; ?></div>
                                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                                <p><?php echo htmlspecialchars($category['description']); ?></p>
                                <div class="result-actions">
                                    <a href="articles.php?category=<?php echo $category['id']; ?>" class="btn btn-secondary">View Articles</a>
                                    <a href="tips.php?category=<?php echo $category['id']; ?>" class="btn btn-secondary">View Tips</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>