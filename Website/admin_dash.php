<?php
include 'database.php'; // Database connection

session_start();

// Check if the page is accessed through proper authentication
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'index.php') === false) {
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

// Fetch organizers
$organizersStmt = $conn->query("SELECT o.*, p.f_name, p.l_name FROM Organizer o JOIN Person p ON o.person_id = p.person_id");
$organizers = $organizersStmt->fetch_all(MYSQLI_ASSOC);

// Fetch upcoming competitions
$upcomingStmt = $conn->query("SELECT * FROM Competition WHERE date >= CURRENT_DATE ORDER BY date ASC");
$upcomingCompetitions = $upcomingStmt->fetch_all(MYSQLI_ASSOC);

// Fetch previous competitions
$previousStmt = $conn->query("SELECT * FROM Competition WHERE date < CURRENT_DATE ORDER BY date DESC");
$previousCompetitions = $previousStmt->fetch_all(MYSQLI_ASSOC);

// Fetch previous results
$resultsStmt = $conn->query("
    SELECT r.*, p.f_name, p.l_name
    FROM Result r
    JOIN Person p ON r.winner_id = p.person_id OR r.runner_up_id = p.person_id
    ORDER BY r.result_id ASC
");
$previousResults = $resultsStmt->fetch_all(MYSQLI_ASSOC);


// Handle adding new organizer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_organizer'])) {
    $person_id = $_POST['person_id'];
    $status = $_POST['status'];

    $addOrganizerStmt = $conn->prepare("INSERT INTO Organizer (person_id, status) VALUES (?, ?)");
    $addOrganizerStmt->bind_param("is", $person_id, $status);
    $addOrganizerStmt->execute();
    header("Location: admin_dashboard.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Management System</title>
    <link rel="stylesheet" href="./admin_dash.css">
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
        <!-- Organizers Section -->
        <section>
            <h1>Organizers</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ORGANIZER Name</th>
                            <th>ORGANIZER ID</th>
                            <th>Person ID</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody id="organizers-table">
                        <?php foreach ($organizers as $organizer): ?>
                        <tr>
                            <td><?= htmlspecialchars($organizer['f_name'] . ' ' . $organizer['l_name']) ?></td>
                            <td><?= htmlspecialchars($organizer['organizer_id']) ?></td>
                            <td><?= htmlspecialchars($organizer['person_id']) ?></td>
                            <td><?= htmlspecialchars($organizer['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button onclick="showAddOrganizerForm()">Modify</button>
        </section>

        <section>
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
                        <?php foreach ($upcomingCompetitions as $index => $competition): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($competition['name']) ?></td>
                            <td><button onclick="navigateToDetails(<?= $competition['event_id'] ?>)">Details</button></td>
                            <td><?= htmlspecialchars($competition['starting_time']) ?></td>
                            <td><?= htmlspecialchars($competition['ending_time']) ?></td>
                            <td><?= htmlspecialchars($competition['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
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
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="previous-competitions">
                        <?php foreach ($previousCompetitions as $competition): ?>
                        <tr>
                            <td><?= htmlspecialchars($competition['date']) ?></td>
                            <td><?= htmlspecialchars($competition['name']) ?></td>
                            <td><?= htmlspecialchars($competition['event_id']) ?></td>
                            <td><button onclick="showResults(<?= $competition['event_id'] ?>)">Result</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="previous-results" style="display: none;">
            <h1>Previous Results</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Event ID</th>
                            <th>Participant Name</th>
                            <th>Result ID</th>
                        </tr>
                    </thead>
                    <tbody id="previous-results-body">
                        <?php foreach ($previousResults as $result): ?>
                        <tr>
                            <td><?= htmlspecialchars($result['event_id']) ?></td>
                            <td><?= htmlspecialchars($result['f_name'] . ' ' . $result['l_name']) ?></td>
                            <td><?= htmlspecialchars($result['result_id']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('logout-button').addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'index.php';
                }
            });
        });

        function navigateToDetails(eventId) {
            // Fetch competition details and redirect to details page
            window.location.href = `admin_details.php?event_id=${eventId}`;
        }

        function showResults(eventId) {
            // Fetch results and display them
            const resultsSection = document.getElementById('previous-results');
            resultsSection.style.display = 'block';
            resultsSection.scrollIntoView({ behavior: "smooth" });
        }

        function showAddOrganizerForm() {
            window.location.href = "admin_organizer_mod.php";
        }
    </script>
</body>
</html>
