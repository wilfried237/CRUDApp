<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <?php include_once '../component/navbar/navbar.php';

    ?>
    
    <div class="d-flex flex-col">
        <div class="gap-y-4 p-4 w-100">
            <div class="d-flex align-items-center">
                <div class="d-flex flex-column">
                    <p class="fs-2 fw-bold p-0 m-0">Events</p>
                    <p class="fs-5 fw-semibold p-0 m-0 text-secondary">Have a look on Events and register</p>
                </div>
            </div>
            <hr/>
            <div class="row row-cols-2 row-cols-lg-4 g-2 g-lg-3 g-2 g-lg-3">
                <?php
                    $sql = "    SELECT Event.event_id, Event.event_name, Event.event_date, Event.event_venue, 
                    Registered_User.user_nickname, Registered_User.user_name
             FROM Event
             JOIN Registered_User ON Event.event_creator_id = Registered_User.user_id";

                    $stmt = $db->prepare($sql);
                    $result = $stmt->execute();
                    
                // Check if the query returned any results
                if ($result->fetchArray(SQLITE3_ASSOC)) {
                    $result->reset();
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        // Get the first letter of the user's last name
                        $lastNameInitial = strtoupper($row['user_name'][0]);

                        echo '<div class="col">';
                        echo '    <div class="card">';
                        echo '        <div class="card-body">';
                        echo '            <h5 class="card-title">' . htmlspecialchars($row['event_name']) . '</h5>';
                        echo '            <p class="card-text">Venue: ' . htmlspecialchars($row['event_venue']) . '</p>';
                        echo '            <p class="card-text">Date: ' . htmlspecialchars($row['event_date']) . '</p>';
                        echo '          <div class="d-flex justify-content-even align-items-center my-2 gap-2">
                                            <p style="height: 50px; width: 50px;" class="bg-dark rounded-circle d-flex justify-content-center align-items-center text-center text-white fs-4 fw-semibold m-0 p-0"> ' . $lastNameInitial . '</p>
                                            <p class="m-0 p-0"> ' . htmlspecialchars($row['user_name']) . '</p>
                                        </div>
                                        ';
                        echo '            <a href="EventDetails.php?event_id='.$row["event_id"].'" class="btn btn-primary">See more</a>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No events found.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>
