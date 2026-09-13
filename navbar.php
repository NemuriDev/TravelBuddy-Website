<?php
require_once __DIR__ . '/config.php';
/**
 * Shared navbar.
 *
 * Include this after setting, optionally:
 *   $activePage = 'home' | 'destination' | 'auth' | 'profile';
 *   $loggedIn   = true | false;  // should be set from session
 *   $user       = getCurrentUser(); // for name/initials
 *
 * Defaults are safe if none of these are set before the include.
 */
$activePage = $activePage ?? '';
$loggedIn   = $loggedIn ?? false;
$user       = $user ?? null;

$userName = $user ? ($user['name'] ?? 'Guest') : 'Guest';
$initials = $user ? ($user['initials'] ?? getUserInitials($userName)) : getUserInitials($userName);
?>
<nav class="guide-nav" aria-label="Main navigation">
    <div class="nav-inner">
        <a class="brand-button" id="brand-home" href="home.php" aria-label="TravelBuddies home">
            <span class="brand-mark" aria-hidden="true">◉</span>
            <span class="brand-copy">
                <span class="brand-name">Travel<span class="brand-accent">Buddy</span></span>
            </span>
        </a>
        <div class="nav-links">
            <a href="home.php" class="<?= $activePage === 'home' ? 'active' : '' ?>">Home</a>
            <a href="destination.php#places" class="<?= $activePage === 'destination' ? 'active' : '' ?>">Places</a>
            <a href="contact.php">Contact</a>
            <button id="nav-saved" type="button"><span class="heart-icon" aria-hidden="true"></span> Saved
                <span class="saved-count" id="nav-saved-count">0</span></button>
            <?php if ($loggedIn && $user): ?>
                <a class="nav-account" href="userprofile.php">
                    <span class="nav-avatar">
                        <?php if (!empty($user['profile_photo'])): ?>
                            <img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="" class="nav-avatar-image">
                        <?php else: ?>
                            <?= htmlspecialchars($initials) ?>
                        <?php endif; ?>
                    </span>
                    <span><?= htmlspecialchars(explode(' ', trim($userName))[0]) ?></span>
                </a>
            <?php else: ?>
                <a class="primary-button" href="auth.php">Log In</a>
            <?php endif; ?>
        </div>
        <button class="mobile-saved" id="mobile-saved" type="button" aria-label="Show saved places">
            <span aria-hidden="true">♡</span> <span id="mobile-saved-count">0</span>
        </button>
    </div>
</nav>
<script>
    // Defined here (once, in the shared navbar) instead of per-page, so
    // every page that includes navbar.php — not just home.php — knows
    // whether the visitor is logged in before script.js decides whether
    // to sync favorites/reviews to the server.
    window.APP_CONFIG = {
        loggedIn: <?= $loggedIn ? 'true' : 'false' ?>,
        isAdmin: <?= ($loggedIn && $user && ($user['role'] ?? '') === ROLE_ADMIN) ? 'true' : 'false' ?>,
        csrfToken: <?= json_encode(generateCSRFToken()) ?>
    };
</script>