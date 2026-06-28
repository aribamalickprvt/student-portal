<?php
session_start();
require_once 'includes/api_helper.php';
requireLogin();

$student = getCurrentStudent();
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

$result = fetchTips($category_filter);
$tips = $result['data']['tips'] ?? [];

$categories_result = fetchCategories();
$categories = $categories_result['data']['categories'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success Tips - Student Wellbeing Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>💡 Success Tips</h1>
            <p>Practical, actionable tips to help you succeed in your academic journey</p>
        </div>
        
        <div class="filter-section">
            <h3>Filter by Category:</h3>
            <div class="category-filter">
                <a href="tips.php" class="filter-btn <?php echo $category_filter == 0 ? 'active' : ''; ?>">All</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="tips.php?category=<?php echo $cat['id']; ?>" 
                       class="filter-btn <?php echo $category_filter == $cat['id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="tips-grid">
            <?php if (!empty($tips)): ?>
                <?php foreach ($tips as $tip): ?>
                    <div class="tip-card-large">
                        <div class="tip-priority">
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
                        </div>
                        <span class="category-badge"><?php echo htmlspecialchars($tip['category_name']); ?></span>
                        <h3><?php echo htmlspecialchars($tip['title']); ?></h3>
                        <p><?php echo htmlspecialchars($tip['tip_content']); ?></p>
                        <div class="tip-footer">
                            <span class="tip-date">Added <?php echo date('M j, Y', strtotime($tip['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-content">
                    <p>No tips found in this category.</p>
                    <a href="tips.php" class="btn btn-secondary">View All Tips</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>