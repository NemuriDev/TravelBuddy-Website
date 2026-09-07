<?php
require_once __DIR__ . '/config.php';
?>
<?php
$activePage = 'auth';
$loggedIn   = false;
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
    <title>TravelBuddies — Log In</title>
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

        <section class="auth-grid">
            <div class="auth-art reveal">
                <div class="auth-art-image image-frame">
                    <img src="https://images.unsplash.com/photo-1644063858399-f8f45b52fd7c?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                        alt="A view of the Bulacan countryside" />
                    <span class="image-fallback" aria-hidden="true">◉</span>
                </div>
                <p class="section-kicker" style="margin-top: 22px;">Province of Bulacan</p>
                <h1 style="font-family: var(--display); font-size: clamp(32px, 4vw, 44px); font-weight: 600; letter-spacing: -.02em; line-height: 1.05; margin-top: 10px; max-width: 460px; color: var(cream);">
                    Your gateway to Bulacan's wonders.
                </h1>
                <p class="hero-description" style="max-width: 440px;">Become a traveler discovering heritage sites, natural escapes, and vibrant festivals across the province.</p>
                <div class="auth-stats">
                    <div class="auth-stat"><strong>500+</strong><span>Reviews</span></div>
                    <div class="auth-stat"><strong>40+</strong><span>Places</span></div>
                    <div class="auth-stat"><strong>4.8★</strong><span>Rating</span></div>
                </div>
            </div>

            <div class="auth-panel reveal" style="--delay: .1s">
                <div class="auth-tabs">
                    <button class="auth-tab active" id="loginTab" type="button" onclick="switchAuthTab('login')">Log In</button>
                    <button class="auth-tab" id="signupTab" type="button" onclick="switchAuthTab('signup')">Sign Up</button>
                </div>

                <div id="loginForm">
                    <h2>Welcome back!</h2>
                    <p class="auth-subtitle">Log in to save places, write reviews, and plan your visits.</p>

                    <form onsubmit="handleAuthSubmit(event)">
                        <div class="field">
                            <label>Email Address <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input type="email" placeholder="juan@email.com" required />
                            </div>
                        </div>

                        <div class="field">
                            <label>Password <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input type="password" id="passwordInput" placeholder="••••••••" required />
                                <button type="button" class="show-toggle" onclick="togglePassword(this)">Show</button>
                            </div>
                        </div>

                        <button type="submit" class="primary-button btn-block">Log In to My Account</button>
                    </form>
                    
                </div>

                <div id="signupForm" style="display: none">
                    <h2>Create your account</h2>
                    <p class="auth-subtitle">Sign up to save places, write reviews, and plan your visits.</p>

                    <form onsubmit="handleAuthSubmit(event)">
                        <div class="field">
                            <label>Full Name <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input type="text" placeholder="Juan Dela Cruz" required />
                            </div>
                        </div>

                        <div class="field">
                            <label>Email Address <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input type="email" placeholder="juan@email.com" required />
                            </div>
                        </div>

                        <div class="field">
                            <label>Password <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input type="password" id="signupPasswordInput" placeholder="••••••••" required />
                                <button type="button" class="show-toggle" onclick="toggleSignupPassword(this)">Show</button>
                            </div>
                        </div>

                        <div class="field">
                            <label>Location <span class="optional">(Optional)</span></label>
                            <div class="input-wrap">
                                <input type="text" placeholder="e.g. Malolos, Bulacan" />
                            </div>
                        </div>
                        <button type="submit" class="primary-button btn-block">Create My Account</button>
                    </form>
                </div>
            </div>
        </section>

        <?php include __DIR__ . '/footer.php'; ?>
    </main>

    <script src="script.js" defer></script>
</body>
</html>