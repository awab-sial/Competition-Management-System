<?php
include 'database.php';

$organizer_id = isset($_GET['organizer_id']) ? $_GET['organizer_id'] : '';

// Initialize variables
$name = $date = $stime = $etime = $venue = $rules = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $date = $_POST['date'];
    $stime = $_POST['stime'];
    $etime = $_POST['etime'];
    $venue = $_POST['venue'];
    $rules = $_POST['rules'];
    $organizer_id = $_POST['organizer_id'];

    // Insert competition into database
    $sql = "INSERT INTO Competition (name, venue, date, starting_time, ending_time, rules, status, organizer_id) 
            VALUES ('$name', '$venue', '$date', '$stime', '$etime', '$rules', 'Active', '$organizer_id')";
    if (mysqli_query($conn, $sql)) {
        $competition_id = mysqli_insert_id($conn);

        // Insert judges
        if (!empty($_POST['judges'])) {
            foreach ($_POST['judges'] as $judge_id) {
                $sql = "INSERT INTO CompetitionJudge (event_id, judge_id) VALUES ($competition_id, $judge_id)";
                mysqli_query($conn, $sql);
            }
        }
        else {
            echo "<script>alert('Please add at least one judge!');</script>";
        }

        // Insert invigilators
        if (!empty($_POST['invigilators'])) {
            foreach ($_POST['invigilators'] as $invigilator_id) {
                $sql = "INSERT INTO CompetitionInvigilator (event_id, invig_id) VALUES ($competition_id, $invigilator_id)";
                mysqli_query($conn, $sql);
            }
        }
        else {
            echo "<script>alert('Please add at least one inv!');</script>";
        }

        // Insert resources
        if (!empty($_POST['resources'])) {
            $resourcesData = json_decode($_POST['resources'], true); // Decode JSON string to array
            if (is_array($resourcesData)) {
                foreach ($resourcesData as $resource) {
                    if (isset($resource['item_id']) && isset($resource['quantity'])) {
                        $item_id = $resource['item_id'];
                        $quantity = $resource['quantity'];
                        $sql = "INSERT INTO CompetitionResource (event_id, item_id, quantity) VALUES ($competition_id, $item_id, $quantity)";
                        mysqli_query($conn, $sql);
                    } else {
                        // Log or handle unexpected data in $resource
                        echo "Invalid resource data: " . json_encode($resource);
                    }
                }
            } else {
                echo "<script>alert('Invalid resource data!');</script>";
            }
        } else {
            echo "<script>alert('Please add at least one resource!');</script>";
        }

        echo "<script>alert('Competition saved successfully!');</script>";
    } else {
        echo "<script>alert('Error saving competition: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./organizer_competition_creation.css">
    <style>
        /* Inline CSS for centering dialogs */
        .dialog-content {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
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
            <li><a href="./index.php" id="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Competition Creation</h1>
        <form id="competition-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="organizer_id" value="<?php echo $organizer_id; ?>">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" required>
            </div>
            <div class="form-group">
                <label for="stime">Starting Time</label>
                <input type="time" id="stime" name="stime" required>
            </div>
            <div class="form-group">
                <label for="etime">Ending Time</label>
                <input type="time" id="etime" name="etime" required>
            </div>
            <div class="form-group">
                <label for="venue">Venue</label>
                <input type="text" id="venue" name="venue" required>
            </div>
            <div class="form-group">
                <label for="rules">Rules</label>
                <textarea id="rules" name="rules" required></textarea>
            </div>

            <!-- Update the HTML form to include proper field names -->
            <div class="section">
                <h2>Judges</h2>
                <button type="button" class="btn" id="add-judge-btn"><i class="fas fa-plus-circle"></i> Add Judge</button>
                <table id="judges-table">
                    <thead>
                        <tr>
                            <th>Judge ID</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Judges will be dynamically added here -->
                    </tbody>
                </table>
                <!-- Add hidden input field to store judges data -->
                <input type="hidden" id="judges-data" name="judges[]" value="">
            </div>

            <div class="section">
                <h2>Invigilators</h2>
                <button type="button" class="btn" id="add-invigilator-btn"><i class="fas fa-plus-circle"></i> Add Invigilator</button>
                <table id="invigilators-table">
                    <thead>
                        <tr>
                            <th>Invigilator ID</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Invigilators will be dynamically added here -->
                    </tbody>
                </table>
                <!-- Add hidden input field to store invigilators data -->
                <input type="hidden" id="invigilators-data" name="invigilators[]" value="">
            </div>

            <div class="section">
                <h2>Resource Allocation</h2>
                <button type="button" class="btn" id="add-resource-btn"><i class="fas fa-plus-circle"></i> Add Resource</button>
                <table id="resources-table">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Quantity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Resources will be dynamically added here -->
                    </tbody>
                </table>
                <!-- Add hidden input field to store resources data -->
                <input type="hidden" id="resources-data" name="resources" value="">

            </div>


            <button type="submit" class="btn save-btn" id="save-competition-btn"><i class="fas fa-save"></i> Save Competition</button>
       
            </form>
    </div>

    <!-- Add Judge Dialog -->
    <div id="add-judge-dialog" class="dialog">
        <div class="dialog-content">
            <span class="close-btn">&times;</span>
            <h2>Add a Judge</h2>
            <div class="form-group">
                <label for="judge-id">Judge ID</label>
                <select id="judge-id" required>
                    <?php
                    // Fetch judges from database
                    $judge_sql = "SELECT * FROM Judge";
                    $judge_result = mysqli_query($conn, $judge_sql);
                    while ($row = mysqli_fetch_assoc($judge_result)) {
                        echo "<option value='" . $row['judge_id'] . "'>" . $row['judge_name'] . " (" . $row['judge_id'] . ")</option>";
                    }
                    ?>
                </select>
            </div>
            <button class="btn" id="save-judge-btn"><i class="fas fa-check"></i> Add Judge</button>
        </div>
    </div>

    <!-- Add Invigilator Dialog -->
    <div id="add-invigilator-dialog" class="dialog">
        <div class="dialog-content">
            <span class="close-btn">&times;</span>
            <h2>Add an Invigilator</h2>
            <div class="form-group">
                <label for="invigilator-id">Invigilator ID</label>
                <select id="invigilator-id" required>
                    <?php
                    // Fetch invigilators from database
                    $invigilator_sql = "SELECT * FROM Invigilator";
                    $invigilator_result = mysqli_query($conn, $invigilator_sql);
                    while ($row = mysqli_fetch_assoc($invigilator_result)) {
                        echo "<option value='" . $row['invig_id'] . "'>" . $row['invig_name'] . " (" . $row['invig_id'] . ")</option>";
                    }
                    ?>
                </select>
            </div>
            <button class="btn" id="save-invigilator-btn"><i class="fas fa-check"></i> Add Invigilator</button>
        </div>
    </div>

    <!-- Add Resource Dialog -->
    <div id="add-resource-dialog" class="dialog">
        <div class="dialog-content">
            <span class="close-btn">&times;</span>
            <h2>Allocate New Resource</h2>
            <div class="form-group">
                <label for="resource-id">Resource ID</label>
                <select id="resource-id" required>
                    <?php
                    // Fetch resources from database
                    $resource_sql = "SELECT * FROM Resource";
                    $resource_result = mysqli_query($conn, $resource_sql);
                    while ($row = mysqli_fetch_assoc($resource_result)) {
                        echo "<option value='" . $row['item_id'] . "'>" . $row['item_name'] . " (" . $row['item_id'] . ")</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="resource-quantity">Quantity</label>
                <input type="number" id="resource-quantity" required>
            </div>
            <button class="btn" id="save-resource-btn"><i class="fas fa-check"></i> Add Resource</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addJudgeBtn = document.getElementById('add-judge-btn');
            const addInvigilatorBtn = document.getElementById('add-invigilator-btn');
            const addResourceBtn = document.getElementById('add-resource-btn');
            const logoutBtn = document.getElementById('logout-btn');

            const addJudgeDialog = document.getElementById('add-judge-dialog');
            const addInvigilatorDialog = document.getElementById('add-invigilator-dialog');
            const addResourceDialog = document.getElementById('add-resource-dialog');

            const closeButtons = document.querySelectorAll('.close-btn');

            addJudgeBtn.addEventListener('click', () => {
            addJudgeDialog.style.display = 'block';
            centerDialog(addJudgeDialog);
        });

        
            addInvigilatorBtn.addEventListener('click', () => {
                addInvigilatorDialog.style.display = 'block';
                centerDialog(addInvigilatorDialog);

            });

            addResourceBtn.addEventListener('click', () => {
                addResourceDialog.style.display = 'block';
                centerDialog(addResourceDialog);

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
                    window.location.href = 'index.html';
                }
            });

            // Function to add Judge
            document.getElementById('save-judge-btn').addEventListener('click', () => {
                const id = document.getElementById('judge-id').value;

                if (id) {
                    const table = document.getElementById('judges-table').getElementsByTagName('tbody')[0];
                    const newRow = table.insertRow();
                    newRow.insertCell(0).textContent = id;
                    const actionCell = newRow.insertCell(1);
                    const deleteBtn = document.createElement('button');
                    deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                    deleteBtn.className = 'btn';
                    deleteBtn.addEventListener('click', () => {
                        table.deleteRow(newRow.rowIndex - 1);
                    });
                    actionCell.appendChild(deleteBtn);

                    addJudgeDialog.style.display = 'none';
                }
            });

            // Function to add Invigilator
            document.getElementById('save-invigilator-btn').addEventListener('click', () => {
                const id = document.getElementById('invigilator-id').value;

                if (id) {
                    const table = document.getElementById('invigilators-table').getElementsByTagName('tbody')[0];
                    const newRow = table.insertRow();
                    newRow.insertCell(0).textContent = id;
                    const actionCell = newRow.insertCell(1);
                    const deleteBtn = document.createElement('button');
                    deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                    deleteBtn.className = 'btn';
                    deleteBtn.addEventListener('click', () => {
                        table.deleteRow
                        (newRow.rowIndex - 1);
                    });
                    actionCell.appendChild(deleteBtn);

                    addInvigilatorDialog.style.display = 'none';
                }
            });

            // Function to add Resource
            document.getElementById('save-resource-btn').addEventListener('click', () => {
                const id = document.getElementById('resource-id').value;
                const quantity = document.getElementById('resource-quantity').value;

                if (id && quantity) {
                    const table = document.getElementById('resources-table').getElementsByTagName('tbody')[0];
                    const newRow = table.insertRow();
                    newRow.insertCell(0).textContent = id;
                    newRow.insertCell(1).textContent = quantity;
                    const actionCell = newRow.insertCell(2);
                    const deleteBtn = document.createElement('button');
                    deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
                    deleteBtn.className = 'btn';
                    deleteBtn.addEventListener('click', () => {
                        table.deleteRow(newRow.rowIndex - 1);
                    });
                    actionCell.appendChild(deleteBtn);

                    addResourceDialog.style.display = 'none';
                }
            });

           // Function to save the competition
            document.getElementById('save-competition-btn').addEventListener('click', () => {
                // Populate hidden input fields with judges data
                const judgesData = Array.from(document.querySelectorAll('#judges-table tbody tr td:first-child')).map(td => td.textContent.trim()).join(',');
                document.getElementById('judges-data').value = judgesData;

                // Populate hidden input fields with invigilators data
                const invigilatorsData = Array.from(document.querySelectorAll('#invigilators-table tbody tr td:first-child')).map(td => td.textContent.trim()).join(',');
                document.getElementById('invigilators-data').value = invigilatorsData;

                // Populate hidden input fields with resources data
                const resourcesData = Array.from(document.querySelectorAll('#resources-table tbody tr')).map(row => {
                    const itemId = row.cells[0].textContent.trim();
                    const quantity = row.cells[1].textContent.trim();
                    return { "item_id": itemId, "quantity": quantity };
                });
                document.getElementById('resources-data').value = JSON.stringify(resourcesData);

                const competitionForm = document.getElementById('competition-form');
                if (competitionForm.checkValidity()) {
                    competitionForm.submit();
                } else {
                    alert('Please fill in all required fields.');
                }
            });


        });
        // Function to center the dialog vertically and horizontally
        function centerDialog(dialog) {
            const dialogContent = dialog.querySelector('.dialog-content');
            const windowHeight = window.innerHeight;
            const dialogHeight = dialogContent.offsetHeight;
            const dialogWidth = dialogContent.offsetWidth;

            // Calculate the top offset to center the dialog vertically
            const topOffset = (windowHeight - dialogHeight) / 2;

            // Calculate the left offset to center the dialog horizontally
            const leftOffset = (window.innerWidth - dialogWidth) / 2;

            // Apply the top and left offsets to center the dialog
            dialogContent.style.top = topOffset + 'px';
            dialogContent.style.left = leftOffset + 'px';
        }


    </script>
</body>
</html>
