<?php
require_once __DIR__ . '/config.php';

$activePage = 'profile';
$loggedIn = isLoggedIn();

// Redirect if not logged in
if (!$loggedIn) {
    header('Location: auth.php');
    exit;
}

$user = getCurrentUser();

// Fetch full user data
$userData = getUserData($user['id']);

if ($userData) {
    $userName = $userData['name'];
    $userEmail = $userData['email'];
    $userLoc = $userData['location'] ?? 'Not set';
    $memberSince = date('F Y', strtotime($userData['created_at']));
} else {
    // Fallback to session data
    $userName = $user['name'] ?? 'Guest';
    $userEmail = $user['email'] ?? '';
    $userLoc = $_SESSION['user_location'] ?? 'Not set';
    $memberSince = $_SESSION['user_created_at'] ?? 'August 2026';
}

$initials = getUserInitials($userName);
$csrf_token = generateCSRFToken();


// =========================================
// DATABASE DATA FOR PROFILE
// =========================================

$reviewCount = 0;
$savedCount = 0;

$recentActivity = [];
$savedPlaces = [];

try {

    $db = getDBConnection();

    if ($db) {

        // =====================================
        // COUNT REVIEWS
        // =====================================

        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM reviews
            WHERE user_id = ?
        ");

        $stmt->execute([$user['id']]);
        $reviewCount = (int) $stmt->fetchColumn();


        // =====================================
        // COUNT SAVED PLACES
        // =====================================

        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM favorites
            WHERE user_id = ?
        ");

        $stmt->execute([$user['id']]);
        $savedCount = (int) $stmt->fetchColumn();


        // =====================================
        // RECENT REVIEWS / ACTIVITY
        // =====================================

        $stmt = $db->prepare("
            SELECT
                reviews.id,
                reviews.rating,
                reviews.comment,
                reviews.created_at,
                destinations.name AS destination_name
            FROM reviews

            INNER JOIN destinations
                ON reviews.destination_id = destinations.id

            WHERE reviews.user_id = ?

            ORDER BY reviews.created_at DESC

            LIMIT 10
        ");

        $stmt->execute([$user['id']]);
        $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // =====================================
        // SAVED PLACES
        // =====================================

        $stmt = $db->prepare("
            SELECT
                favorites.id,
                favorites.created_at,
                destinations.id AS destination_id,
                destinations.name,
                destinations.location,
                destinations.image_url AS image,
                destinations.category
            FROM favorites

            INNER JOIN destinations
                ON favorites.destination_id = destinations.id

            WHERE favorites.user_id = ?

            ORDER BY favorites.created_at DESC
        ");

        $stmt->execute([$user['id']]);
        $savedPlaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {

    // Keep the profile functional if a database query fails
    $reviewCount = 0;
    $savedCount = 0;
    $recentActivity = [];
    $savedPlaces = [];
}


// =========================================
// SUCCESS / ERROR MESSAGES
// =========================================

$updated = isset($_GET['updated']) ? true : false;

$error = isset($_GET['error'])
    ? htmlspecialchars($_GET['error'])
    : '';

?>
<!doctype html>
<html lang="en">

<head>

    <meta charset="UTF-8" />

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0" />

    <title>TravelBuddies — My Profile</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin />

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap"
        rel="stylesheet" />

    <link
        rel="stylesheet"
        href="style.css" />

</head>


<body>

    <main class="page-shell">

        <?php include __DIR__ . '/navbar.php'; ?>


        <!-- =====================================
         PROFILE HERO
    ====================================== -->

        <div class="profile-hero">

            <div class="profile-hero-inner">

                <div class="profile-info">

                    <div class="avatar-box">
                        <?php if (!empty($userData['profile_photo'])): ?>

                            <img
                                src="<?= htmlspecialchars($userData['profile_photo']) ?>"
                                alt="Profile Photo"
                                class="profile-avatar-image">

                        <?php else: ?>

                            <?= htmlspecialchars($initials) ?>

                        <?php endif; ?>
                    </div>


                    <div class="profile-text">

                        <div class="member-since">

                            Member since
                            <?= htmlspecialchars($memberSince) ?>

                        </div>


                        <h1 id="headerName">

                            <?= htmlspecialchars($userName) ?>

                        </h1>


                        <div class="profile-meta">

                            <span>

                                ✉️

                                <span id="headerEmail">

                                    <?= htmlspecialchars($userEmail) ?>

                                </span>

                            </span>


                            <span>

                                📍

                                <span id="headerLocation">

                                    <?= htmlspecialchars($userLoc) ?>

                                </span>

                            </span>

                        </div>


                        <p class="profile-bio" id="bioDisplay">
                            <?= htmlspecialchars(
                                !empty($userData['bio'])
                                    ? $userData['bio']
                                    : 'Tell other travelers a little about yourself.'
                            ) ?>
                        </p>

                    </div>

                </div>


                <div class="profile-actions">

                    <button
                        class="btn-outline primary-button"
                        id="editToggleBtn"
                        type="button"
                        onclick="toggleEditMode()">
                        Edit Profile
                    </button>


                    <a
                        class="btn-danger primary-button"
                        href="logout.php"
                        onclick="clearLocalFavorites()">
                        Log Out
                    </a>

                </div>

            </div>

        </div>



        <!-- =====================================
         PROFILE STATISTICS
    ====================================== -->

        <div class="stats-bar">

            <div class="stat-box">

                <strong>
                    <?= $reviewCount ?>
                </strong>

                <span>
                    Reviews Written
                </span>

            </div>


            <div class="stat-box">

                <strong>
                    <?= $savedCount ?>
                </strong>

                <span>
                    Places Saved
                </span>

            </div>


            <div class="stat-box">

                <strong>
                    0
                </strong>

                <span>
                    Places Visited
                </span>

            </div>

        </div>



        <!-- =====================================
         PROFILE CONTENT
    ====================================== -->

        <div class="profile-content">

            <div id="tabsSection">


                <!-- =====================================
                 TABS
            ====================================== -->

                <div class="profile-tabs">

                    <button
                        class="profile-tab-btn active"
                        id="tab-activity"
                        type="button"
                        onclick="switchProfileTab('activity')">
                        Recent Activity
                    </button>


                    <button
                        class="profile-tab-btn"
                        id="tab-saved"
                        type="button"
                        onclick="switchProfileTab('saved')">
                        Saved Places
                    </button>


                    <button
                        class="profile-tab-btn"
                        id="tab-settings"
                        type="button"
                        onclick="switchProfileTab('settings')">
                        Settings
                    </button>

                </div>



                <!-- =====================================
                 RECENT ACTIVITY
            ====================================== -->

                <div
                    class="profile-panel active"
                    id="panel-activity">

                    <?php if (empty($recentActivity)): ?>

                        <div class="empty-state">

                            <div class="empty-icon">
                                📝
                            </div>


                            <h3>
                                No recent activity yet
                            </h3>


                            <p>
                                Start exploring destinations and
                                leave a rating or review.
                                Your activity will appear here.
                            </p>


                            <a
                                href="destination.php"
                                class="primary-button">
                                Explore Destinations
                            </a>

                        </div>

                    <?php else: ?>


                        <?php foreach ($recentActivity as $activity): ?>

                            <div class="activity-card">

                                <div class="activity-left">

                                    <div class="activity-icon">

                                        ✏️

                                    </div>


                                    <div class="activity-text">

                                        <div class="headline">

                                            Reviewed

                                            <b>

                                                <?= htmlspecialchars(
                                                    $activity['destination_name']
                                                ) ?>

                                            </b>


                                            <span class="stars">

                                                <?php

                                                $rating =
                                                    (int) $activity['rating'];

                                                for (
                                                    $i = 1;
                                                    $i <= 5;
                                                    $i++
                                                ) {

                                                    echo $i <= $rating
                                                        ? '★'
                                                        : '☆';
                                                }

                                                ?>

                                            </span>

                                        </div>


                                        <?php if (
                                            !empty($activity['comment'])
                                        ): ?>

                                            <div class="quote">

                                                "<?= htmlspecialchars(
                                                        $activity['comment']
                                                    ) ?>"

                                            </div>

                                        <?php endif; ?>

                                    </div>

                                </div>


                                <div class="activity-time">

                                    <?= htmlspecialchars(
                                        date(
                                            'M d, Y',
                                            strtotime(
                                                $activity['created_at']
                                            )
                                        )
                                    ) ?>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>



                <!-- =====================================
                 SAVED PLACES
            ====================================== -->

                <div
                    class="profile-panel"
                    id="panel-saved">

                    <?php if (empty($savedPlaces)): ?>

                        <div class="empty-state">

                            <div class="empty-icon">
                                🔖
                            </div>


                            <h3>
                                No saved places yet
                            </h3>


                            <p>
                                Destinations you save while
                                exploring Bulacan will appear here.
                            </p>


                            <a
                                href="destination.php"
                                class="primary-button">
                                Explore Destinations
                            </a>

                        </div>

                    <?php else: ?>


                        <div class="places-grid">


                            <?php foreach ($savedPlaces as $place): ?>

                                <div class="place-card">

                                    <?php if (
                                        !empty($place['image'])
                                    ): ?>

                                        <img
                                            class="place-thumb"
                                            src="<?= htmlspecialchars(
                                                        $place['image']
                                                    ) ?>"
                                            alt="<?= htmlspecialchars(
                                                        $place['name']
                                                    ) ?>" />

                                    <?php else: ?>

                                        <div
                                            class="place-thumb"
                                            style="
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                            background:#eee;
                                        ">
                                            🌄
                                        </div>

                                    <?php endif; ?>


                                    <div>

                                        <div class="place-category">

                                            <?= htmlspecialchars(
                                                $place['category']
                                                    ?? 'Destination'
                                            ) ?>

                                        </div>


                                        <div class="place-name">

                                            <?= htmlspecialchars(
                                                $place['name']
                                            ) ?>

                                        </div>


                                        <div class="place-loc">

                                            📍

                                            <?= htmlspecialchars(
                                                $place['location']
                                            ) ?>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>


                        </div>

                    <?php endif; ?>

                </div>



                <!-- =====================================
                 SETTINGS
            ====================================== -->

                <div
                    class="profile-panel"
                    id="panel-settings">

                    <div class="settings-card">

                        <h2>
                            Account Settings
                        </h2>


                        <form
                            action="update_profile.php"
                            method="post">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars(
                                            $csrf_token
                                        ) ?>" />


                            <div class="field">

                                <label>
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    id="settingsName"
                                    value="<?= htmlspecialchars(
                                                $userName
                                            ) ?>" />

                            </div>


                            <div class="field">

                                <label>
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    id="settingsEmail"
                                    value="<?= htmlspecialchars(
                                                $userEmail
                                            ) ?>" />

                            </div>


                            <div class="field">

                                <label>
                                    Location
                                </label>

                                <input
                                    type="text"
                                    name="location"
                                    id="settingsLocation"
                                    value="<?= htmlspecialchars(
                                                $userLoc
                                            ) ?>" />

                            </div>


                            <hr class="divider" />


                            <h3 class="section-subhead">
                                Change Password
                            </h3>


                            <div class="field">

                                <label>
                                    New Password
                                </label>

                                <input
                                    type="password"
                                    name="new_password"
                                    id="settingsNewPassword"
                                    placeholder="••••••••" />

                            </div>


                            <div class="field">

                                <label>
                                    Confirm New Password
                                </label>

                                <input
                                    type="password"
                                    name="confirm_password"
                                    id="settingsConfirmPassword"
                                    placeholder="••••••••" />

                            </div>


                            <button
                                class="primary-button"
                                id="saveSettingsBtn"
                                type="submit">
                                Save Changes
                            </button>


                            <p
                                class="save-confirm"
                                id="saveConfirm">
                                Changes saved successfully.
                            </p>


                            <hr class="divider" />


                            <button
                                class="delete-link"
                                type="button">
                                Delete Account
                            </button>

                        </form>

                    </div>

                </div>

            </div>



<!-- =====================================
     EDIT PROFILE
====================================== -->

<div
    class="edit-bio-card"
    id="editBioSection"
    style="display: none">

    <form
        action="update_profile.php"
        method="post"
        enctype="multipart/form-data">

        <!-- CSRF -->
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($csrf_token) ?>">

        <!-- Keep current account information -->
        <input
            type="hidden"
            name="name"
            value="<?= htmlspecialchars($userName) ?>">

        <input
            type="hidden"
            name="email"
            value="<?= htmlspecialchars($userEmail) ?>">

        <input
            type="hidden"
            name="location"
            value="<?= htmlspecialchars(
                $userLoc === 'Not set' ? '' : $userLoc
            ) ?>">

        <!-- =====================================
             PROFILE PHOTO
        ====================================== -->

        <div class="edit-photo-row">

            <div
                class="avatar-box"
                id="editAvatarPreview">

                <?php if (!empty($userData['profile_photo'])): ?>

                    <img
                        src="<?= htmlspecialchars($userData['profile_photo']) ?>"
                        alt="Profile Photo"
                        class="profile-avatar-image">

                <?php else: ?>

                    <?= htmlspecialchars($initials) ?>

                <?php endif; ?>

            </div>

            <div class="edit-photo-actions">

                <label
                    class="secondary-button"
                    for="profilePhotoInput">
                    Change Profile Photo
                </label>

                <input
                    type="file"
                    id="profilePhotoInput"
                    name="profile_photo"
                    accept="image/jpeg,image/png,image/webp"
                    hidden
                    onchange="previewPhoto(event)">

                <p class="photo-hint">
                    JPG, PNG or WEBP. Max 2MB.
                </p>

            </div>

        </div>


        <!-- =====================================
             BIO
        ====================================== -->

        <h2>
            Edit Bio
        </h2>

        <textarea
            id="bioTextarea"
            name="bio"
            placeholder="Tell other travelers about yourself..."><?= htmlspecialchars(
                $userData['bio'] ?? ''
            ) ?></textarea>


        <!-- =====================================
             SAVE
        ====================================== -->

        <button
            class="primary-button"
            type="submit">
            Save Changes
        </button>

    </form>

</div>




    </main>


    <script
        src="script.js"
        defer></script>

</body>

</html>