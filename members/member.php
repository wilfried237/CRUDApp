<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php include_once '../component/navbar/navbar.php'; 
 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['user_id'] ?? '';

    if ($action === 'delete' && $userId) {
        $stmt = $db->prepare('DELETE FROM Registered_User WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            echo 'success';
            exit;
        } else {
            echo 'error';
            exit;
        }
    } elseif ($action === 'promote' && $userId) {
        $stmt = $db->prepare('UPDATE Registered_User SET user_auth_level = 2 WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            echo 'success';
            exit;
        } else {
            echo 'error';
            exit;
        }
    }
}
    ?>

    
    <div class="d-flex flex-col">
        <div class="gap-x-2 p-4 w-100">
            <div class="d-flex align-items-center">
            <div class="d-flex flex-column">
                    <p class="fs-2 fw-bold p-0 m-0">Members</p>
                    <p class="fs-5 fw-semibold p-0 m-0 text-secondary">Manage your Members</p>
                </div>
            </div>
            <hr />
            <?php
            // Get the user ID from the session
            $userId = $_SESSION['user_id'];

            // Fetch events created by the user
            $stmt = $db->prepare('SELECT * FROM Registered_User WHERE user_id != :user_id');
            $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
            $result = $stmt->execute();

            // Check if any events were found
            if ($result->fetchArray(SQLITE3_ASSOC)) {
                // Display the table with events
                echo '<table class="table table-hover mt-5">';
                echo '<thead>';
                echo '<tr>';
                echo '<th scope="col">#</th>';
                echo '<th scope="col">User Name</th>';
                echo '<th scope="col">User Email</th>';
                echo '<th scope="col">User Nickname</th>';
                echo '<th scope="col">User Devices</th>';
                echo '<th scope="col">Created At</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                // Reset the result pointer
                $result->reset();

                // Loop through the events and display them in the table
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    echo '<tr>';
                    echo '<th scope="row">' . $row['user_id'] . '</th>';
                    echo '<td>' . $row['user_name'] . '</td>';
                    echo '<td>' . $row['user_email'] . '</td>';
                    echo '<td>' . $row['user_nickname'] . '</td>';
                    echo '<td>' . $row['user_device_count'] . '</td>';
                    echo '<td>' . $row['user_registered_timestamp'] . '</td>';
                    echo '<td> <div class="dropdown"> <a class="text-decoration-none text-dark dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
  <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
</svg>
  </a>

  <ul class="dropdown-menu">
    <li><a class="dropdown-item text-warning" href="../profile/profiles.php?member_id='.$row["user_id"].'">Update</a></li>
    <li><a class="dropdown-item text-success" href="#" onclick="promoteMember(' . htmlspecialchars($row['user_id']) . ')">Promote</a></li>
    <li><a class="dropdown-item text-danger" href="#" onclick="deleteMember(' . htmlspecialchars($row['user_id']) . ')">Delete</a></li>
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
        function deleteMember(user_id) {
            if (confirm("Are you sure you want to delete this member?")) {
                fetch('member.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=delete&user_id=' + user_id
                })
                .then(response => response.text())
                .then(result => {
                    if (result === 'success') {
                        alert("Member deleted successfully!");
                        location.reload(); // Refresh the page to reflect changes
                    } else {
                        alert("Error deleting member. Please try again.");
                    }
                })
                .catch(error => {
                    alert("Error deleting member. Please try again.");
                });
            }
        }
        function promoteMember(user_id) {
            fetch('member.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=promote&user_id=' + user_id
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'success') {
                    alert("Member promoted successfully!");
                    location.reload(); // Refresh the page to reflect changes
                } else {
                    alert("Error promoting member. Please try again.");
                }
            })
            .catch(error => {
                alert("Error promoting member. Please try again.");
            });
        }

    </script>
</body>

</html>