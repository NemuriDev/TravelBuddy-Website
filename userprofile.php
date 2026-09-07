<?php
require_once __DIR__ . '/config.php';
?>
<?php
$activePage = 'profile';
$loggedIn   = true;
$userName   = 'Juan dela Cruz';
$userEmail  = 'juandelacruz@gmail.com';
$userLoc    = 'Malolos, Bulacan';
$memberSince = 'August 2026';
$initials = 'JD';
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
    <title>TravelBuddies — My Profile</title>
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

        <div class="profile-hero">
            <div class="profile-hero-inner">
                <div class="profile-info">
                    <div class="avatar-box"><?= htmlspecialchars($initials) ?></div>
                    <div class="profile-text">
                        <div class="member-since">Member since <?= htmlspecialchars($memberSince) ?></div>
                        <h1 id="headerName"><?= htmlspecialchars($userName) ?></h1>
                        <div class="profile-meta">
                            <span>✉️ <span id="headerEmail"><?= htmlspecialchars($userEmail) ?></span></span>
                            <span>📍 <span id="headerLocation"><?= htmlspecialchars($userLoc) ?></span></span>
                        </div>
                        <p class="profile-bio" id="bioDisplay">Proud Bulakeño exploring every corner of this beautiful province.</p>
                    </div>
                </div>
                <div class="profile-actions">
                    <button class="btn-outline primary-button" id="editToggleBtn" type="button" onclick="toggleEditMode()">Edit Profile</button>
                    <button class="btn-danger primary-button" type="button" onclick="logOut()">Log Out</button>
                </div>
            </div>
        </div>

        <div class="stats-bar">
            <div class="stat-box"><strong>3</strong><span>Reviews Written</span></div>
            <div class="stat-box"><strong>4</strong><span>Places Saved</span></div>
            <div class="stat-box"><strong>12</strong><span>Places Visited</span></div>
        </div>

        <div class="profile-content">
            <div id="tabsSection">
                <div class="profile-tabs">
                    <button class="profile-tab-btn active" id="tab-activity" type="button" onclick="switchProfileTab('activity')">Recent Activity</button>
                    <button class="profile-tab-btn" id="tab-saved" type="button" onclick="switchProfileTab('saved')">Saved Places</button>
                    <button class="profile-tab-btn" id="tab-settings" type="button" onclick="switchProfileTab('settings')">Settings</button>
                </div>

                <div class="profile-panel active" id="panel-activity">
                    <div class="activity-card">
                        <div class="activity-left">
                            <div class="activity-icon">✏️</div>
                            <div class="activity-text">
                                <div class="headline">Reviewed <b>Barasoain Church</b><span class="stars">★★★★★</span></div>
                                <div class="quote">"Truly magnificent. The history here is palpable."</div>
                            </div>
                        </div>
                        <div class="activity-time">2 days ago</div>
                    </div>

                    <div class="activity-card">
                        <div class="activity-left">
                            <div class="activity-icon">✏️</div>
                            <div class="activity-text">
                                <div class="headline">Reviewed <b>Angat River & Dam</b><span class="stars">★★★★☆</span></div>
                                <div class="quote">"Stunning scenery. Perfect for a day trip from the city."</div>
                            </div>
                        </div>
                        <div class="activity-time">1 week ago</div>
                    </div>

                    <div class="activity-card">
                        <div class="activity-left">
                            <div class="activity-icon visit">📍</div>
                            <div class="activity-text">
                                <div class="headline">Visited <b>Bocaue River Festival</b></div>
                            </div>
                        </div>
                        <div class="activity-time">3 weeks ago</div>
                    </div>
                </div>

                <div class="profile-panel" id="panel-saved">
                    <div class="places-grid">
                        <div class="place-card">
                            <img class="place-thumb"
                                src="https://images.unsplash.com/photo-1644063858399-f8f45b52fd7c?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                alt="Barasoain Church" />
                            <div>
                                <div class="place-category">Heritage</div>
                                <div class="place-name">Barasoain Church</div>
                                <div class="place-loc">📍 Malolos City</div>
                                <div class="place-rating">★★★★★ <span class="count">4.9 (312)</span></div>
                            </div>
                        </div>

                        <div class="place-card">
                            <img class="place-thumb"
                                src="https://images.unsplash.com/photo-1548013146-72479768bada?q=80&w=300&auto=format&fit=crop"
                                alt="Marcelo H. del Pilar Shrine" />
                            <div>
                                <div class="place-category">Heritage</div>
                                <div class="place-name">Marcelo H. del Pilar Shrine</div>
                                <div class="place-loc">📍 Bulakan, Bulacan</div>
                                <div class="place-rating">★★★★★ <span class="count">4.7 (198)</span></div>
                            </div>
                        </div>

                        <div class="place-card">
                            <img class="place-thumb"
                                src="https://images.unsplash.com/photo-1500534623283-312aade485b7?q=80&w=300&auto=format&fit=crop"
                                alt="Angat River & Dam" />
                            <div>
                                <div class="place-category">Nature</div>
                                <div class="place-name">Angat River & Dam</div>
                                <div class="place-loc">📍 Norzagaray, Bulacan</div>
                                <div class="place-rating">★★★★★ <span class="count">4.6 (143)</span></div>
                            </div>
                        </div>

                        <div class="place-card">
                            <img class="place-thumb"
                                src="https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?q=80&w=300&auto=format&fit=crop"
                                alt="Bocaue River Festival" />
                            <div>
                                <div class="place-category">Festival</div>
                                <div class="place-name">Bocaue River Festival</div>
                                <div class="place-loc">📍 Bocaue, Bulacan</div>
                                <div class="place-rating">★★★★★ <span class="count">4.8 (267)</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-panel" id="panel-settings">
                    <div class="settings-card">
                        <h2>Account Settings</h2>
                        <div class="field">
                            <label>Full Name</label>
                            <input type="text" id="settingsName" value="<?= htmlspecialchars($userName) ?>" />
                        </div>
                        <div class="field">
                            <label>Email Address</label>
                            <input type="email" id="settingsEmail" value="<?= htmlspecialchars($userEmail) ?>" />
                        </div>
                        <div class="field">
                            <label>Location</label>
                            <input type="text" id="settingsLocation" value="<?= htmlspecialchars($userLoc) ?>" />
                        </div>
                        <hr class="divider" />
                        <h3 class="section-subhead">Change Password</h3>
                        <div class="field">
                            <label>New Password</label>
                            <input type="password" id="settingsNewPassword" placeholder="••••••••" />
                        </div>
                        <div class="field">
                            <label>Confirm New Password</label>
                            <input type="password" id="settingsConfirmPassword" placeholder="••••••••" />
                        </div>
                        <button class="primary-button" id="saveSettingsBtn" type="button" onclick="saveSettings()">Save Changes</button>
                        <p class="save-confirm" id="saveConfirm">Changes saved successfully.</p>
                        <hr class="divider" />
                        <button class="delete-link" type="button">Delete Account</button>
                    </div>
                </div>
            </div>

            <div class="edit-bio-card" id="editBioSection" style="display: none">
                <div class="edit-photo-row">
                    <div class="avatar-box" id="editAvatarPreview"><?= htmlspecialchars($initials) ?></div>
                    <div class="edit-photo-actions">
                        <label class="secondary-button" for="profilePhotoInput">Change Profile Photo</label>
                        <input type="file" id="profilePhotoInput" accept="image/*" hidden onchange="previewPhoto(event)" />
                        <p class="photo-hint">JPG or PNG. Max 2MB.</p>
                    </div>
                </div>
                <h2>Edit Bio</h2>
                <textarea id="bioTextarea">Proud Bulakeño exploring every corner of this beautiful province.</textarea>
                <button class="primary-button" type="button" onclick="saveBio()">Save Changes</button>
            </div>
        </div>

        <?php include __DIR__ . '/footer.php'; ?>
    </main>

    <script src="script.js" defer></script>
</body>
</html>