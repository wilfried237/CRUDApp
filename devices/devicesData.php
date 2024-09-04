<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<?php include_once '../component/navbar/navbar.php'; ?>

<body>

    <div class="d-flex flex-col">
        <div class="gap-y-4 p-4 w-100">
            <div class="d-flex align-items-center">
                <?php
                // Check if the action is "create" or "update"
                $action = isset($_GET['action']) ? $_GET['action'] : 'create';
                $deviceId = isset($_GET['device_id']) ? $_GET['device_id'] : null;

                if ($action === 'create') {
                    echo '<p class="fs-2 fw-bold p- m-0">Create Device</p>';
                } elseif ($action === 'update') {
                    echo '<p class="fs-2 fw-bold p-0 m-0">Edit Device</p>';
                } else {
                    // Handle other actions or display an error message
                    echo '<p class="fs-2 fw-bold p-0">Invalid Action</p>';
                }

                // If the action is "update", fetch the device data from the database
                if ($action === 'update') {
                    $stmt = $db->prepare('SELECT * FROM Crypto_Device WHERE crypto_device_id = :device_id');
                    $stmt->bindValue(':device_id', $deviceId, SQLITE3_INTEGER);
                    $result = $stmt->execute();
                    if ($result) {
                        $row = $result->fetchArray(SQLITE3_ASSOC);
                        
                        // Check if a row was actually returned
                        if ($row) {
                            // Now safely access the array offsets
                            $deviceName = $row['crypto_device_name'];
                            $deviceImage = $row['crypto_device_image_name'];
                            $deviceVisible = $row['crypto_device_record_visible'];
                        } else {
                            // No row was returned, handle the empty result
                            echo '<script>alert("No device found with the given ID.");</script>';
                        }
                    } else {
                        // Query execution failed, handle the error
                        echo '<script>alert("Error executing query.");</script>';
                    }
                }
                ?>
            </div>
            <hr />
            <form id="deviceForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <?php if ($action === 'update'): ?>
                    <input type="hidden" name="device_id" value="<?php echo $deviceId; ?>">
                <?php endif; ?>

                <div class="gap-y-2 col-lg-4 col-sm-12">
                    <div class="mb-3">
                        <label for="deviceName" class="form-label">Device Name</label>
                        <input type="text" name="crypto_device_name" class="form-control" id="deviceName"
                            placeholder="Enter the name of the Device"
                            value="<?php echo $action === 'update' ? htmlspecialchars($deviceName) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="deviceImage" class="form-label">Device Image</label>
                        
                        <?php if ($action === 'update' && !empty($deviceImage)): ?>
                            <div class="d-flex flex-column w-100 my-2 gap-2">
                                <p class="m-0 fw-semibold">Current Image:</p>
                                <img class="rounded" src="data:image/jpeg;base64,<?php echo htmlspecialchars($deviceImage); ?>" alt="Device Image" style="max-width: 75px;">
                            </div>
                        <?php endif; ?>

                        <input type="file" name="crypto_device_image" class="form-control" id="deviceImage" accept="image/*" 
                            <?php echo $action === 'create' ? 'required' : ''; ?>>
                    </div>

                    <div class="mb-3">
                        <label for="deviceVisible" class="form-label">Is Visible</label>
                        <select name="crypto_device_record_visible" class="form-control" id="deviceVisible" required>
                            <option value="1" <?php echo $action === 'update' && $deviceVisible == 1 ? 'selected' : ''; ?>>Yes</option>
                            <option value="0" <?php echo $action === 'update' && $deviceVisible == 0 ? 'selected' : ''; ?>>No</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end align-items-end w-100">
                        <button type="submit" class="btn btn-lg btn-outline-dark">
                            <?php echo $action === 'create' ? 'Create' : 'Update'; ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'];
        $userId = $_SESSION['user_id'];

        // Sanitize input
        $deviceName = filter_var($_POST['crypto_device_name'], FILTER_SANITIZE_STRING);
        $deviceVisible = filter_var($_POST['crypto_device_record_visible'], FILTER_SANITIZE_NUMBER_INT);

        // Handle image upload and Base64 conversion
        $deviceImageBase64 = null;
        if (isset($_FILES['crypto_device_image']) && $_FILES['crypto_device_image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['crypto_device_image']['tmp_name']);
            $deviceImageBase64 = base64_encode($imageData);
        } elseif ($action === 'update') {
            // If no new image is uploaded, use the existing image from the database
            $deviceId = $_POST['device_id'];
            $stmt = $db->prepare('SELECT crypto_device_image_name FROM Crypto_Device WHERE crypto_device_id = :device_id');
            $stmt->bindValue(':device_id', $deviceId, SQLITE3_INTEGER);
            $result = $stmt->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);
            
            if ($row) {
                $deviceImageBase64 = $row['crypto_device_image_name'];
            } else {
                echo '<script>alert("Error fetching current image. Please try again.");</script>';
                exit;
            }
        }

        if ($action === 'create') {
            // Prepare and execute the SQL statement to create a new device
            $stmt = $db->prepare('INSERT INTO Crypto_Device (fk_user_id, crypto_device_name, crypto_device_image_name, crypto_device_record_visible) VALUES (:user_id, :device_name, :device_image_base64, :device_visible)');
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $stmt->bindValue(':device_name', $deviceName, SQLITE3_TEXT);
            $stmt->bindValue(':device_image_base64', $deviceImageBase64, SQLITE3_TEXT);
            $stmt->bindValue(':device_visible', $deviceVisible, SQLITE3_INTEGER);

            if ($stmt->execute()) {
                // Success
                echo '<script>alert("Device created successfully!");</script>';
                echo "<script>window.location.href = 'devices.php';</script>";
                exit;
            } else {
                // Error
                echo '<script>alert("Error creating device. Please try again.");</script>';
            }
        } elseif ($action === 'update') {
            $deviceId = $_POST['device_id'];

            // Check if the necessary data exists in the POST request
            if (!isset($_POST['crypto_device_name'], $_POST['crypto_device_record_visible'])) {
                echo '<script>alert("Missing required fields.");</script>';
                exit;
            }

            // Sanitize and assign the input values
            $deviceName = filter_var($_POST['crypto_device_name'], FILTER_SANITIZE_STRING);
            $deviceVisible = filter_var($_POST['crypto_device_record_visible'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($deviceVisible === null) {
                echo '<script>alert("Invalid visibility value.");</script>';
                exit;
            }

            // Prepare and execute the SQL statement to update the device
            $stmt = $db->prepare('UPDATE Crypto_Device SET crypto_device_name = :device_name, crypto_device_image_name = :device_image_base64, crypto_device_record_visible = :device_visible WHERE crypto_device_id = :device_id');
            $stmt->bindValue(':device_name', $deviceName, SQLITE3_TEXT);
            $stmt->bindValue(':device_image_base64', $deviceImageBase64, SQLITE3_TEXT);
            $stmt->bindValue(':device_visible', $deviceVisible, SQLITE3_INTEGER);
            $stmt->bindValue(':device_id', $deviceId, SQLITE3_INTEGER);

            $result = $stmt->execute();

            if ($result) {
                // Success
                echo '<script>alert("Device updated successfully!");</script>';
                echo "<script>window.location.href = 'devices.php';</script>";
                exit;
            } else {
                // Error
                echo '<script>alert("Error updating device. Please try again.");</script>';
            }
        }
    }
    ?>

</body>

</html>
