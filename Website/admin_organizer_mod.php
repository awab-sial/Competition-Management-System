<?php
include 'database.php';


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

// Fetch organizers from the database
$sql = "SELECT * FROM organizer";
$result = $conn->query($sql);
$organizers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $organizers[] = $row;
    }
}

// Fetch persons from the database for the dropdown
$personSql = "SELECT person_id, f_name, l_name FROM person";
$personResult = $conn->query($personSql);
$persons = [];
if ($personResult->num_rows > 0) {
    while ($row = $personResult->fetch_assoc()) {
        $persons[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Organizers</title>
    <link rel="stylesheet" href="./admin_organizer_mod.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            <h1>Organizers</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Organizer ID</th>
                            <th>Person ID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="organizers-table">
                        <?php foreach ($organizers as $organizer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($organizer['organizer_id']); ?></td>
                                <td><?php echo htmlspecialchars($organizer['person_id']); ?></td>
                                <td><?php echo htmlspecialchars($organizer['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="buttons">
                    <button onclick="showAddOrganizerDialog()" class="add-btn"><i class="fas fa-user-plus"></i> Add Organizer</button>
                    <button onclick="deleteOrganizer()" class="delete-btn"><i class="fas fa-user-minus"></i> Delete Organizer</button>
                </div>
            </div>
        </section>

        <!-- Add Organizer Dialog -->
        <div id="add-organizer-dialog" class="hidden dialog">
            <h2>Add Organizer</h2>
            <form id="add-organizer-form" method="post" action="add_organizer.php">
                <label for="person-id">Person:</label><br>
                <select id="person-id" name="person-id" required>
                    <option value="">Select a person</option>
                    <?php foreach ($persons as $person): ?>
                        <option value="<?php echo htmlspecialchars($person['person_id']); ?>">
                            <?php echo htmlspecialchars($person['f_name'] . ' ' . $person['l_name'] . ' (' . $person['person_id'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>
                <label for="status">Status:</label><br>
                <input type="text" id="status" name="status">
                <div class="dialog-buttons">
                    <button type="submit" class="save-btn"><i class="fas fa-save"></i> Save</button>
                    <button type="button" onclick="hideAddOrganizerDialog()" class="cancel-btn"><i class="fas fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show add organizer dialog
            window.showAddOrganizerDialog = function() {
                document.getElementById('add-organizer-dialog').classList.remove('hidden');
            };

            // Hide add organizer dialog
            window.hideAddOrganizerDialog = function() {
                document.getElementById('add-organizer-dialog').classList.add('hidden');
            };

            // Delete organizer
            window.deleteOrganizer = function() {
                const selectedRow = document.querySelector('#organizers-table tr.selected');
                if (selectedRow) {
                    const id = selectedRow.querySelector('td:nth-child(1)').innerText;
                    fetch(`delete_organizer.php?id=${id}`, { method: 'POST' })
                        .then(response => response.text())
                        .then(result => {
                            if (result === 'success') {
                                selectedRow.remove();
                            } else {
                                alert('Failed to delete organizer.');
                            }
                        });
                } else {
                    alert('Please select an organizer to delete.');
                }
            };

            // Select row
            document.querySelector('#organizers-table').addEventListener('click', function(event) {
                const rows = document.querySelectorAll('#organizers-table tr');
                rows.forEach(row => row.classList.remove('selected'));
                event.target.parentElement.classList.add('selected');
            });

            document.getElementById('logout-button').addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'index.php';
                }
            });
        });
    </script>
</body>
</html>
