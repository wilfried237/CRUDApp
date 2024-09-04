<?php
session_start(); // Start the session

// Database connection (replace with your actual connection)
$db = new SQLite3('../db/CryptoShow.db');

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
  // Fetch user information
  $userId = $_SESSION['user_id'];
  $stmt = $db->prepare('SELECT user_auth_level FROM Registered_User WHERE user_id = :user_id');
  $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
  $result = $stmt->execute();

  if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $authLevel = $row['user_auth_level'];
  } else {
    // User not found, redirect to login
    header("Location: ../Auth/login.php");
    exit;
  }
} else {
  // User not logged in, redirect to login
  header("Location: ../Auth/login.php");
  exit;
}

// Determine the current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Website</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<style>
  .navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background-color: #f0f0f0;
  }

  .logo {
    display: flex;
    align-items: center;
  }

  .nav-links {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .nav-links li {
    margin-left: 1rem;
  }

  .nav-links a {
    text-decoration: none;
    color: #333;
  }

  .navbar-nav .nav-link.active {
    font-weight: bold;
    color: #007bff;
  }

  /* Responsive navbar (same as before) */
  @media (max-width: 768px) {
    .nav-links {
      display: none;
    }

    .navbar {
      position: relative;
    }

    .navbar .logo {
      width: 100%;
    }

    .navbar .nav-links {
      position: absolute;
      top: 100%;
      left: 0;
      width: 100%;
      background-color: #f0f0f0;
      flex-direction: column;
      padding: 1rem;
      display: none;
    }

    .navbar .nav-links li {
      margin-bottom: 1rem;
    }

    .navbar .nav-links a {
      color: #333;
    }

    .navbar .nav-links.show {
      display: flex;
    }
  }
</style>

<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand" href="../../home/home.php">CryptoShow</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'Event.php') ? 'active' : ''; ?>" href="../Events/Event.php">Events</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'devices.php') ? 'active' : ''; ?>" href="../devices/devices.php">Devices</a>
          </li>
          <?php if ($authLevel == 2): ?>
            <li class="nav-item">
              <a class="nav-link <?php echo ($current_page == 'createEvent.php') ? 'active' : ''; ?>" href="../createEvent/createEvent.php">Create Event</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo ($current_page == 'member.php') ? 'active' : ''; ?>" href="../members/member.php">Members</a>
            </li>
          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'RegisteredEvent.php') ? 'active' : ''; ?>" href="../Events/RegisteredEvent.php">Registered Event</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'myProfile.php') ? 'active' : ''; ?>" href="../Profile/myProfile.php">Profile</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
