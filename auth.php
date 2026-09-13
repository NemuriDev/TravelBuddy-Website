<?php
require_once __DIR__ . '/config.php';
$activePage = 'auth';
$loggedIn = isLoggedIn();
$user = getCurrentUser();
$csrf_token = generateCSRFToken();

// If user is already logged in, redirect to home
if ($loggedIn) {
    header('Location: home.php');
    exit;
}

// Check for error message from login attempt
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

// Which tab was active when the error happened, and what the visitor
// had already typed — so a validation error doesn't wipe the form back
// to blank. Password is never carried back, only re-typed by the user.
$activeTab = (($_GET['tab'] ?? 'login') === 'signup') ? 'signup' : 'login';
$oldEmail = htmlspecialchars($_GET['email'] ?? '');
$oldName = htmlspecialchars($_GET['name'] ?? '');
$oldLocation = htmlspecialchars($_GET['location'] ?? '');
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
    <link rel="stylesheet" href="style.css?v=6">
</head>
<body>
    <main class="page-shell">
        <?php include __DIR__ . '/navbar.php'; ?>

        <?php if ($error): ?>
            <div class="error-message" style="background: #fee; border: 1px solid #c45c26; padding: 10px 20px; margin: 20px auto; max-width: 600px; border-radius: 8px; color: #1a4332;">
                <?= $error ?>
            </div>
        <?php endif; ?>

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
                    <button class="auth-tab <?= $activeTab === 'login' ? 'active' : '' ?>" id="loginTab" type="button" onclick="switchAuthTab('login')">Log In</button>
                    <button class="auth-tab <?= $activeTab === 'signup' ? 'active' : '' ?>" id="signupTab" type="button" onclick="switchAuthTab('signup')">Sign Up</button>
                </div>

                <div id="loginForm" <?= $activeTab === 'signup' ? 'style="display: none"' : '' ?>>
                    <h2>Welcome back!</h2>
                    <p class="auth-subtitle">Log in to save places, write reviews, and plan your visits.</p>

                    <form action="login.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <div class="field">
                            <label>Email Address <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input type="email" name="email" value="<?= $activeTab === 'login' ? $oldEmail : '' ?>" placeholder="juan@email.com" required />
                            </div>
                        </div>

                        <div class="field">
                            <label>Password <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input type="password" name="password" id="passwordInput" placeholder="••••••••" required />
                                <button type="button" class="show-toggle" onclick="togglePassword(this)">Show</button>
                            </div>
                        </div>

                        <button type="submit" class="primary-button btn-block">Log In to My Account</button>
                    </form>
                    
                </div>

                <div id="signupForm" <?= $activeTab === 'signup' ? '' : 'style="display: none"' ?>>
                    <h2>Create your account</h2>
                    <p class="auth-subtitle">Sign up to save places, write reviews, and plan your visits.</p>

                    <form action="signup.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <div class="field">
                            <label>Full Name <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input type="text" name="name" value="<?= $activeTab === 'signup' ? $oldName : '' ?>" placeholder="Juan Dela Cruz" required />
                            </div>
                        </div>

                        <div class="field">
                            <label>Email Address <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input type="email" name="email" value="<?= $activeTab === 'signup' ? $oldEmail : '' ?>" placeholder="juan@email.com" required />
                            </div>
                        </div>

                        <div class="field">
                            <label>Password <span class="req">*</span></label>
                            <div class="input-wrap">
                                <input type="password" name="password" id="signupPasswordInput" placeholder="••••••••" required />
                                <button type="button" class="show-toggle" onclick="toggleSignupPassword(this)">Show</button>
                            </div>
                        </div>

                        <div class="field">
                            <label>Location <span class="optional">(Optional)</span></label>
                            <div class="input-wrap">
                                <input type="text" name="location" value="<?= $activeTab === 'signup' ? $oldLocation : '' ?>" placeholder="e.g. Malolos, Bulacan" />
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