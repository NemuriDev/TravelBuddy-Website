<?php
require_once __DIR__ . '/config.php';
?>
<?php
$activePage = 'destination';
$loggedIn   = false; // set true (and $userName) once you wire up real sessions
?>
<?php
// Database connection
$db = getDBConnection();

// Check if user is logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    echo 'Welcome, ' . $user['name'];
}

// Create a CSRF token for forms
$csrf_token = generateCSRFToken();

// Build URLs
$link = url('destination.php');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="TravelBuddies — a visual field guide to places worth the detour in Bulacan." />
    <meta property="og:title" content="TravelBuddies — Bulacan Field Guide" />
    <meta property="og:description" content="A field guide for weekends with room to breathe." />
    <title>TravelBuddies — Bulacan Field Guide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <main class="page-shell">
        <?php include __DIR__ . '/navbar.php'; ?>

        <section class="hero-grid" aria-labelledby="hero-title">
            <div class="hero-copy reveal">
                <p class="section-kicker">A small province, a thousand reasons to wander</p>
                <h1 id="hero-title">Take the <em>long way</em> to Bulacan.</h1>
                <p class="hero-description">A field guide for weekends with room to breathe — stone churches, river
                    bends, old stories, and the roads between them.</p>
                <div class="hero-actions">
                    <a class="primary-button" href="#places">Start wandering <span aria-hidden="true">↗</span></a>
                    <span class="hero-count">40 places / 20 towns</span>
                </div>
            </div>
            <div class="hero-art reveal" style="--delay: .12s">
                <div class="hero-art-image image-frame">
                    <img id="hero-image"
                        src="https://thumb.wikimedia.org/wikipedia/commons/thumb/2/2c/Bulacan_Provincial_Capitol_Building%2C_July_2023.jpg/1280px-Bulacan_Provincial_Capitol_Building%2C_July_2023.jpg?utm_source=commons.wikimedia.org&utm_campaign=index&utm_content=thumbnail"
                        alt="Hero image" />
                    <span class="image-fallback" aria-hidden="true">◉</span>
                </div>
                <span class="hero-stamp">Issue 01<br />Field notes<br />from the north</span>
                <div class="hero-art-caption">
                    <p class="caption-kicker">Featured route / DRT</p>
                    <p>Where the clouds keep their secrets.</p>
                </div>
            </div>
        </section>

        <section class="places-section" id="places" aria-labelledby="places-title">
            <div class="section-heading">
                <div>
                    <p class="section-kicker">The index</p>
                    <h2 id="places-title">Places worth the detour.</h2>
                </div>
                <p>Start with a mood, a municipality, or the first road out of the city.</p>
            </div>

            <div class="filter-rail" aria-label="Destination filters">
                <div class="filter-row">
                    <label class="search-wrap">
                        <span class="search-icon" aria-hidden="true">⌕</span>
                        <input id="search-input" type="search" placeholder="Search a place, story, or town..."
                            autocomplete="off" />
                    </label>
                    <div class="select-wrap">
                        <span class="filter-icon" aria-hidden="true">☷</span>
                        <select id="municipality-select" aria-label="Filter by municipality"></select>
                        <span class="select-arrow" aria-hidden="true">⌄</span>
                    </div>
                    <div class="select-wrap mood-select">
                        <select id="category-select" aria-label="Filter by mood"></select>
                        <span class="select-arrow" aria-hidden="true">⌄</span>
                    </div>
                    <button class="filter-chip saved-filter" id="saved-filter" type="button"><span
                            aria-hidden="true">♡</span> Saved</button>
                    <button class="clear-button hidden" id="clear-filters" type="button">↻ Clear</button>
                </div>
                <div class="category-row" id="category-chips" aria-label="Mood filters"></div>
            </div>

            <div class="results-meta">
                <p id="results-count">40 entries / all municipalities</p>
                <p class="curious-label"><span aria-hidden="true">✦</span> Locally curious</p>
            </div>

            <div class="destination-grid" id="destination-grid"></div>
            <div class="empty-state hidden" id="empty-state">
                <div class="empty-icon" aria-hidden="true">▱</div>
                <h3>No notes in this margin.</h3>
                <p>Try another town or clear the filters. Bulacan has plenty more road left.</p>
                <button class="primary-button" id="empty-clear" type="button">Reset the index <span
                        aria-hidden="true">↻</span></button>
            </div>
        </section>

        <section class="about-section" id="about" aria-labelledby="about-title">
            <div class="about-intro">
                <p class="section-kicker">How to use this guide</p>
                <p id="about-title">Leave a little room for the places you did not plan for.</p>
            </div>
            <div class="about-steps">
                <div><span>01 /</span>
                    <p>Pick a town near your route. Then look at what it has been quietly keeping.</p>
                </div>
                <div><span>02 /</span>
                    <p>Save the places that feel like your kind of Saturday. Your shortlist stays in this browser.</p>
                </div>
                <div><span>03 /</span>
                    <p>Open a note for the small details: when to go, where it is, and what to bring.</p>
                </div>
            </div>
        </section>

        <?php include __DIR__ . '/footer.php'; ?>
    </main>

    <div class="modal-backdrop hidden" id="detail-modal" role="presentation">
        <div class="detail-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
            <div class="detail-image image-frame">
                <img id="modal-image"
                    src="https://thumb.wikimedia.org/wikipedia/commons/thumb/2/2c/Bulacan_Provincial_Capitol_Building%2C_July_2023.jpg/1280px-Bulacan_Provincial_Capitol_Building%2C_July_2023.jpg?utm_source=commons.wikimedia.org&utm_campaign=index&utm_content=thumbnail"
                    alt="" />
                <span class="image-fallback" aria-hidden="true">◉</span>
            </div>
            <div class="detail-content">
                <button class="close-button" id="close-modal" type="button"
                    aria-label="Close destination details">×</button>
                <p class="section-kicker" id="modal-kicker"></p>
                <h2 id="modal-title"></h2>
                <div class="modal-location"><span aria-hidden="true">⌖</span> <span id="modal-municipality"></span>
                </div>
                <p class="modal-description" id="modal-description"></p>
                <div class="modal-tags" id="modal-tags"></div>

                <div class="reviews-section">
                    <div class="reviews-header">
                        <h3>Community Reviews</h3>
                        <button class="primary-button" id="write-review-btn" type="button">+ Write a Review</button>
                    </div>

                    <div class="review-form hidden" id="review-form">
                        <p class="review-form-label">Your Rating</p>
                        <div class="star-input" id="star-input" role="radiogroup" aria-label="Your rating"></div>
                        <textarea id="review-text" placeholder="Share your experience..."></textarea>
                        <div class="review-form-actions">
                            <button class="secondary-button" id="cancel-review-btn" type="button">Cancel</button>
                            <button class="primary-button btn-terracotta" id="submit-review-btn" type="button">Submit Review</button>
                        </div>
                    </div>

                    <div class="review-list" id="review-list"></div>
                </div>

                <div class="modal-facts">
                    <div>
                        <p>Where to find it</p><strong id="modal-location"></strong>
                    </div>
                    <div>
                        <p>Field note</p><strong id="modal-time"></strong>
                    </div>
                </div>
                <div class="modal-actions">
                    <button class="primary-button" id="modal-favorite" type="button"></button>
                    <button class="secondary-button" id="modal-share" type="button"><span aria-hidden="true">⌁</span>
                        <span id="share-label">Share the note</span></button>
                </div>
                <p class="saved-note">A guide is only as good as the care you bring to the place. Check local access,
                    weather, and current opening hours before you go.</p>
            </div>
        </div>
    </div>

    <script src="script.js" defer></script>
</body>
</html>