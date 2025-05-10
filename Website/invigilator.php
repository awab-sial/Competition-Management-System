<?php
session_start();

// Check if the page is accessed through proper authentication
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'index.php') === false) {
    // Redirect to the login page if accessed directly or from an unauthorized source
    header('Location: index.php');
    exit();
}


// Check if the user is logged in and has the invigilator role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'invigilator') {
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
// Include the database connection file
include 'database.php';

// Fetch the logged-in invigilator's person ID
$loggedInPersonID = $_SESSION['user_id'];

// Fetch the invigilator ID based on the person ID
$getInvigilatorIDQuery = "SELECT invig_id FROM Invigilator WHERE person_id = $loggedInPersonID";
$invigilatorIDResult = $conn->query($getInvigilatorIDQuery);

// Check if a valid invigilator ID is found
if ($invigilatorIDResult->num_rows == 1) {
    $row = $invigilatorIDResult->fetch_assoc();
    $invigilatorID = $row['invig_id'];

    // Fetch upcoming competitions assigned to the current invigilator
    $currentDateTime = date('Y-m-d H:i:s');
    $upcomingCompetitionsQuery = "SELECT c.event_id, c.name, DATE_FORMAT(c.date, '%d %b %Y') AS start_date, DATE_FORMAT(c.date, '%d %b %Y') AS end_date, c.starting_time, c.ending_time, c.venue 
                                  FROM Competition c 
                                  INNER JOIN CompetitionInvigilator ci ON c.event_id = ci.event_id 
                                  WHERE ci.invig_id = $invigilatorID 
                                  AND CONCAT(c.date, ' ', c.starting_time) > '$currentDateTime' 
                                  ORDER BY c.date ASC";
    $upcomingCompetitionsResult = $conn->query($upcomingCompetitionsQuery);

    // Fetch previous competitions assigned to the current invigilator
    $previousCompetitionsQuery = "SELECT c.date, c.name, c.event_id 
                                  FROM Competition c 
                                  INNER JOIN CompetitionInvigilator ci ON c.event_id = ci.event_id 
                                  WHERE ci.invig_id = $invigilatorID 
                                  AND CONCAT(c.date, ' ', c.ending_time) <= '$currentDateTime' 
                                  ORDER BY c.date DESC";
    $previousCompetitionsResult = $conn->query($previousCompetitionsQuery);
} else {
    // If no valid invigilator ID is found, redirect the user
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Management System</title>
    <link rel="stylesheet" href="./invigilator.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">Competition Management System</div>
            <nav>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Competitions</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Updates</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Help</a></li>
                    <li><button id="logout-button">Logout</button></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section id="upcoming-competitions">
            <h2>Upcoming Competitions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Competition ID #</th>
                        <th>Competition Title</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($upcomingCompetitionsResult->num_rows > 0): ?>
                        <?php while ($row = $upcomingCompetitionsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['event_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><button onclick="showDetails(<?php echo $row['event_id']; ?>)">Details</button></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No upcoming competitions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section id="competition-details" style="display: none;">
            <h2>Competition Details</h2>
            <div class="details-form">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" readonly>
                <label for="start-date">Starting Date</label>
                <input type="text" id="start-date" name="start-date" readonly>
                <label for="end-date">Ending Date</label>
                <input type="text" id="end-date" name="end-date" readonly>
                <label for="start-time">Start Time</label>
                <input type="text" id="start-time" name="start-time" readonly>
                <label for="end-time">End Time</label>
                <input type="text" id="end-time" name="end-time" readonly>
                <label for="venue">Venue</label>
                <input type="text" id="venue" name="venue" readonly>
            </div>
        </section>

        <section id="previous-competitions">
            <h2>Previous Competitions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Competition Name</th>
                        <th>Competition ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($previousCompetitionsResult->num_rows > 0): ?>
                        <?php while ($row = $previousCompetitionsResult
                        ->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo $row['event_id']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No previous competitions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<script>
    function showDetails(id) {
        // Fetch competition details using AJAX
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    const competition = JSON.parse(xhr.responseText);
                    document.getElementById('name').value = competition.name;
                    document.getElementById('start-date').value = competition.start_date;
                    document.getElementById('end-date').value = competition.end_date;
                    document.getElementById('start-time').value = competition.starting_time;
                    document.getElementById('end-time').value = competition.ending_time;
                    document.getElementById('venue').value = competition.venue;
                    document.getElementById('competition-details').style.display = 'block';
                } else {
                    alert('Error: Unable to fetch competition details');
                }
            }
        };
        xhr.open('GET', 'get_competition_details.php?id=' + id, true);
        xhr.send();
    }

    document.getElementById('logout-button').addEventListener('click', function() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'index.php';
        }
    });
</script>
</body>
</html>
