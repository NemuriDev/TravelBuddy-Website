<?php
require_once __DIR__ . '/config.php';
?>
<?php
$activePage = 'home';
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
    <meta property="og:description" content="Forty places, twenty towns, one province worth the long way round." />
    <title>TravelBuddies — Home</title>
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
                <p class="section-kicker">Province of Bulacan</p>
                <h1 id="hero-title">Where history meets <em>living nature</em>.</h1>
                <p class="hero-description">Forty places across twenty towns — stone churches, river valleys,
                    hidden falls, and a festival that lights up the water every July. Start wherever your
                    Saturday takes you.</p>
                <div class="hero-actions">
                    <a class="primary-button" href="destination.php#places">Explore the guide <span aria-hidden="true">↗</span></a>
                    <a class="secondary-button" href="LogIn.php">Create a free account</a>
                </div>
            </div>
            <div class="hero-art reveal" style="--delay: .12s">
                <div class="hero-art-image image-frame">
                    <img id="hero-image"
                        src="https://as2.ftcdn.net/jpg/02/66/96/15/1000_F_266961583_7vsjOxLaD9wre1dbEsmZYX4YktptOo2M.jpg"
                        alt="Barasoain Church in Malolos" />
                    <span class="image-fallback" aria-hidden="true">◉</span>
                </div>
                <span class="hero-stamp">Cradle of<br />Philippine<br />democracy</span>
                <div class="hero-art-caption">
                    <p class="caption-kicker">Featured landmark</p>
                    <p>Barasoain Church, Malolos.</p>
                </div>
            </div>
        </section>

        <section class="places-section" aria-labelledby="featured-title">
            <div class="section-heading">
                <div>
                    <p class="section-kicker">Handpicked for you</p>
                    <h2 id="featured-title">Top Destinations</h2>
                </div>
                <a class="view-all-link" href="destination.php#places">View all 40 places <span aria-hidden="true">→</span></a>
            </div>
            <div class="destination-grid" id="featured-grid"></div>
        </section>

        <?php include __DIR__ . '/footer.php'; ?>
    </main>

    <script src="script.js" defer></script>
</body>
</html>