<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php include_once '../component/navbar/navbar.php'; 
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
        $deviceId = $_POST['crypto_device_id'];

        // Prepare and execute the SQL statement to delete the device
        $stmt = $db->prepare('DELETE FROM Crypto_Device WHERE crypto_device_id = :crypto_device_id');
        $stmt->bindValue(':crypto_device_id', $deviceId, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            // Success
            echo '<script>alert("Device deleted successfully!");</script>';
            // Refresh the page to reflect the changes
        } else {
            // Error
            echo '<script>alert("Error deleting device. Please try again.");</script>';
        }
    }
    ?>

    <div class="d-flex flex-col">
        <div class="gap-x-2 p-4 w-100">
            <div class="d-flex align-items-center">
                <div class="d-flex flex-column">
                    <p class="fs-2 fw-bold p-0 m-0">YOUR DEVICES</p>
                    <p class="fs-5 fw-semibold p-0 m-0 text-secondary">Manage your Devices</p>
                </div>
                
                <div class="ms-auto">
                    <?php
                    // Get the user ID from the session
                    $userId = $_SESSION['user_id'];

                    // Generate the URL with the user ID
                    $addEventUrl = "./devicesData.php?action=create";
                    ?>
                    <a href="<?php echo $addEventUrl; ?>"> <button class="btn btn-lg btn-dark" type="button">Add
                            Device</button> </a>
                </div>
            </div>
            <hr />
            <?php
            // Get the user ID from the session
            $userId = $_SESSION['user_id'];

            // Fetch devices created by the user
            $stmt = $db->prepare('SELECT * FROM Crypto_Device WHERE fk_user_id = :user_id');
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $result = $stmt->execute();

            // Check if any devices were found
            if ($result->fetchArray(SQLITE3_ASSOC)) {
                // Display the table with devices
                echo '<table class="table table-hover mt-5">';
                echo '<thead>';
                echo '<tr>';
                echo '<th scope="col">#</th>';
                echo '<th scope="col">Name</th>';
                echo '<th scope="col">Image</th>';
                echo '<th scope="col">isVisible</th>';
                echo '<th scope="col">Date</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                // Reset the result pointer
                $result->reset();

                // Loop through the devices and display them in the table
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    echo '<tr>';
                    echo '<th scope="row">' . $row['crypto_device_id'] . '</th>';
                    echo '<td>' . htmlspecialchars($row['crypto_device_name']) . '</td>';

                    // Display the Base64 image
                    echo '<td>';
                    if (!empty($row['crypto_device_image_name'])) {
                        echo '<img src="data:image/jpeg;base64,' . $row['crypto_device_image_name'] . '" alt="Device Image" class="rounded" style="width: 75px; height: 75px; object-fit: cover;">';
                    } else {
                        echo 'No Image';
                    }
                    echo '</td>';

                    echo '<td>' . ($row['crypto_device_record_visible'] ? 'Yes' : 'No') . '</td>';
                    echo '<td>' . $row['crypto_device_registered_timestamp'] . '</td>';
                    echo '<td> <div class="dropdown"> <a class="text-decoration-none text-dark dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
  <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
</svg>
  </a>

  <ul class="dropdown-menu">
    <li><a class="dropdown-item text-warning" href="devicesData.php?action=update&device_id='.$row["crypto_device_id"].'">Update</a></li>
    <li><a class="dropdown-item text-danger" href="#" data-device-id="' . $row['crypto_device_id'] . '" onclick="deleteDevice(this)">Delete</a></li>
  </ul>
</div>';
                    echo '
                    </td>
                    ';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
            } else {
                // Display a message if no devices were found
                echo '<p class="mt-5 text-center fs-4 text-secondary">No Devices found.</p>';
            }
            ?>
        </div>
    </div>
    <script>
        function deleteDevice(element) {
            if (confirm("Are you sure you want to delete this device?")) {
                const deviceId = element.dataset.deviceId;
                console.log(deviceId);
                fetch('devices.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=delete&crypto_device_id=' + deviceId
                })
                .then(response => {
                    if (response.ok) {
                        alert("Device deleted successfully!");
                        location.href = 'devices.php';
                    } else {
                        alert("Error deleting device. Please try again.");
                    }
                })
                .catch(error => {
                    alert("Error deleting device. Please try again.");
                });
            }
        }
    </script>
</body>

</html>
