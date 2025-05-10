<?php
// Include the database connection file
include ("database.php");


session_start();

// Check if the page is accessed through proper authentication
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'admin_dash.php') === false) {
    // Redirect to the login page if accessed directly or from an unauthorized source
    header('Location: index.php');
    exit();
}


// Check if the user is logged in and has the invigilator role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the login page if not logged in or not an invigilator
    header('Location: index.php');
    exit();
}

// Check if logout is requested
if (isset($_GET['logout'])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to the login page after logout
    header('Location: index.php');
    exit();
}
// Check if event_id is set in the URL
if (isset($_GET['event_id'])) {
    // Get the event_id from the URL
    $event_id = $_GET['event_id'];

    // Fetch competition details from the database
    $stmt = $conn->prepare("SELECT * FROM Competition WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if competition exists
    if ($result->num_rows > 0) {
        $competition = $result->fetch_assoc();
    } else {
        // Redirect to admin dashboard if competition not found
        header("Location: admin_dash.html");
        exit();
    }


    $attendee_query = "SELECT * FROM Attendees WHERE event_id = $event_id";
    $attendee_result = $conn->query($attendee_query);
    $attendee_count = $attendee_result->num_rows;


} else {
    // Redirect to admin dashboard if event_id is not set
    header("Location: admin_dash.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./admin_details.css">
</head>
<body>
    <header>
        <div class="navbar">
            <span class="logo">Competition Management System</span>
            <nav>
                <a href="./admin_dash.html">Home</a>
                <a href="#">Competitions</a>
                <a href="#">Contact</a>
                <a href="#">Updates</a>
                <a href="#">About Us</a>
                <a href="#">Help</a>
                <button id="logout-button"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </nav>
        </div>
    </header>

    <main>
        <section>
            <h1>Competition Details</h1>
            <div class="competition-info">
                <div>
                    <label for="competition-name"><i class="fas fa-trophy" style="color: gold;"></i> Name:</label>
                    <span id="competition-name"><?= $competition['name'] ?></span>
                </div>
                <div>
                    <label for="competition-date"><i class="fas fa-calendar-alt" style="color: #2c2424;"></i> Date:</label>
                    <span id="competition-date"><?= $competition['date'] ?></span>
                </div>
                <div>
                    <label for="competition-venue"><i class="fas fa-map-marker-alt" style="color: #d9534f;"></i> Venue:</label>
                    <span id="competition-venue"><?= $competition['venue'] ?></span>
                </div>
                <div>
                    <label for="competition-rules"><i class="fas fa-file-alt" style="color: #5bc0de;"></i> Set of Rules:</label>
                    <span id="competition-rules"><?= $competition['rules'] ?></span>
                </div>
                <div>
                    <label for="competition-rules"><i class="fas fa-file-alt" style="color: #5bc0de;"></i> Attendees Person_ID's:</label>
                    <?php foreach ($attendee_result as $attendee) { ?>
                        <span id="competition-rules"><?= $attendee['person_id'] ?></span>
                    <?php } ?>
                </div>
                <!-- Add more details as needed -->
            </div>
        </section>
    </main>

    <script>
        document.getElementById('logout-button').addEventListener('click', function() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'index.php';
            }
        });
    </script>
</body>
</html>
