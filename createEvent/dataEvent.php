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
                $eventId = isset($_GET['event_id']) ? $_GET['event_id'] : null;

                if ($action === 'create') {
                    echo '<p class="fs-2 fw-bold p-0">Create EVENT</p>';
                } elseif ($action === 'update') {
                    echo '<p class="fs-2 fw-bold p-0">Edit EVENT</p>';
                } else {
                    // Handle other actions or display an error message
                    echo '<p class="fs-2 fw-bold p-0">Invalid Action</p>';
                }
                    // If the action is "update", fetch the event data from the database
    if ($action === 'update') {
        $stmt = $db->prepare('SELECT * FROM Event WHERE event_id = :event_id');
        $stmt->bindValue(':event_id', $eventId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
    }
                ?>
            </div>
            <hr />
            <form id="eventForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?> " method="POST">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <?php if ($action === 'update'): ?>
                    <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                <?php endif; ?>

                <div class="gap-y-2 col-lg-4 col-sm-12">
                    <div class="mb-3">
                        <label for="exampleFormControlInput1" class="form-label">Event Name</label>
                        <input type="text" name="event_name" class="form-control" id="exampleFormControlInput1"
                            placeholder="Enter the name of the event"
                            <?php if ($action === 'update'): ?>
                                value="<?php echo htmlspecialchars($row['event_name']); ?>"
                            <?php endif; ?> required >
                    </div>
                    <div class="mb-3">
                        <label for="exampleFormControlInput2" class="form-label">Event Date</label>
                        <input type="date" name="event_date" class="form-control" id="exampleFormControlInput2"
                            <?php if ($action === 'update'): ?>
                                value="<?php echo htmlspecialchars($row['event_date']); ?>"
                            <?php endif; ?> required >
                    </div>
                    <div class="mb-3">
                        <label for="exampleFormControlInput3" class="form-label">Event venue</label>
                        <input type="text" name="event_venue" class="form-control" id="exampleFormControlInput3"
                            <?php if ($action === 'update'): ?>
                                value="<?php echo htmlspecialchars($row['event_venue']); ?>"
                            <?php endif; ?> required >
                    </div>
                    <div class="d-flex justify-content-end align-items-end w-100" >
                        <button  type="submit" class=" btn btn-lg btn-outline-dark">
                            <?php if ($action === 'create'): ?>
                                Create
                            <?php else: ?>
                                Update
                            <?php endif; ?>
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
        $eventName = filter_var($_POST['event_name'], FILTER_SANITIZE_STRING);
        $eventDate = filter_var($_POST['event_date'], FILTER_SANITIZE_STRING);
        $eventVenue = filter_var($_POST['event_venue'], FILTER_SANITIZE_STRING);

        if ($action === 'create') {
            // Prepare and execute the SQL statement to create a new event
            $stmt = $db->prepare('INSERT INTO Event (event_name, event_date, event_venue, event_creator_id) VALUES (:event_name, :event_date, :event_venue, :event_creator_id)');
            $stmt->bindValue(':event_name', $eventName, SQLITE3_TEXT);
            $stmt->bindValue(':event_date', $eventDate, SQLITE3_TEXT);
            $stmt->bindValue(':event_venue', $eventVenue, SQLITE3_TEXT);
            $stmt->bindValue(':event_creator_id', $userId, SQLITE3_INTEGER);

            if ($stmt->execute()) {
                // Success
                echo '<script>alert("Event created successfully!");</script>';
                echo "<script>window.location.href = 'createEvent.php';</script>";
                exit;
            } else {
                // Error
                echo '<script>alert("Error creating event. Please try again.");</script>';
            }
        } elseif ($action === 'update') {
            $eventId = $_POST['event_id'];

            // Prepare and execute the SQL statement to update the event
            $stmt = $db->prepare('UPDATE Event SET event_name = :event_name, event_date = :event_date, event_venue = :event_venue WHERE event_id = :event_id');
            $stmt->bindValue(':event_name', $eventName, SQLITE3_TEXT);
            $stmt->bindValue(':event_date', $eventDate, SQLITE3_TEXT);
            $stmt->bindValue(':event_venue', $eventVenue, SQLITE3_TEXT);
            $stmt->bindValue(':event_id', $eventId, SQLITE3_INTEGER);

            if ($stmt->execute()) {
                // Success
                echo '<script>alert("Event updated successfully!");</script>';
                echo "<script>window.location.href = 'createEvent.php';</script>";
                exit;
            } else {
                // Error
                echo '<script>alert("Error updating event. Please try again.");</script>';
            }
        }
    }


    ?>

</body>
</html>
