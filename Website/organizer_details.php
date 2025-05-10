<?php
include 'database.php'; // include the database connection file


// Function to get available judge IDs
function getAvailableJudgeIds() {
    global $conn;
    $sql = "SELECT judge_id FROM judge";
    $result = $conn->query($sql);
    $judgeIds = [];
    while ($row = $result->fetch_assoc()) {
        $judgeIds[] = $row['judge_id'];
    }
    return $judgeIds;
}

// Function to get available invigilator IDs
function getAvailableInvigilatorIds() {
    global $conn;
    $sql = "SELECT invig_id FROM invigilator";
    $result = $conn->query($sql);
    $invigIds = [];
    while ($row = $result->fetch_assoc()) {
        $invigIds[] = $row['invig_id'];
    }
    return $invigIds;
}

// Function to get available resource IDs
function getAvailableResourceIds() {
    global $conn;
    $sql = "SELECT item_id FROM resource";
    $result = $conn->query($sql);
    $resourceIds = [];
    while ($row = $result->fetch_assoc()) {
        $resourceIds[] = $row['item_id'];
    }
    return $resourceIds;
}
// Function to get competition details from the database
function getCompetitionDetails($compId) {
    global $conn;

    $sql = "SELECT * FROM Competition WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $compId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to update competition details in the database
function updateCompetition($compId, $name, $date, $venue, $rules) {
    global $conn;

    $sql = "UPDATE Competition SET name = ?, date = ?, venue = ?, rules = ? WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $date, $venue, $rules, $compId);
    $stmt->execute();
}

// Function to delete competition from the database
function deleteCompetition($compId) {
    global $conn;


    // Delete related rows from competitioninvigilator table
    $sqlDeleteresource = "DELETE FROM competitionresource WHERE event_id = ?";
    $stmtDeleteresource = $conn->prepare($sqlDeleteresource);
    $stmtDeleteresource->bind_param("i", $compId);
    $stmtDeleteresource->execute();
    $stmtDeleteresource->close();

    // Delete related rows from competitioninvigilator table
    $sqlDeleteInvigilators = "DELETE FROM competitioninvigilator WHERE event_id = ?";
    $stmtDeleteInvigilators = $conn->prepare($sqlDeleteInvigilators);
    $stmtDeleteInvigilators->bind_param("i", $compId);
    $stmtDeleteInvigilators->execute();
    $stmtDeleteInvigilators->close();


    // Delete related rows from competitionjudge table
    $sqlDeletejudge = "DELETE FROM competitionjudge WHERE event_id = ?";
    $stmtDeletejudge = $conn->prepare($sqlDeletejudge);
    $stmtDeletejudge->bind_param("i", $compId);
    $stmtDeletejudge->execute();
    $stmtDeletejudge->close();

    // Delete competition from Competition table
    $sqlDeleteCompetition = "DELETE FROM Competition WHERE event_id = ?";
    $stmtDeleteCompetition = $conn->prepare($sqlDeleteCompetition);
    $stmtDeleteCompetition->bind_param("i", $compId);
    $stmtDeleteCompetition->execute();
    $stmtDeleteCompetition->close();
}


// Function to get judges for the competition
function getJudges($compId) {
    global $conn;

    $sql = "SELECT * FROM competitionjudge WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $compId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


function getJudges_name($compId) {
    global $conn;

    // First query to get the judge_id
    $sql = "SELECT judge_id FROM competitionjudge WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $compId);
    $stmt->execute();
    $result = $stmt->get_result();
    $judgeIds = $result->fetch_all(MYSQLI_ASSOC);

    // Array to store judge names
    $judgeNames = [];

    // Assuming $judgeIds is an array of judge_id values
    foreach ($judgeIds as $judgeId) {
        $judgeId = $judgeId['judge_id'];
        // Second query using the fetched judge_id to get person_id
        $sql2 = "SELECT person_id FROM judge WHERE judge_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $judgeId);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $personId = $result2->fetch_assoc()['person_id'];

        // Third query to get person's first name using person_id
        $sql3 = "SELECT f_name FROM person WHERE person_id = ?";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("i", $personId);
        $stmt3->execute();
        $result3 = $stmt3->get_result();
        $judgeName = $result3->fetch_assoc()['f_name'];

        // Store judge name in the array
        $judgeNames[] = $judgeName;
    }

    return $judgeNames;
}


// Function to get invigilators for the competition
function getInvigilators($compId) {
    global $conn;

    $sql = "SELECT * FROM competitioninvigilator WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $compId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get resources for the competition
function getResources($compId) {
    global $conn;

    $sql = "SELECT * FROM competitionresource WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $compId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

$compId = $_GET['compId'];

// Check if form is submitted to save competition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_competition'])) {
    $name = $_POST['name'];
    $date = $_POST['date'];
    $venue = $_POST['venue'];
    $rules = $_POST['rules'];

    updateCompetition($compId, $name, $date, $venue, $rules);
}

// Check if form is submitted to delete competition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_competition'])) {
    deleteCompetition($compId);
    header('Location: ./organizer.php');
    exit;
}

// Fetch competition details
$competition = getCompetitionDetails($compId);
$judges = getJudges($compId);
$invigilators = getInvigilators($compId);
$resources = getResources($compId);
$availableJudgeIds = getAvailableJudgeIds();
$availableInvigilatorIds = getAvailableInvigilatorIds();
$availableResourceIds = getAvailableResourceIds();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./organizer_details.css">
</head>
<body>
    <nav>
        <div class="logo">Competition Management System</div>
        <ul>
            <li><a href="./organizer.php">Home</a></li>
            <li><a href="#">Competitions</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Updates</a></li>
            <li><a href="#">About Us</a></li>
            <li><a href="#">Help</a></li>
            <li><a href="./login.php" id="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <form id="competition-form" method="post" action="">
            <button class="btn delete-btn" id="delete-competition-btn" name="delete_competition"><i class="fas fa-trash-alt"></i> Delete Contest</button>
            <h1>Competition Details</h1>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($competition['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($competition['date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="venue">Venue</label>
                <input type="text" id="venue" name="venue" value="<?php echo htmlspecialchars($competition['venue']); ?>" required>
            </div>
            <div class="form-group">
                <label for="rules">Rules</label>
                <textarea id="rules" name="rules" required><?php echo htmlspecialchars($competition['rules']); ?></textarea>
            </div>
            <button class="btn save-btn" id="save-competition-btn" name="save_competition"><i class="fas fa-save"></i> Save Competition</button>
        </form>

        <div class="section">
            <h2>Judges</h2>
            <button class="btn" id="add-judge-btn"><i class="fas fa-plus-circle"></i> Add Judge</button>
            <table id="judges-table">
                <thead>
                    <tr>
                        <th>Competition Judge ID</th>
                        <th>Judge ID</th>
                        <th>Event ID</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($judges as $judge): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($judge['comp_judge_id']); ?></td>
                            <td><?php echo htmlspecialchars($judge['judge_id']); ?></td>
                            <td><?php echo htmlspecialchars($judge['event_id']); ?></td>
                            <td><button class="delete-row btn"><i class="fas fa-trash-alt"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Invigilators</h2>
            <button class="btn" id="add-invigilator-btn"><i class="fas fa-plus-circle"></i> Add Invigilator</button>
            <table id="invigilators-table">
                <thead>
                    <tr>
                        <th>Competition Invigilator ID</th>
                        <th>Invigilator ID</th>
                        <th>Event ID</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invigilators as $invigilator): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($invigilator['comp_invig_id']); ?></td>
                            <td><?php echo htmlspecialchars($invigilator['invig_id']); ?></td>
                            <td><?php echo htmlspecialchars($invigilator['event_id']); ?></td>
                            <td><button class="delete-row btn"><i class="fas fa-trash-alt"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Resource Allocation</h2>
            <button class="btn" id="add-resource-btn"><i class="fas fa-plus-circle"></i> Add Resource</button>
            <table id="resources-table">
                <thead>
                    <tr>
                        <th>Item ID</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resources as $resource): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($resource['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($resource['quantity']); ?></td>
                            <td><button class="delete-row btn"><i class="fas fa-trash-alt"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<!-- Add Judge Dialog -->
<div id="add-judge-dialog" class="dialog">
    <div class="dialog-content">
        <span class="close-btn">&times;</span>
        <h2>Register New Judge</h2>
        <form id="add-judge-form" method="post" action="">
            <div class="form-group">
                <label for="judge-name">Judge Name</label>
                <input type="text" id="judge-name" name="judge_name" required>
            </div>
            <div class="form-group">
                <label for="judge-id">Judge ID</label>
                <select id="judge-id" name="judge_id" required>
                    <?php foreach ($availableJudgeIds as $judgeId): ?>
                        <option value="<?php echo $judgeId; ?>"><?php echo $judgeId; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="judge-notes">Notes</label>
                <textarea id="judge-notes" name="judge_notes"></textarea>
            </div>
            <input type="hidden" id="competition_id" name="competition_id" value="<?php echo $compId; ?>">
            <button class="btn" id="save-judge-btn" name="add_judge"><i class="fas fa-check"></i> Add Judge</button>
        </form>
    </div>
</div>

<!-- Add Invigilator Dialog -->
<div id="add-invigilator-dialog" class="dialog">
    <div class="dialog-content">
        <span class="close-btn">&times;</span>
        <h2>Register New Invigilator</h2>
        <form id="add-invigilator-form" method="post" action="">
            <div class="form-group">
                <label for="invigilator-name">Invigilator Name</label>
                <input type="text" id="invigilator-name" name="invigilator_name" required>
            </div>
            <div class="form-group">
                <label for="invigilator-id">Invigilator ID</label>
                <select id="invigilator-id" name="invigilator_id" required>
                    <?php foreach ($availableInvigilatorIds as $invigId): ?>
                        <option value="<?php echo $invigId; ?>"><?php echo $invigId; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="invigilator-notes">Notes</label>
                <textarea id="invigilator-notes" name="invigilator_notes"></textarea>
            </div>
            <input type="hidden" id="competition_id" name="competition_id" value="<?php echo $compId; ?>">
            <button class="btn" id="save-invigilator-btn" name="add_invigilator"><i class="fas fa-check"></i> Add Invigilator</button>
        </form>
    </div>
</div>

<!-- Add Resource Dialog -->
<div id="add-resource-dialog" class="dialog">
    <div class="dialog-content">
        <span class="close-btn">&times;</span>
        <h2>Allocate New Resource</h2>
        <form id="add-resource-form" method="post" action="">
            <div class="form-group">
                <label for="resource-item-id">Item ID</label>
                <select id="resource-item-id" name="resource_item_id" required>
                    <?php foreach ($availableResourceIds as $resId): ?>
                        <option value="<?php echo $resId; ?>"><?php echo $resId; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="resource-quantity">Quantity</label>
                <input type="number" id="resource-quantity" name="resource_quantity" required>
            </div>
            <input type="hidden" id="competition_id" name="competition_id" value="<?php echo $compId; ?>">
            <button class="btn" id="save-resource-btn" name="add_resource"><i class="fas fa-check"></i> Add Resource</button>
        </form>
    </div>
</div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addJudgeBtn = document.getElementById('add-judge-btn');
            const addInvigilatorBtn = document.getElementById('add-invigilator-btn');
            const addResourceBtn = document.getElementById('add-resource-btn');
            const logoutBtn = document.getElementById('logout-btn');
            const deleteCompetitionBtn = document.getElementById('delete-competition-btn');

            const addJudgeDialog = document.getElementById('add-judge-dialog');
            const addInvigilatorDialog = document.getElementById('add-invigilator-dialog');
            const addResourceDialog = document.getElementById('add-resource-dialog');

            const closeButtons = document.querySelectorAll('.close-btn');

            addJudgeBtn.addEventListener('click', () => {
                addJudgeDialog.style.display = 'flex';
            });

            addInvigilatorBtn.addEventListener('click', () => {
                addInvigilatorDialog.style.display = 'flex';
            });

            addResourceBtn.addEventListener('click', () => {
                addResourceDialog.style.display = 'flex';
            });

            closeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    addJudgeDialog.style.display = 'none';
                    addInvigilatorDialog.style.display = 'none';
                    addResourceDialog.style.display = 'none';
                });
            });

            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'login.php';
                }
            });

            deleteCompetitionBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to delete this contest?')) {
                    const form = document.getElementById('competition-form');
                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = 'delete_competition';
                    form.appendChild(deleteInput);
                    form.submit();
                }
            });

            // Add Judge
            document.getElementById('save-judge-btn').addEventListener('click', () => {
                const name = document.getElementById('judge-name').value;
                const id = document.getElementById('judge-id').value;
                const notes = document.getElementById('judge-notes').value;
                const competitionId = document.getElementById('competition_id').value;

                if (name && id) {
                    const table = document.getElementById('judges-table').getElementsByTagName('tbody')[0];
                    const newRow = table.insertRow();
                    newRow.innerHTML = `
                        <td>${name}</td>
                        <td>${id}</td>
                        <td>${notes}</td>
                        <td><button class="btn delete-row"><i class="fas fa-trash-alt"></i></button></td>
                    `;

                    addJudgeDialog.style.display = 'none';
                    document.getElementById('judge-name').value = '';
                    document.getElementById('judge-id').value = '';
                    document.getElementById('judge-notes').value = '';

                    const deleteButtons = document.querySelectorAll('.delete-row');
                    deleteButtons.forEach(button => {
                        button.addEventListener('click', (e) => {
                            e.target.closest('tr').remove();
                        });
                    });
                }
            });

            // Add Invigilator
            document.getElementById('save-invigilator-btn').addEventListener('click', () => {
                const name = document.getElementById('invigilator-name').value;
                const id = document.getElementById('invigilator-id').value;
                const notes = document.getElementById('invigilator-notes').value;
                const competitionId = document.getElementById('competition_id').value;

                if (name && id) {
                    const table = document.getElementById('invigilators-table').getElementsByTagName('tbody')[0];
                    const newRow = table.insertRow();
                    newRow.innerHTML = `
                        <td>${name}</td>
                        <td>${id}</td>
                        <td>${notes}</td>
                        <td><button class="btn delete-row"><i class="fas fa-trash-alt"></i></button></td>
                    `;

                    addInvigilatorDialog.style.display = 'none';
                    document.getElementById('invigilator-name').value = '';
                    document.getElementById('invigilator-id').value = '';
                    document.getElementById('invigilator-notes').value = '';

                    const deleteButtons = document.querySelectorAll('.delete-row');
                    deleteButtons.forEach(button => {
                        button.addEventListener('click', (e) => {
                            e.target.closest('tr').remove();
                        });
                    });
                }
            });

            // Add Resource
            document.getElementById('save-resource-btn').addEventListener('click', () => {
                const itemID = document.getElementById('resource-item-id').value;
                const quantity = document.getElementById('resource-quantity').value;
                const competitionId = document.getElementById('competition_id').value;

                if (itemID && quantity) {
                    const table = document.getElementById('resources-table').getElementsByTagName('tbody')[0];
                    const newRow = table.insertRow();
                    newRow.innerHTML = `
                        <td>${itemID}</td>
                        <td>${itemID}</td>
                        <td>${quantity}</td>
                        <td><button class="btn delete-row"><i class="fas fa-trash-alt"></i></button></td>
                    `;

                    addResourceDialog.style.display = 'none';
                    document.getElementById('resource-item-id').value = '';
                    document.getElementById('resource-quantity').value = '';

                    const deleteButtons = document.querySelectorAll('.delete-row');
                    deleteButtons.forEach(button => {
                        button.addEventListener('click', (e) => {
                            e.target.closest('tr').remove();
                        });
                    });
                }
            });
        });
        const deleteJudgeButtons = document.querySelectorAll('.delete-judge-btn');
        deleteJudgeButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const judgeId = e.target.closest('tr').querySelector('td:nth-child(2)').textContent; // Assuming Judge ID is in the second column
                const competitionId = document.getElementById('competition_id').value;

                if (confirm('Are you sure you want to delete this judge?')) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_judge.php', true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            // Reload the page or update the UI as needed
                            location.reload(); // Reloads the page
                        }
                    };
                    xhr.send(`judge_id=${judgeId}&competition_id=${competitionId}`);
                }
            });
        });
        // Delete Invigilator
        const deleteInvigilatorButtons = document.querySelectorAll('.delete-invigilator-btn');
        deleteInvigilatorButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const invigilatorId = e.target.closest('tr').querySelector('td:nth-child(2)').textContent; // Assuming Invigilator ID is in the second column
                const competitionId = document.getElementById('competition_id').value;

                if (confirm('Are you sure you want to delete this invigilator?')) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_invigilator.php', true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            // Reload the page or update the UI as needed
                            location.reload(); // Reloads the page
                        }
                    };
                    xhr.send(`invigilator_id=${invigilatorId}&competition_id=${competitionId}`);
                }
            });
        });
        // Delete Resource
        const deleteResourceButtons = document.querySelectorAll('.delete-resource-btn');
        deleteResourceButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const resourceId = e.target.closest('tr').querySelector('td:nth-child(2)').textContent; // Assuming Resource ID is in the second column
                const competitionId = document.getElementById('competition_id').value;

                if (confirm('Are you sure you want to delete this resource?')) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'delete_resource.php', true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            // Reload the page or update the UI as needed
                            location.reload(); // Reloads the page
                        }
                    };
                    xhr.send(`resource_id=${resourceId}&competition_id=${competitionId}`);
                }
            });
        });


        </script>
    </body>
</html>

