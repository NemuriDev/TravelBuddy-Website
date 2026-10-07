<?php
require_once __DIR__ . '/config.php';

$activePage = 'contact';
$loggedIn = isLoggedIn();
$user = getCurrentUser();

$messageSent = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Your session expired. Please reload the page and try again.';
    } elseif ($fullName === '' || $email === '' || $subject === '' || $message === '') {
        $errorMessage = 'Please fill in all fields.';
    } elseif (!in_array($subject, CONTACT_SUBJECTS, true)) {
        $errorMessage = 'Please choose a topic from the list.';
    } elseif (mb_strlen($fullName) > MAX_NAME_LENGTH) {
        $errorMessage = 'Name must be ' . MAX_NAME_LENGTH . ' characters or fewer.';
    } elseif (mb_strlen($email) > MAX_EMAIL_LENGTH) {
        $errorMessage = 'Email must be ' . MAX_EMAIL_LENGTH . ' characters or fewer.';
    } elseif (mb_strlen($message) > MAX_MESSAGE_LENGTH) {
        $errorMessage = 'Message must be ' . MAX_MESSAGE_LENGTH . ' characters or fewer.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } else {
        $body =
            "New message from the TravelBuddy contact form\n\n" .
            "Name: {$fullName}\n" .
            "Email: {$email}\n" .
            "Subject: {$subject}\n\n" .
            "Message:\n{$message}";

        try {
            // Replying to the notification goes straight to the visitor,
            // not back through the site's own Brevo account.
            sendTransactionalEmail(
                CONTACT_RECIPIENT,
                'TravelBuddy Admin',
                '[TravelBuddy Contact] ' . $subject,
                $body,
                $email,
                $fullName
            );
            $messageSent = true;
        } catch (RuntimeException $e) {
            logMessage('Contact form email failed: ' . $e->getMessage(), 'error');
            $errorMessage = 'Sorry, your message could not be sent right now. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TravelBuddy — Contact Us</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
</head>

<body class="contact-page">

    <?php include __DIR__ . '/navbar.php'; ?>


    <!-- HEADER -->
    <section class="contact-header">
        <div class="contact-header-inner">
            <h1>Contact Us</h1>
        </div>
    </section>


    <!-- MAIN CONTENT -->
    <main class="contact-main">

        <div class="contact-container">

            <!-- LEFT SIDE -->
            <section class="contact-info">

                <h2>Plan Your Bulacan Adventure</h2>

                <p class="contact-description">
                    Have a question about visiting Bulacan? Want to submit a new place,
                    correct information, or partner with us? We'd love to hear from you.
                </p>


                <div class="contact-details">

                    <div class="contact-detail">

                        <div class="contact-icon location-icon">
                            ●
                        </div>

                        <div>
                            <h3>Provincial Capitol</h3>
                            <p>Capitol H. del Pilar St., City of Malolos, Bulacan</p>
                        </div>

                    </div>


                    <div class="contact-detail">

                        <div class="contact-icon email-icon">
                            ●
                        </div>

                        <div>
                            <h3>Email Us</h3>
                            <p>travelbuddies79@gmail.com</p>
                        </div>

                    </div>


                    <div class="contact-detail">

                        <div class="contact-icon phone-icon">
                            ●
                        </div>

                        <div>
                            <h3>Tourism Hotline</h3>
                            <p>+63 966 244 9804</p>
                        </div>

                    </div>

                </div>


                <!-- BULACAN MAP -->
                <div class="contact-location-card">

                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d61706.07106848129!2d120.81140647145043!3d14.84604100481396!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x339653af937db881%3A0x32b2f559d7f23f59!2sMalolos%2C%20Bulacan!5e0!3m2!1sen!2sph!4v1790661778070!5m2!1sen!2sph" 
                        width="400" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="strict-origin-when-cross-origin">
                    </iframe>

                </div>

            </section>


            <!-- RIGHT SIDE -->
            <section class="contact-form-card">

                <h2>Send a Message</h2>


                <?php if ($messageSent): ?>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            showToast("Thank you! Your message has been sent successfully.", "success");
                        });
                    </script>
                <?php endif; ?>

                <?php if ($errorMessage !== ''): ?>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            showToast(<?= json_encode($errorMessage) ?>, "error");
                        });
                    </script>
                <?php endif; ?>


                <form method="POST" action="contact.php">

                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCSRFToken()) ?>">

                    <div class="contact-form-row">

                        <div class="contact-field">

                            <label for="full_name">
                                FULL NAME
                            </label>

                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                maxlength="<?= MAX_NAME_LENGTH ?>"
                                placeholder="Juan dela Cruz"
                                value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">

                        </div>


                        <div class="contact-field">

                            <label for="email">
                                EMAIL ADDRESS
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="juan@email.com"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                        </div>

                    </div>


                    <div class="contact-field">

                        <label for="subject">
                            SUBJECT
                        </label>

                        <select id="subject" name="subject">

                            <option value="">
                                Select a topic...
                            </option>

                            <?php foreach (CONTACT_SUBJECTS as $option): ?>
                                <option value="<?= htmlspecialchars($option) ?>" <?= ($_POST['subject'] ?? '') === $option ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($option) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="contact-field">

                        <label for="message">
                            MESSAGE
                        </label>

                        <textarea
                            id="message"
                            name="message"
                            maxlength="<?= MAX_MESSAGE_LENGTH ?>"
                            placeholder="Tell us how we can help..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>

                    </div>


                    <button type="submit" class="contact-submit">
                        Send Message →
                    </button>


                    <p class="contact-response-text">
                        We typically respond within 2 business days.
                        Your information is never shared.
                    </p>

                </form>

            </section>

        </div>

    </main>


    <?php include __DIR__ . '/footer.php'; ?>
    <script src="script.js?v=<?= filemtime(__DIR__ . '/script.js') ?>" defer></script>
</body>

</html>