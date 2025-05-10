<?php
include 'database.php'; // Database connection

// Check if user is logged in
session_start();

// Check if the page is accessed through proper authentication

// Check if the user is logged in and has the invigilator role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'contestant') {
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

$user_id = $_SESSION['user_id']; // Retrieve user_id from session

// Fetch upcoming competitions
$upcomingStmt = $conn->query("SELECT * FROM Competition WHERE date >= CURRENT_DATE ORDER BY date ASC");
$upcomingCompetitions = $upcomingStmt->fetch_all(MYSQLI_ASSOC);

// Fetch previous competitions where the user was registered
$previousStmt = $conn->prepare("
    SELECT c.*
    FROM Competition c
    JOIN Attendees a ON c.event_id = a.event_id
    WHERE c.date < CURRENT_DATE AND a.person_id = ?
    ORDER BY c.date DESC
");
$previousStmt->bind_param("i", $user_id);
$previousStmt->execute();
$previousCompetitions = $previousStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch previous results
$resultsStmt = $conn->query("
    SELECT r.*, c.date, p1.f_name AS winner_fname, p1.l_name AS winner_lname, p2.f_name AS runnerup_fname, p2.l_name AS runnerup_lname
    FROM Result r
    JOIN Competition c ON r.event_id = c.event_id
    LEFT JOIN Person p1 ON r.winner_id = p1.person_id
    LEFT JOIN Person p2 ON r.runner_up_id = p2.person_id
    ORDER BY r.result_id ASC
");
$previousResults = $resultsStmt->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // Check if already registered
    $registerStmt = $conn->prepare("SELECT * FROM Attendees WHERE event_id = ? AND person_id = ?");
    $registerStmt->bind_param("ii", $event_id, $user_id);
    $registerStmt->execute();
    $result = $registerStmt->get_result();

    if ($result->num_rows > 0) {
        $message = "Already registered";
        echo "<script type='text/javascript'>alert('$message');</script>";
    } else {
        // Register for the competition
        $registerStmt = $conn->prepare("INSERT INTO Attendees (event_id, person_id) VALUES (?, ?)");
        $registerStmt->bind_param("ii", $event_id, $user_id);
        $registerStmt->execute();
        $message = "Successfully registered";
        echo "<script type='text/javascript'>alert('$message');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Management System</title>
    <link rel="stylesheet" href="./contestant.css">
</head>
<body>

<nav>
    <div>
        <span>Competition Management System</span>
    </div>
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="#">Competitions</a></li>
        <li><a href="#">Contact</a></li>
        <li><a href="#">Updates</a></li>
        <li><a href="#">About</a></li>
        <li><a href="#">Help</a></li>
    </ul>
    <button onclick="confirmLogout()">Logout</button>
</nav>

<section>
    <h2>Upcoming Competitions</h2>
    <table id="upcoming-competitions">
        <tr>
            <th>Competition Title</th>
            <th>Venue</th>
            <th>Date</th>
            <th>Starting Time</th>
            <th>Ending Time</th>
            <th>Registration</th>
        </tr>
        <?php foreach ($upcomingCompetitions as $competition): ?>
        <tr>
            <td><?= htmlspecialchars($competition['name']) ?></td>
            <td><?= htmlspecialchars($competition['venue']) ?></td>
            <td><?= htmlspecialchars($competition['date']) ?></td>
            <td><?= htmlspecialchars($competition['starting_time']) ?></td>
            <td><?= htmlspecialchars($competition['ending_time']) ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($competition['event_id']) ?>">
                    <button type="submit">Register</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>

<section id="about">
    <h2>About <span id="competition-name">Competition Name</span></h2>
    <p><?= htmlspecialchars($competition['description']) ?></p>
    <button onclick="registerCompetition()">Register Now</button>
</section>

<div class="separator"></div>

<section>
    <h2>Previous Competitions</h2>
    <table id="previous-competitions">
        <tr>
            <th>Date</th>
            <th>Competition Name</th>
            <th>Competition ID</th>
        </tr>
        <?php foreach ($previousCompetitions as $competition): ?>
        <tr>
            <td><?= htmlspecialchars($competition['date']) ?></td>
            <td><?= htmlspecialchars($competition['name']) ?></td>
            <td><?= htmlspecialchars($competition['event_id']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <button onclick="viewPreviousResults()">View Previous Results</button>
</section>

<section id="previous-results" style="display: none;">
    <h2>Previous Results</h2>
    <table>
        <tr>
            <th>S.no</th>
            <th>Date</th>
            <th>Winner</th>
            <th>Runner-up</th>
        </tr>
        <?php foreach ($previousResults as $result): ?>
        <tr>
            <td><?= htmlspecialchars($result['result_id']) ?></td>
            <td><?= htmlspecialchars($result['date']) ?></td>
            <td><?= htmlspecialchars($result['winner_fname']) . " " . htmlspecialchars($result['winner_lname']) ?></td>
            <td><?= htmlspecialchars($result['runnerup_fname']) . " " . htmlspecialchars($result['runnerup_lname']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>

<script>
function registerCompetition() {
    alert("You have successfully registered for the competition.");
}

function viewPreviousResults() {
    document.getElementById("previous-results").style.display = "block";
    document.getElementById("previous-results").scrollIntoView({ behavior: "smooth" });
}

function confirmLogout() {
    if (confirm("Are you sure you want to logout?")) {
        alert("You have been logged out.");
        window.location.href = 'index.php';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // Load competition data if necessary
});
</script>

</body>
</html>
