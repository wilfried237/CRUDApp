<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include_once '../component/navbar/navbar.php'; ?>
    <?php
    session_start();
    $user_id = $_GET['member_id'];
    $authenticated_user = $_SESSION['user_id'];
    // Fetch current user details
    $user_sql = "SELECT * FROM Registered_User WHERE user_id = :user_id";
    $stmt = $db->prepare($user_sql);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);


    // fetch auth user details
    $auth_sql = "SELECT * FROM Registered_User WHERE user_id = :user_id";
    $stmtAuth = $db->prepare($user_sql);
    $stmtAuth->bindValue(':user_id', $authenticated_user, SQLITE3_INTEGER);
    $auth_user = $stmtAuth->execute()->fetchArray(SQLITE3_ASSOC);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize and validate user input
        $nickname = filter_input(INPUT_POST, 'nickname', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $password = $_POST['password'];

        // Update the user's information
        $update_sql = "UPDATE Registered_User SET user_nickname = :nickname, user_email = :email, user_name = :name WHERE user_id = :user_id";
        $stmt = $db->prepare($update_sql);
        $stmt->bindValue(':nickname', $nickname, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);

        if (!empty($password)) {
            if (strlen($password) >= 8) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE Registered_User SET user_password = :password WHERE user_id = :user_id";
                $stmt = $db->prepare($update_sql);
                $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
            } else {
                echo '<script>alert("Password must be at least 8 characters long.");</script>';
            }
        }

        if ($stmt->execute()) {
            echo '<script>alert("Profile updated successfully!");</script>';
            echo '<script>window.location.href = "myProfile.php";</script>';
            exit;
        } else {
            echo '<script>alert("Failed to update profile. Please try again.");</script>';
        }
    }

    // Fetch user devices
    $device_sql = "SELECT * FROM Crypto_Device WHERE fk_user_id = :user_id";
    $stmt = $db->prepare($device_sql);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $devices = $stmt->execute();
    ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex flex-column">
                    <p class="fs-2 fw-bold p-0 m-0">Profile</p>
                    <p class="fs-5 fw-semibold p-0 m-0 text-secondary">See profile</p>
                </div>
            </div>
            <hr/>
            <!-- Profile Section -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p style="height: 50px; width: 50px;" class="bg-dark rounded-circle d-flex justify-content-center align-items-center text-center text-white fs-4 fw-semibold m-0 p-0">
                                    <?php echo substr(htmlspecialchars($user['user_nickname']), 0, 1); ?>
                                </p>
                            </div>
                            <div class="ms-4">
                                <h2 class="mb-0">
                                    <?php echo htmlspecialchars($user['user_nickname']); ?>
                                </h2>
                                <p class="text-muted mb-0">
                                    <?php echo htmlspecialchars($user['user_email']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form to Modify User Information -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if ($auth_user['user_auth_level'] === 2) echo '<h4 class="card-title">Edit Profile</h4>'; ?>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nickname" class="form-label">Nickname</label>
                                <input type="text" class="form-control" id="nickname" name="nickname"
                                    value="<?php echo htmlspecialchars($user['user_nickname']); ?>"
                                    <?php if ($auth_user['user_auth_level'] !== 2) echo 'readonly'; ?>
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo htmlspecialchars($user['user_email']); ?>"
                                    <?php if ($auth_user['user_auth_level'] !== 2) echo 'readonly'; ?>
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?php echo htmlspecialchars($user['user_name']); ?>"
                                    <?php if ($auth_user['user_auth_level'] !== 2) echo 'readonly'; ?>
                                    required>
                            </div>
                            
                            <?php if ($auth_user['user_auth_level'] === 2) { ?>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <div id="passwordHelp" class="form-text">Leave blank if you do not want to change the password.</div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            <?php } ?>
                            
                        </form>
                    </div>
                </div>
            </div>

            <!-- User Devices Section -->
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">User Devices</h4>
                        <div class="list-group">
                            <?php while ($device = $devices->fetchArray(SQLITE3_ASSOC)) { ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($device['crypto_device_name']); ?></h5>
                                    </div>
                                    <img src="data:image/jpeg;base64,<?php echo $device['crypto_device_image_name'] ?>" alt="Device Image" class="rounded" style="width: 75px; height: 75px; object-fit: cover;">
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Include Bootstrap JS for interactivity -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
