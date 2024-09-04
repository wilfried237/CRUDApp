<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="reset.css">
    <title>Reset Password</title>
</head>

<body>
    <div class="wrapper">
        <div class="title-text">
            <div class="title login">Reset Password</div>
        </div>
        <div class="form-container">
            <div class="form-inner">
                <?php
                // Enable error reporting for debugging
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);

                // SQLite database setup
                $db = new SQLite3('../../component/navbar/CryptoShow.db');

                // Helper function to sanitize user input
                function sanitizeInput($data)
                {
                    return htmlspecialchars(trim($data));
                }

                // Handle form submission
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // Sanitize and validate input
                    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
                    $password = isset($_POST['password']) ? sanitizeInput($_POST['password']) : '';
                    $confirmPassword = isset($_POST['confirm_password']) ? sanitizeInput($_POST['confirm_password']) : '';

                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        echo "<script>alert('Invalid email format.');</script>";
                    } elseif ($password !== $confirmPassword) {
                        echo '<script>alert("Passwords do not match.")</script>';
                    } else {
                        // Update password in the database
                        $hashedPassword = md5($password); // Use md5 for demo; use a stronger hash in production
                        $stmt = $db->prepare('UPDATE Registered_User SET user_hashed_password = :password WHERE user_email = :email');
                        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                        $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
                        $stmt->execute();

                        echo '<script>alert("Password reset successful!.")</script>';
                    }
                }
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="login">
                    <div class="field">
                        <input type="email" name="email" placeholder="Email Address" required>
                    </div>
                    <div class="field">
                        <input type="password" name="password" placeholder="New Password" required>
                    </div>
                    <div class="field">
                        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                    </div>
                    <div class="field btn">
                        <div class="btn-layer"></div>
                        <input type="submit" value="Reset Password">
                    </div>
                    <div class="signup-link">Not a member? <a href="../login.php">Signup now</a></div>
                </form>
            </div>
        </div>
    </div>
    <script src="reset.js"></script>
</body>

</html>
