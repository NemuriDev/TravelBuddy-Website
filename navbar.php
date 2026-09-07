<?php
require_once __DIR__ . '/config.php';
?>
<?php
/**
 * Shared navbar.
 *
 * Include this after setting, optionally:
 *   $activePage = 'home' | 'destination' | 'auth' | 'profile';
 *   $loggedIn   = true | false;
 *   $userName   = 'Juan dela Cruz';
 *
 * Defaults are safe if none of these are set before the include.
 */
$activePage = $activePage ?? '';
$loggedIn   = $loggedIn ?? false;
$userName   = $userName ?? 'Guest';
$initials   = '';
foreach (explode(' ', trim($userName)) as $part) {
    if ($part !== '') {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
}
$initials = mb_substr($initials, 0, 2) ?: 'TB';
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
            <button id="nav-saved" type="button"><span class="heart-icon" aria-hidden="true">♡</span> Saved
                <span class="saved-count" id="nav-saved-count">0</span></button>
            <?php if ($loggedIn): ?>
                <a class="nav-account" href="userprofile.php">
                    <span class="nav-avatar"><?= htmlspecialchars($initials) ?></span>
                    <span><?= htmlspecialchars(explode(' ', trim($userName))[0]) ?></span>
                </a>
            <?php else: ?>
                <a class="primary-button" href="LogIn.php">Log In</a>
            <?php endif; ?>
        </div>
        <button class="mobile-saved" id="mobile-saved" type="button" aria-label="Show saved places">
            <span aria-hidden="true">♡</span> <span id="mobile-saved-count">0</span>
        </button>
    </div>
</nav>