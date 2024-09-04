<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// SQLite database setup
$db = new SQLite3('../db/CryptoShow.db');

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
  $nickname = isset($_POST['nickname']) ? sanitizeInput($_POST['nickname']) : '';
  $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
  $action = isset($_POST['action']) ? sanitizeInput($_POST['action']) : '';

  if ($action === 'login') {
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      echo "<script>alert('Invalid email format.');</script>";
    } else {
      // Handle login
      $stmt = $db->prepare('SELECT user_id FROM Registered_User WHERE user_email = :email AND user_hashed_password = :password');
      $stmt->bindValue(':email', $email, SQLITE3_TEXT);
      $stmt->bindValue(':password', md5($password), SQLITE3_TEXT); // Use md5 for demo; use a stronger hash in production
      $result = $stmt->execute();

      if ( $row = $result->fetchArray(SQLITE3_ASSOC)) {
        $user_id = $row['user_id'];
        echo "<script>alert('Login successful!')</script>";
        // add the user id to the url
        session_start();
        $_SESSION['user_id'] = $user_id;
        header("location: ../home/home.php");
      } else {
        echo "<script>alert('Invalid email or password.')</script>";
      }
    }
  } elseif ($action === 'signup') {
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      echo '<script>alert("Invalid email format.")</script>';
    } elseif ($password !== $confirmPassword) {
      echo '<script>alert("Passwords do not match.")</script>';
    } else {
      // Handle signup
      $hashedPassword = md5($password); // Use md5 for demo; use a stronger hash in production
      $stmt = $db->prepare('INSERT INTO Registered_User (user_email, user_hashed_password, user_nickname, user_name) VALUES (:email, :password, :nickname, :name)');
      $stmt->bindValue(':email', $email, SQLITE3_TEXT);
      $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
      $stmt->bindValue(':nickname', $nickname, SQLITE3_TEXT);
      $stmt->bindValue(':name', $name, SQLITE3_TEXT);
      $stmt->execute();
      echo '<script>alert("Signup successful!.")</script>';
    
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login & Signup</title>
  <link rel="stylesheet" type="text/css" href="login.css">
</head>

<body>
  <div class="wrapper">
    <div class="title-text">
      <div class="title login">Login Form</div>
      <div class="title signup">Signup Form</div>
    </div>
    <div class="form-container">
      <div class="slide-controls">
        <input type="radio" name="slide" id="login" checked>
        <input type="radio" name="slide" id="signup">
        <label for="login" class="slide login">Login</label>
        <label for="signup" class="slide signup">Signup</label>
        <div class="slider-tab"></div>
      </div>
      <div class="form-inner">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> " method="POST" class="login">
          <input type="hidden" name="action" value="login">
          <div class="field">
            <input type="text" name="email" placeholder="Email Address" required>
          </div>
          <div class="field">
            <input type="password" name="password" placeholder="Password" required>
          </div>
          <div class="pass-link"><a href="./forgetPassword/forgetPassword.php">Forgot password?</a></div>
          <div class="field btn">
            <div class="btn-layer"></div>
            <input type="submit" value="Login">
          </div>
          <div class="signup-link">Not a member? <a href="#">Signup now</a></div>
        </form>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> " method="POST" class="signup">
          <input type="hidden" name="action" value="signup">
          <div class="field">
            <input type="email" name="email" placeholder="Email Address" required>
          </div>
          <div class="field">
            <input type="text" name="nickname" placeholder="NickName" required>
          </div>
          <div class="field">
            <input type="text" name="name" placeholder="Name" required>
          </div>
          <div class="field">
            <input type="password" name="password" placeholder="Password" required>
          </div>
          <div class="field">
            <input type="password" name="confirm_password" placeholder="Confirm password" required>
          </div>
          <div class="field btn">
            <div class="btn-layer"></div>
            <input type="submit" value="Signup">
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="login.js"></script>
</body>
</html >