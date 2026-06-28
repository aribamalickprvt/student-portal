<?php
require_once 'includes/api_helper.php';
require_login();

$student = get_current_student($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Paper Search - Student Portal</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/research_papers.css">
</head>
<body>
    <?php include 'reusable/header.php'; ?>
    
    <div class="container">
        <div class="research-header">
            <h1>📚 Research Paper Search</h1>
            <p>Find academic papers from multiple open-access sources</p>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <div class="search-box">
                <input 
                    type="text" 
                    id="searchQuery" 
                    placeholder="Enter keywords, paper title, author, or DOI..."
                    autocomplete="off"
                />
                <button id="searchBtn" class="btn btn-primary">
                    <span id="searchBtnText">🔍 Search Papers</span>
                    <span id="searchBtnLoader" style="display: none;">⏳ Searching...</span>
                </button>
            </div>

            <!-- Advanced Filters -->
            <div class="advanced-filters">
                <button id="toggleFilters" class="btn-link">⚙️ Advanced Filters</button>
                <div id="filterPanel" style="display: none;">
                    <div class="filter-grid">
                        <div class="filter-item">
                            <label>Year Range:</label>
                            <div class="year-range">
                                <input type="number" id="yearFrom" placeholder="From" min="1900" max="2026" />
                                <span>to</span>
                                <input type="number" id="yearTo" placeholder="To" min="1900" max="2026" />
                            </div>
                        </div>
                        <div class="filter-item">
                            <label>Field of Study:</label>
                            <select id="fieldFilter">
                                <option value="">All Fields</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="Physics">Physics</option>
                                <option value="Medicine">Medicine</option>
                                <option value="Biology">Biology</option>
                                <option value="Chemistry">Chemistry</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label>Sort By:</label>
                            <select id="sortBy">
                                <option value="relevance">Relevance</option>
                                <option value="year">Year (Newest First)</option>
                                <option value="citations">Most Cited</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Suggestions -->
            <div class="quick-suggestions">
                <span class="suggestion-label">Popular searches:</span>
                <button class="suggestion-tag" onclick="quickSearch('machine learning')">Machine Learning</button>
                <button class="suggestion-tag" onclick="quickSearch('deep learning')">Deep Learning</button>
                <button class="suggestion-tag" onclick="quickSearch('artificial intelligence')">AI</button>
                <button class="suggestion-tag" onclick="quickSearch('data science')">Data Science</button>
                <button class="suggestion-tag" onclick="quickSearch('cybersecurity')">Cybersecurity</button>
            </div>
        </div>

        <!-- Data Sources Info -->
        <div class="sources-info">
            <h3>📖 Searching Across Multiple Sources:</h3>
            <div class="sources-grid">
                <div class="source-card">
                    <div class="source-icon">🟢</div>
                    <div class="source-name">arXiv</div>
                    <div class="source-desc">Open-access preprints</div>
                </div>
                <div class="source-card">
                    <div class="source-icon">🟢</div>
                    <div class="source-name">Semantic Scholar</div>
                    <div class="source-desc">AI-powered search</div>
                </div>
                <div class="source-card">
                    <div class="source-icon">🟢</div>
                    <div class="source-name">CORE</div>
                    <div class="source-desc">Open access papers</div>
                </div>
                <div class="source-card">
                    <div class="source-icon">🟢</div>
                    <div class="source-name">PubMed</div>
                    <div class="source-desc">Medical research</div>
                </div>
                <div class="source-card">
                    <div class="source-icon">🟡</div>
                    <div class="source-name">Sci-Hub</div>
                    <div class="source-desc">Alternative access</div>
                </div>
                <div class="source-card">
                    <div class="source-icon">🟢</div>
                    <div class="source-name">Google Scholar</div>
                    <div class="source-desc">Comprehensive search</div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" style="display: none;">
            <div class="results-header">
                <h2>Search Results</h2>
                <span id="resultsCount" class="results-count"></span>
            </div>
            <div id="resultsContainer" class="results-container"></div>
            <div id="loadMoreBtn" class="load-more" style="display: none;">
                <button class="btn btn-secondary" onclick="loadMoreResults()">Load More Results</button>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="empty-state">
            <div class="empty-icon">🔬</div>
            <h3>Start Your Research</h3>
            <p>Search for research papers from multiple open-access sources</p>
            <ul class="tips-list">
                <li>✓ Access papers that may not be available on IEEE</li>
                <li>✓ Search across arXiv, Semantic Scholar, PubMed, and more</li>
                <li>✓ Find alternative download links and preprint versions</li>
                <li>✓ Save papers to your personal library</li>
            </ul>
        </div>

        <!-- My Saved Papers -->
        <div class="saved-papers-section">
            <h3>💾 My Saved Papers (<span id="savedCount">0</span>)</h3>
            <div id="savedPapers" class="saved-papers-list">
                <p class="no-saved">No saved papers yet. Search and save papers to access them later!</p>
            </div>
        </div>
    </div>

    <?php include 'reusable/footer.php'; ?>

    <script src="js/research_papers.js"></script>
</body>
</html>