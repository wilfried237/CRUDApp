<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php include_once '../component/navbar/navbar.php'; 
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
        $eventId = $_POST['event_id'];

        // Prepare and execute the SQL statement to delete the event
        $stmt = $db->prepare('DELETE FROM Event WHERE event_id = :event_id');
        $stmt->bindValue(':event_id', $eventId, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            // Success
            echo '<script>alert("Event deleted successfully!");</script>';
            // Refresh the page to reflect the changes
        } else {
            // Error
            echo '<script>alert("Error deleting event. Please try again.");</script>';
        }
    }
    ?>

    
    <div class="d-flex flex-col">
        <div class="gap-x-2 p-4 w-100">
            <div class="d-flex align-items-center">
            <div class="d-flex flex-column">
                    <p class="fs-2 fw-bold p-0 m-0">YOUR Events</p>
                    <p class="fs-5 fw-semibold p-0 m-0 text-secondary">Manage your Events</p>
                </div>
                <div class="ms-auto">
                    <?php
                    // Get the user ID from the session
                    $userId = $_SESSION['user_id'];

                    // Generate the URL with the user ID
                    $addEventUrl = "./dataEvent.php?action=create";
                    ?>
                    <a href="<?php echo $addEventUrl; ?>"> <button class="btn btn-lg btn-dark" type="button">Add
                            Event</button> </a>
                </div>
            </div>
            <hr />
            <?php
            // Get the user ID from the session
            $userId = $_SESSION['user_id'];

            // Fetch events created by the user
            $stmt = $db->prepare('SELECT * FROM Event WHERE event_creator_id = :user_id');
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $result = $stmt->execute();

            // Check if any events were found
            if ($result->fetchArray(SQLITE3_ASSOC)) {
                // Display the table with events
                echo '<table class="table table-hover mt-5">';
                echo '<thead>';
                echo '<tr>';
                echo '<th scope="col">#</th>';
                echo '<th scope="col">Event Name</th>';
                echo '<th scope="col">Date</th>';
                echo '<th scope="col">Venue</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                // Reset the result pointer
                $result->reset();

                // Loop through the events and display them in the table
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    echo '<tr>';
                    echo '<th scope="row">' . $row['event_id'] . '</th>';
                    echo '<td>' . $row['event_name'] . '</td>';
                    echo '<td>' . $row['event_date'] . '</td>';
                    echo '<td>' . $row['event_venue'] . '</td>';
                    echo '<td>' . $row['event_location'] . '</td>';
                    echo '<td> <div class="dropdown"> <a class="text-decoration-none text-dark dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
  <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
</svg>
  </a>

  <ul class="dropdown-menu">
  <li><a class="dropdown-item text-primary" href="../Events/EventDetails.php?event_id='.$row["event_id"].'">See more</a></li>
    <li><a class="dropdown-item text-warning" href="dataEvent.php?action=update&event_id='.$row["event_id"].'">Update</a></li>
    <li><a class="dropdown-item text-danger" href="#" data-event-id="' . $row['event_id'] . '" onclick="deleteEvent(this)">Delete</a></li>
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
                // Display a message if no events were found
                echo '<p class="mt-5 text-center fs-4 text-secondary">No events found.</p>';
            }
            ?>
        </div>
    </div>
    <script>
        function deleteEvent(element) {
            if (confirm("Are you sure you want to delete this event?")) {
                const eventId = element.dataset.eventId;
                fetch('createEvent.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=delete&event_id=' + eventId
                })
                .then(response => {
                    if (response.ok) {
                        alert("Event deleted successfully!");
                        location.href = 'createEvent.php';
                    } else {
                        alert("Error deleting event. Please try again.");
                    }
                })
                .catch(error => {
                    alert("Error deleting event. Please try again.");
                });
            }
        }
    </script>
</body>

</html>