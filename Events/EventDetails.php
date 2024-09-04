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
    // Fetch event information (assuming event_id is passed via GET)
    $event_id = $_GET['event_id'];
    $event_sql = "
            SELECT Event.event_name, Event.event_date, Event.event_venue, 
                   Registered_User.user_nickname, Registered_User.user_name
            FROM Event
            JOIN Registered_User ON Event.event_creator_id = Registered_User.user_id
            WHERE Event.event_id = :event_id
        ";
    $stmt = $db->prepare($event_sql);
    $stmt->bindValue(':event_id', $event_id, SQLITE3_INTEGER);
    $event_result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    // Handle device registration
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_with_device'])) {
        $current_user_id = $_SESSION['user_id']; // Replace with actual logged-in user ID
        $selected_device_ids = $_POST['device_ids']; // Array of selected device IDs

        foreach ($selected_device_ids as $device_id) {
            // Check if the device is already registered for the event
            $check_device_sql = "
                SELECT * FROM Event_Device 
                WHERE fk_event_id = :event_id 
                AND fk_device_id = :device_id
            ";
            $stmt = $db->prepare($check_device_sql);
            $stmt->bindValue(':event_id', $event_id, SQLITE3_INTEGER);
            $stmt->bindValue(':device_id', $device_id, SQLITE3_INTEGER);
            $result = $stmt->execute();

            if (!$result->fetchArray()) {
                // If not registered, insert the device into Event_Device table
                $register_device_sql = "
                    INSERT INTO Event_Device (fk_event_id, fk_device_id, fk_user_id)
                    VALUES (:event_id, :device_id, :user_id)
                ";
                $stmt = $db->prepare($register_device_sql);
                $stmt->bindValue(':event_id', $event_id, SQLITE3_INTEGER);
                $stmt->bindValue(':device_id', $device_id, SQLITE3_INTEGER);
                $stmt->bindValue(':user_id', $current_user_id, SQLITE3_INTEGER);
                $stmt->execute();
            }
        }

        // Redirect to refresh the page and show the newly registered devices
        echo '<script>alert("Registered successfully!");</script>';
        echo '<script>window.location.href = window.location.href;</script>';
        exit;
    }

    // Fetch users registered for the event and their devices
    $user_sql = "
        SELECT Registered_User.user_id, Registered_User.user_nickname, Registered_User.user_name,
               Crypto_Device.crypto_device_name, Crypto_Device.crypto_device_image_name
        FROM Event_Device
        JOIN Registered_User ON Event_Device.fk_user_id = Registered_User.user_id
        JOIN Crypto_Device ON Event_Device.fk_device_id = Crypto_Device.crypto_device_id
        WHERE Event_Device.fk_event_id = :event_id
    ";
    $stmt = $db->prepare($user_sql);
    $stmt->bindValue(':event_id', $event_id, SQLITE3_INTEGER);
    $user_results = $stmt->execute();
    ?>

    <div class="d-flex flex-col">
        <div class="gap-y-4 p-4 w-100">
            <div class="d-flex align-items-center">
                <div class="d-flex flex-column">
                    <p class="fs-2 fw-bold p-0 m-0">Event: <?php echo htmlspecialchars($event_result['event_name']); ?></p>
                    <p class="fs-5 fw-semibold p-0 m-0 text-secondary">Have a look on the Event</p>
                </div>
                <div class="ms-auto">
                    <?php
                    $current_user_id = $_SESSION['user_id']; // Replace with actual logged-in user ID
                    $check_registration_sql = "
                    SELECT * FROM Event_Device
                    WHERE fk_user_id = :user_id AND fk_event_id = :event_id
                ";
                    $stmt = $db->prepare($check_registration_sql);
                    $stmt->bindValue(':user_id', $current_user_id, SQLITE3_INTEGER);
                    $stmt->bindValue(':event_id', $event_id, SQLITE3_INTEGER);
                    $registration_result = $stmt->execute();

                    if ($registration_result->fetchArray()) {
                        echo '<button class="btn btn-lg btn-secondary" disabled>Registered</button>';
                    } else {
                        echo '<button class="btn btn-lg btn-dark" type="button" data-bs-toggle="modal" data-bs-target="#deviceModal">Register with Device</button>';
                    }
                    ?>
                </div>
            </div>
            <hr />
            <div class="row">
                <!-- Left Side: Event Information -->
                <div class="col-lg-6 col-md-12 mb-4">
                    <h3><?php echo htmlspecialchars($event_result['event_name']); ?></h3>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($event_result['event_date']); ?></p>
                    <p><strong>Venue:</strong> <?php echo htmlspecialchars($event_result['event_venue']); ?></p>
                    <div class="d-flex justify-content-even align-items-center my-2 gap-2">
                        <p style="height: 50px; width: 50px;" class="bg-dark rounded-circle d-flex justify-content-center align-items-center text-center text-white fs-4 fw-semibold m-0 p-0"> <?php echo htmlspecialchars($event_result['user_name'][0]); ?></p>
                        <p class="m-0 p-0"> <?php echo htmlspecialchars($event_result['user_nickname']) ?></p>
                    </div>
                </div>

                <!-- Right Side: Registered Users and Devices -->
                <div class="col-lg-6 col-md-12">
                    <h4>Registered Users and Devices</h4>
                    <?php
                    $current_user = null;
                    while ($user_row = $user_results->fetchArray(SQLITE3_ASSOC)) {
                        if ($current_user != $user_row['user_id']) {
                            if ($current_user !== null) {
                                echo '</ul></div></div>'; // Close the previous user's div
                            }
                            $current_user = $user_row['user_id'];

                            echo '<div class="mb-3">';
                            echo '    <a href="../profile/profiles.php?member_id='.$user_row["user_id"].'" class="d-flex gap-1 my-2">';
                            echo '        <strong>' . htmlspecialchars($user_row['user_nickname']) . '</strong> <span style="height: 25px; width: 25px;" class="text-underline-none bg-dark rounded-circle d-flex justify-content-center align-items-center text-center text-white fs-6 fw-semibold m-0 p-0" >' . htmlspecialchars($user_row['user_name'][0]) . '</span>';
                            echo '    </a>';
                            echo '    <div id="devices-' . htmlspecialchars($user_row['user_id']) . '">';
                            echo '        <ul class="list-group">';
                        }

                        echo '            <li class="list-group-item d-flex justify-content-between align-items-center">';
                        echo '                <span>' . htmlspecialchars($user_row['crypto_device_name']) . '</span>';
                        echo '                <img src="data:image/jpeg;base64,' . htmlspecialchars($user_row['crypto_device_image_name']) . '" alt="' . htmlspecialchars($user_row['crypto_device_name']) . '" class="img-thumbnail" style="max-width: 50px;">';
                        echo '            </li>';
                        echo '<hr/>';
                    }
                    if ($current_user !== null) {
                        echo '</ul></div></div>'; // Close the last user's div
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Device Selection -->
    <div class="modal fade" id="deviceModal" tabindex="-1" aria-labelledby="deviceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deviceModalLabel">Select Devices</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="deviceSelect">Select Device(s):</label>
                            <select class="form-select" id="deviceSelect" name="device_ids[]" multiple required>
                                <?php
                                $devices_sql = "
                                    SELECT * 
                                    FROM Crypto_Device 
                                    WHERE fk_user_id = :user_id
                                ";
                                $stmt = $db->prepare($devices_sql);
                                $stmt->bindValue(':user_id', $current_user_id, SQLITE3_INTEGER);
                                $devices_results = $stmt->execute();

                                while ($device_row = $devices_results->fetchArray(SQLITE3_ASSOC)) {
                                    if($device_row['crypto_device_record_visible']===1){
                                        echo '<option value="' . htmlspecialchars($device_row['crypto_device_id']) . '">' . htmlspecialchars($device_row['crypto_device_name']) . '</option>';
                                    }else{
                                        continue;
                                    }

                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="register_with_device" class="btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
