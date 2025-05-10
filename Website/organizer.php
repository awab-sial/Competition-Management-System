<?php
session_start();

// Check if the page is accessed through proper authentication
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'index.php') === false) {
    // Redirect to the login page if accessed directly or from an unauthorized source
    header('Location: index.php');
    exit();
}


// Check if the user is logged in and has the invigilator role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
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

// Fetch organizer ID related to the logged-in person ID
$loggedInPersonID = $_SESSION['user_id'];
$getOrganizerIDQuery = "SELECT organizer_id FROM Organizer WHERE person_id = $loggedInPersonID";
$organizerIDResult = $conn->query($getOrganizerIDQuery);

// Check if a valid organizer ID is found
if ($organizerIDResult->num_rows == 1) {
    $row = $organizerIDResult->fetch_assoc();
    $organizerID = $row['organizer_id'];

    // Fetch current date and time
    $currentDateTime = date('Y-m-d H:i:s');

    // Fetch upcoming competitions created by the current organizer
    $upcomingCompetitionsQuery = "SELECT event_id, name, DATE_FORMAT(date, '%d-%b-%Y') AS start_date, DATE_FORMAT(date, '%d-%b-%Y') AS end_date, status, CONCAT(date, ' ', starting_time) AS start_datetime, CONCAT(date, ' ', ending_time) AS end_datetime 
                                  FROM Competition 
                                  WHERE organizer_id = $organizerID 
                                  AND CONCAT(date, ' ', starting_time) > '$currentDateTime' 
                                  ORDER BY date ASC";
    $upcomingCompetitionsResult = $conn->query($upcomingCompetitionsQuery);

    // Fetch previous competitions created by the current organizer
    $previousCompetitionsQuery = "SELECT DATE_FORMAT(date, '%d-%b-%Y') AS date, name, event_id 
                                  FROM Competition 
                                  WHERE organizer_id = $organizerID 
                                  AND CONCAT(date, ' ', ending_time) <= '$currentDateTime' 
                                  ORDER BY date DESC";
    $previousCompetitionsResult = $conn->query($previousCompetitionsQuery);
} else {
    // If no valid organizer ID is found, redirect the user
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
    <link rel="stylesheet" href="./organizer_dash.css">
</head>
<body>
    <header>
        <div class="navbar">
            <span class="logo">Competition Management System</span>
            <nav>
                <a href="#">Home</a>
                <a href="#">Competitions</a>
                <a href="#">Contact</a>
                <a href="#">Updates</a>
                <a href="#">About Us</a>
                <a href="#">Help</a>
                <button id="logout-button">Logout</button>
            </nav>
        </div>
    </header>

    <main>
        <section>
            <h1 style="text-align: center;color: whitesmoke;">Organizer Dashboard</h1>
            <a href="./organizer_create_new.php?organizer_id=<?php echo $organizerID; ?>" id="create-new-link">
                <button id="create-new-btn">Create New</button></a>
            <h1>Upcoming Competitions</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Competition Title</th>
                            <th>Details</th>
                            <th>Starting Date</th>
                            <th>Ending Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="upcoming-competitions">
                        <?php if ($upcomingCompetitionsResult->num_rows > 0): ?>
                            <?php $index = 1; while($row = $upcomingCompetitionsResult->fetch_assoc()): ?>
                                <?php 
                                    $status = '';
                                    if ($currentDateTime < $row['start_datetime']) {
                                        $status = 'Upcoming';
                                    } elseif ($currentDateTime >= $row['start_datetime'] && $currentDateTime <= $row['end_datetime']) {
                                        $status = 'Ongoing';
                                    } elseif ($currentDateTime > $row['end_datetime']) {
                                        $status = 'Ended';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $index++; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><button onclick="navigateToDetails(<?php echo $row['event_id']; ?>)">Details</button></td>
                                    <td><?php echo $row['start_date']; ?></td>
                                    <td><?php echo $row['end_date']; ?></td>
                                    <td><?php echo $status; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No upcoming competitions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h1>Previous Competitions</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Competition Name</th>
                            <th>Competition ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="previous-competitions">
                        <?php if ($previousCompetitionsResult->num_rows > 0): ?>
                            <?php while($row = $previousCompetitionsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['date']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo $row['event_id']; ?></td>
                                    <td><button onclick="showResults()">Result</button></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No previous competitions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="previous-results" class="hidden">
            <h1>Previous Results</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>S.no</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Prized (Yes/No)</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody id="previous-results-body">
                        <!-- Dynamically generated result rows -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle navigate to details
            window.navigateToDetails = function(compId) {
                // Navigate to competition details
                window.location.href = "organizer_details.php?compId=" + compId;
            };

            // Handle show results
            window.showResults = function() {
                const resultsSection = document.getElementById('previous-results');
                resultsSection.classList.toggle('hidden');
            };

            document.getElementById('logout-button').addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'index.php';
                }
            });
        });
    </script>
</body>
</html>
