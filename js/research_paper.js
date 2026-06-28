// research_papers.js - Research Paper Search Functionality

const searchQuery = document.getElementById("searchQuery");
const searchBtn = document.getElementById("searchBtn");
const searchBtnText = document.getElementById("searchBtnText");
const searchBtnLoader = document.getElementById("searchBtnLoader");
const resultsSection = document.getElementById("resultsSection");
const resultsContainer = document.getElementById("resultsContainer");
const resultsCount = document.getElementById("resultsCount");
const emptyState = document.getElementById("emptyState");
const savedPapers = document.getElementById("savedPapers");
const savedCount = document.getElementById("savedCount");

let currentResults = [];
let savedPapersList = [];
let currentPage = 0;

// Load saved papers on init
document.addEventListener("DOMContentLoaded", function () {
  loadSavedPapers();

  // Enter key to search
  searchQuery.addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      performSearch();
    }
  });

  // Toggle filters
  document
    .getElementById("toggleFilters")
    .addEventListener("click", function () {
      const panel = document.getElementById("filterPanel");
      panel.style.display = panel.style.display === "none" ? "block" : "none";
    });
});

// Search button click
searchBtn.addEventListener("click", performSearch);

// Quick search function
function quickSearch(query) {
  searchQuery.value = query;
  performSearch();
}

// Main search function
async function performSearch() {
  const query = searchQuery.value.trim();

  if (!query) {
    alert("Please enter a search term");
    return;
  }

  // Get filters
  const yearFrom = document.getElementById("yearFrom").value;
  const yearTo = document.getElementById("yearTo").value;
  const field = document.getElementById("fieldFilter").value;
  const sortBy = document.getElementById("sortBy").value;

  // Show loading
  toggleSearchButton(false);
  emptyState.style.display = "none";
  resultsSection.style.display = "block";
  resultsContainer.innerHTML =
    '<div class="loading">🔍 Searching across multiple sources...</div>';

  try {
    // Call backend API
    const response = await fetch("search_papers_api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        query: query,
        yearFrom: yearFrom,
        yearTo: yearTo,
        field: field,
        sortBy: sortBy,
        page: 0,
      }),
    });

    const data = await response.json();

    if (data.success) {
      currentResults = data.papers;
      displayResults(data.papers);
      resultsCount.textContent = `Found ${data.total} papers`;
    } else {
      throw new Error(data.error || "Search failed");
    }
  } catch (error) {
    console.error("Search error:", error);
    resultsContainer.innerHTML = `
            <div class="error-message">
                <p>❌ Search failed: ${error.message}</p>
                <p>Please try again or contact support.</p>
            </div>
        `;
  } finally {
    toggleSearchButton(true);
  }
}

// Display search results
function displayResults(papers) {
  if (papers.length === 0) {
    resultsContainer.innerHTML = `
            <div class="no-results">
                <h3>No papers found</h3>
                <p>Try different keywords or adjust your filters</p>
            </div>
        `;
    return;
  }

  resultsContainer.innerHTML = "";

  papers.forEach((paper) => {
    const card = createPaperCard(paper);
    resultsContainer.appendChild(card);
  });
}

// Create paper card
function createPaperCard(paper) {
  const card = document.createElement("div");
  card.className = "paper-card";

  // Determine access type
  let accessBadge = "";
  if (paper.openAccess) {
    accessBadge =
      '<span class="access-badge access-open">🟢 Open Access</span>';
  } else if (paper.pdfUrl) {
    accessBadge =
      '<span class="access-badge access-limited">🟡 PDF Available</span>';
  } else {
    accessBadge =
      '<span class="access-badge access-paywalled">🔴 Limited Access</span>';
  }

  // Authors
  const authors = paper.authors
    ? paper.authors.slice(0, 3).join(", ") +
      (paper.authors.length > 3 ? " et al." : "")
    : "Unknown";

  // Abstract
  const abstract = paper.abstract
    ? paper.abstract.substring(0, 300) +
      (paper.abstract.length > 300 ? "..." : "")
    : "No abstract available";

  card.innerHTML = `
        <div class="paper-header">
            <h3 class="paper-title" onclick="openPaper('${escapeHtml(paper.url)}')">${escapeHtml(paper.title)}</h3>
            <div class="paper-authors">${escapeHtml(authors)}</div>
            <div class="paper-meta">
                <span>📅 ${paper.year || "N/A"}</span>
                <span>📖 ${paper.venue || "Unknown Venue"}</span>
                <span>📊 ${paper.citations || 0} citations</span>
                ${accessBadge}
            </div>
        </div>
        
        <div class="paper-abstract">${escapeHtml(abstract)}</div>
        
        ${
          paper.keywords
            ? `
            <div class="paper-tags">
                ${paper.keywords
                  .slice(0, 5)
                  .map((k) => `<span class="tag">${escapeHtml(k)}</span>`)
                  .join("")}
            </div>
        `
            : ""
        }
        
        <div class="paper-actions">
            ${paper.pdfUrl ? `<button class="btn-small btn-download" onclick="downloadPaper('${escapeHtml(paper.pdfUrl)}', '${escapeHtml(paper.title)}')">📥 Download PDF</button>` : ""}
            ${paper.arxivId ? `<button class="btn-small btn-download" onclick="downloadFromArxiv('${paper.arxivId}')">📥 arXiv PDF</button>` : ""}
            <button class="btn-small btn-view" onclick="openPaper('${escapeHtml(paper.url)}')">👁️ View Details</button>
            <button class="btn-small btn-save" onclick="savePaper(${escapeHtml(JSON.stringify(paper))})">💾 Save</button>
            <button class="btn-small btn-cite" onclick="showCitation(${escapeHtml(JSON.stringify(paper))})">📝 Cite</button>
            ${!paper.pdfUrl && !paper.openAccess ? `<button class="btn-small btn-view" onclick="findAlternatives('${escapeHtml(paper.title)}', '${escapeHtml(paper.doi || "")}')">🔍 Find PDF</button>` : ""}
        </div>
    `;

  return card;
}

// Download paper
function downloadPaper(url, title) {
  const a = document.createElement("a");
  a.href = url;
  a.download = sanitizeFilename(title) + ".pdf";
  a.target = "_blank";
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}

// Download from arXiv
function downloadFromArxiv(arxivId) {
  const url = `https://arxiv.org/pdf/${arxivId}.pdf`;
  window.open(url, "_blank");
}

// Open paper in new tab
function openPaper(url) {
  window.open(url, "_blank");
}

// Find alternative download sources
function findAlternatives(title, doi) {
  let searchUrls = [];

  // Sci-Hub (use with caution - legal gray area)
  if (doi) {
    searchUrls.push(`https://sci-hub.se/${doi}`);
  }

  // Google Scholar
  searchUrls.push(
    `https://scholar.google.com/scholar?q=${encodeURIComponent(title)}`,
  );

  // ResearchGate
  searchUrls.push(
    `https://www.researchgate.net/search/publication?q=${encodeURIComponent(title)}`,
  );

  // Show modal with options
  showAlternativesModal(title, searchUrls);
}

// Show alternatives modal
function showAlternativesModal(title, urls) {
  const modal = document.createElement("div");
  modal.style.cssText =
    "position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;";

  modal.innerHTML = `
        <div style="background: white; padding: 2rem; border-radius: 12px; max-width: 500px; width: 90%;">
            <h3>Alternative Sources for: ${escapeHtml(title.substring(0, 50))}...</h3>
            <p>Try these sources to find the PDF:</p>
            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin: 1rem 0;">
                <a href="${urls[0]}" target="_blank" class="btn-small btn-view" style="display: block; text-align: center; text-decoration: none;">🔓 Sci-Hub</a>
                <a href="${urls[1]}" target="_blank" class="btn-small btn-view" style="display: block; text-align: center; text-decoration: none;">🎓 Google Scholar</a>
                <a href="${urls[2]}" target="_blank" class="btn-small btn-view" style="display: block; text-align: center; text-decoration: none;">🔬 ResearchGate</a>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="btn-small btn-secondary" style="width: 100%;">Close</button>
        </div>
    `;

  document.body.appendChild(modal);
  modal.addEventListener("click", (e) => {
    if (e.target === modal) modal.remove();
  });
}

// Save paper to library
function savePaper(paper) {
  // Check if already saved
  if (savedPapersList.some((p) => p.title === paper.title)) {
    alert("Paper already saved!");
    return;
  }

  savedPapersList.push(paper);
  localStorage.setItem("savedPapers", JSON.stringify(savedPapersList));
  updateSavedPapers();
  alert("✅ Paper saved to your library!");
}

// Show citation
function showCitation(paper) {
  const authors = paper.authors ? paper.authors.join(", ") : "Unknown";
  const year = paper.year || "n.d.";

  const citations = {
    APA: `${authors} (${year}). ${paper.title}. ${paper.venue || "Journal"}. ${paper.doi ? "https://doi.org/" + paper.doi : ""}`,
    MLA: `${authors}. "${paper.title}." ${paper.venue || "Journal"}, ${year}.`,
    Chicago: `${authors}. "${paper.title}." ${paper.venue || "Journal"} (${year}).`,
    BibTeX: `@article{paper${year},\n  title={${paper.title}},\n  author={${authors}},\n  journal={${paper.venue || "Journal"}},\n  year={${year}}\n}`,
  };

  const modal = document.createElement("div");
  modal.style.cssText =
    "position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;";

  modal.innerHTML = `
        <div style="background: white; padding: 2rem; border-radius: 12px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
            <h3>📝 Citations</h3>
            ${Object.entries(citations)
              .map(
                ([style, cite]) => `
                <div style="margin: 1rem 0;">
                    <strong>${style}:</strong>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-top: 0.5rem; font-size: 0.9rem; word-wrap: break-word;">
                        ${escapeHtml(cite)}
                    </div>
                    <button onclick="navigator.clipboard.writeText('${cite.replace(/'/g, "\\'")}'); alert('Copied!')" class="btn-small btn-secondary" style="margin-top: 0.5rem;">📋 Copy</button>
                </div>
            `,
              )
              .join("")}
            <button onclick="this.parentElement.parentElement.remove()" class="btn-small btn-primary" style="width: 100%; margin-top: 1rem;">Close</button>
        </div>
    `;

  document.body.appendChild(modal);
}

// Load saved papers
function loadSavedPapers() {
  const saved = localStorage.getItem("savedPapers");
  if (saved) {
    savedPapersList = JSON.parse(saved);
    updateSavedPapers();
  }
}

// Update saved papers display
function updateSavedPapers() {
  savedCount.textContent = savedPapersList.length;

  if (savedPapersList.length === 0) {
    savedPapers.innerHTML =
      '<p class="no-saved">No saved papers yet. Search and save papers to access them later!</p>';
    return;
  }

  savedPapers.innerHTML = "";
  savedPapersList.forEach((paper, index) => {
    const card = document.createElement("div");
    card.className = "paper-card";
    card.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <h4 class="paper-title" onclick="openPaper('${escapeHtml(paper.url)}')">${escapeHtml(paper.title)}</h4>
                    <p style="color: #6c757d; font-size: 0.9rem;">${paper.authors ? paper.authors.slice(0, 2).join(", ") : "Unknown"}</p>
                </div>
                <button onclick="removeSavedPaper(${index})" class="btn-small" style="background: #dc3545; color: white;">🗑️</button>
            </div>
        `;
    savedPapers.appendChild(card);
  });
}

// Remove saved paper
function removeSavedPaper(index) {
  if (confirm("Remove this paper from your library?")) {
    savedPapersList.splice(index, 1);
    localStorage.setItem("savedPapers", JSON.stringify(savedPapersList));
    updateSavedPapers();
  }
}

// Helper functions
function toggleSearchButton(enabled) {
  searchBtn.disabled = !enabled;
  if (enabled) {
    searchBtnText.style.display = "inline";
    searchBtnLoader.style.display = "none";
  } else {
    searchBtnText.style.display = "none";
    searchBtnLoader.style.display = "inline";
  }
}

function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text.toString();
  return div.innerHTML;
}

function sanitizeFilename(filename) {
  return filename.replace(/[^a-z0-9]/gi, "_").substring(0, 100);
}
