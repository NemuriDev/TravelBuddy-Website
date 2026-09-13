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

    if ($fullName === '' || $email === '' || $subject === '' || $message === '') {
        $errorMessage = 'Please fill in all fields.';
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

    <link rel="stylesheet" href="style.css">
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


                <!-- BULACAN IMAGE -->
                <div class="contact-location-card">

                    <img
                        src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80"
                        alt="Bulacan landscape">

                    <div class="location-overlay">
                        <h3>Bulacan, Philippines</h3>
                        <p>14.796° N, 120.878° E</p>
                    </div>

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

                    <div class="contact-form-row">

                        <div class="contact-field">

                            <label for="full_name">
                                FULL NAME
                            </label>

                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
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

                            <option value="General Inquiry">
                                General Inquiry
                            </option>

                            <option value="Submit a Place">
                                Submit a Place
                            </option>

                            <option value="Correct Information">
                                Correct Information
                            </option>

                            <option value="Partnership">
                                Partnership
                            </option>

                            <option value="Other">
                                Other
                            </option>

                        </select>

                    </div>


                    <div class="contact-field">

                        <label for="message">
                            MESSAGE
                        </label>

                        <textarea
                            id="message"
                            name="message"
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
    <script src="script.js" defer></script>
</body>

</html>