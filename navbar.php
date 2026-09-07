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
            <a href="destination.php#about">Contact</a>
            <button id="nav-saved" type="button"><span class="heart-icon" aria-hidden="true"></span> Saved
                <span class="saved-count" id="nav-saved-count">0</span></button>
            <?php if ($loggedIn && $user): ?>
                <a class="nav-account" href="userprofile.php">
                    <span class="nav-avatar"><?= htmlspecialchars($initials) ?></span>
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