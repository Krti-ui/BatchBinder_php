<?php
// Handle form submission
$message = '';
$messageType = '';

if ($_POST && isset($_POST['name'], $_POST['email'], $_POST['subject'], $_POST['message'])) {
    // Sanitize input data
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $subject = htmlspecialchars(trim($_POST['subject']));
    $messageContent = htmlspecialchars(trim($_POST['message']));
    
    // Validation
    if (!$name || !$email || !$subject || !$messageContent) {
        $message = 'Please fill in all fields with valid information.';
        $messageType = 'error';
    } else {
        // Email configuration
        $to = 'admin@batchbinder.com'; // Replace with your actual email
        $emailSubject = 'Contact Form: ' . $subject;
        $emailBody = "Name: $name\n";
        $emailBody .= "Email: $email\n";
        $emailBody .= "Subject: $subject\n\n";
        $emailBody .= "Message:\n$messageContent";
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Send email
        if (mail($to, $emailSubject, $emailBody, $headers)) {
            $message = 'Thank you for contacting us! We\'ll get back to you soon.';
            $messageType = 'success';
            
            // Optional: Save to database
            // saveContactForm($name, $email, $subject, $messageContent);
        } else {
            $message = 'Sorry, there was an error sending your message. Please try again later.';
            $messageType = 'error';
        }
    }
}

// Optional: Function to save to database
/*
function saveContactForm($name, $email, $subject, $message) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=batchbinder', $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("INSERT INTO contact_forms (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $email, $subject, $message]);
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - BatchBinder</title>
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="navbar.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Raleway', 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        html { scroll-behavior: smooth; }
        body {
            background-color: #fff;
            color: #111;
            line-height: 1.6;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        p {
            font-size: 0.97rem;
            color: #222;
            transition: color 0.3s ease;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        header {
            position: relative;
            height: 220px;
            background: #f8fafc;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: background-color 0.3s ease;
        }
        .header-content {
            text-align: center;
            padding: 0 20px;
        }
        .header-content h1 {
            font-size: 2.2rem;
            margin-bottom: 15px;
        }
        .header-content p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .contact-form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(56,189,248,0.07);
            transition: all 0.3s ease;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
        }
        textarea {
            resize: vertical;
            min-height: 150px;
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background-color: #38bdf8;
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .submit-btn:hover {
            background-color: #0ea5e9;
            transform: translateY(-2px);
        }
        .form-message {
            margin-top: 18px;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-size: 1rem;
        }
        .form-message.success {
            background-color: #d1fae5;
            border: 1px solid #34d399;
            color: #065f46;
        }
        .form-message.error {
            background-color: #fee2e2;
            border: 1px solid #f87171;
            color: #991b1b;
        }
        .custom-footer {
            width: 100%;
            margin-top: 60px;
            padding: 0 0 18px;
        }
        .footer-row {
            display: flex;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .footer-link {
            font-size: 12px;
            color: #888;
            text-decoration: none;
            transition: color 0.3s ease;
            padding: 0 8px;
        }
        .footer-link:hover {
            color: #38bdf8;
            text-decoration: underline;
        }
        .copyright-row {
            text-align: center;
            margin-top: 15px;
        }
        .footer-copyright {
            font-size: 11px;
            color: #aaa;
        }

        /* Dark mode styles */
        body.dark-mode {
            background-color: #111;
            color: linen;
        }
        body.dark-mode p {
            color: #ddd;
        }
        body.dark-mode header {
            background-color: #18181b;
        }
        body.dark-mode .contact-form-container {
            background: #18181b;
            border: 1px solid #222;
            box-shadow: 0 4px 18px rgba(56,189,248,0.1);
        }
        body.dark-mode input,
        body.dark-mode textarea {
            background: #222;
            border-color: #333;
            color: linen;
        }
        body.dark-mode .footer-link {
            color: #aaa;
        }
        body.dark-mode .footer-link:hover {
            color: #38bdf8;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            header {
                height: auto;
                padding: 60px 20px;
            }
            .header-content h1 {
                font-size: 1.8rem;
            }
            .header-content p {
                font-size: 1rem;
            }
            .contact-form-container {
                padding: 20px;
                margin: 20px auto;
            }
            .footer-row {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
            .footer-link {
                text-align: center !important;
                padding: 5px 0;
            }
        }
         .footer {
            width: 100%;
            padding: 40px 0 30px;
            background: #fff;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .footer-links {
            display: flex;
            gap: 25px;
            margin-bottom: 20px;
        }
        
        .footer-link {
            color: #38bdf8;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.2s;
        }
        
        .footer-link:hover {
            color: #0ea5e9;
            text-decoration: underline;
        }
        
        .copyright {
            font-size: 0.85rem;
            color: #777;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index2.php" class="navbar-logo" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
            <span style="font-family: 'Poppins', Arial, sans-serif; font-weight: 600; font-size: 1.5rem; color: #222; letter-spacing: 1px;">BatchBinder</span>
        </a>
        <div class="navbar-links">
            <a href="index2.php">Home</a>
            <a href="notes.php">Notes</a>
            <a href="bookshelf.php">BookShelf</a>
            <a href="#" onclick="showComingSoonMessage('Collaborative Community'); return false;">Community</a>
        </div>
        <!-- Hamburger menu for mobile -->
        <button class="hamburger" aria-label="Toggle menu">
            <span class="hamburger-box">
                <span class="hamburger-inner"></span>
            </span>
        </button>
    </nav>
    <header>
        <div class="header-content">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you! Fill out the form below and we'll get back to you soon.</p>
        </div>
    </header>
    <div class="contact-form-container">
        <form id="contactForm" method="POST" action="">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
            </div>
            <button type="submit" class="submit-btn">Send Message</button>
            
            <?php if ($message): ?>
            <div class="form-message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
        </form>
    </div>
    <div class="custom-footer">
        <div class="footer-row">
            <a class="footer-link" href="terms.php" style="text-align:left;">Terms and Service</a>
            <a class="footer-link" href="privacy.php" style="text-align:center;">Privacy Policy</a>
            <a class="footer-link" href="contact.php" style="text-align:right;">Contact Us</a>
        </div>
        <div class="copyright-row">
            <span class="footer-copyright">&copy; 2025 BatchBinder. All rights reserved.</span>
        </div>
    </div>

    <script>
        // Theme management
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        // Get theme from localStorage or system preference
        function getSavedTheme() {
            const saved = localStorage.getItem('batchbinder-theme');
            if (saved === 'dark') return true;
            if (saved === 'light') return false;
            return prefersDark;
        }

        // Set theme across the application
        // Initialize theme on load
        document.addEventListener('DOMContentLoaded', () => {
            // Set up event listeners
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const isDark = !document.body.classList.contains('dark-mode');
                    setTheme(isDark);
                });
            }
        });

        // Coming soon message
        function showComingSoonMessage(feature) {
            const messages = [
                `🚧 Oops! ${feature} is still cooking in our development kitchen! 👨‍🍳`,
                `✨ Hold tight! ${feature} is getting dressed up for its big debut! 🎭`,
                `🎯 Almost there! ${feature} is doing final rehearsals! 🎬`,
                `🔮 ${feature} is learning some cool tricks before meeting you! 🎩`,
                `🚀 ${feature} is in its final countdown to launch! 🌟`
            ];
            alert(messages[Math.floor(Math.random() * messages.length)]);
        }

        // Hamburger menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.querySelector('.hamburger');
            const navLinks = document.querySelector('.navbar-links');
            
            hamburger.addEventListener('click', function() {
                navLinks.classList.toggle('active');
                hamburger.classList.toggle('is-active');
            });
        });

        <?php if ($messageType === 'success'): ?>
        // Clear form after successful submission
        setTimeout(() => {
            document.getElementById('contactForm').reset();
        }, 100);
        <?php endif; ?>
    </script>
</body>
</html>