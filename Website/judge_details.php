<?php
include 'database.php';

// Fetch competition ID from GET parameter
$compId = isset($_GET['compId']) ? intval($_GET['compId']) : 0;

// Initialize variables
$competitionDetails = null;
$participants = [];

if ($compId > 0) {
    // Fetch competition details
    $query = "SELECT * FROM Competition WHERE event_id = ?";
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $compId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            $competitionDetails = mysqli_fetch_assoc($result);
        }
        mysqli_stmt_close($stmt);
    }

    // Fetch participants
    $query = "SELECT p.person_id, p.f_name, p.l_name FROM Attendees a 
              JOIN Person p ON a.person_id = p.person_id 
              WHERE a.event_id = ?";
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $compId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $participants[] = [
                'id' => $row['person_id'],
                'name' => $row['f_name'] . ' ' . $row['l_name']
            ];
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle form submission via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id']) && isset($_POST['marks'])) {
    $eventId = intval($_POST['event_id']);
    $marks = $_POST['marks'];
    $participantIds = $_POST['participant_ids'];

    // Validate and process marks
    $results = [];
    foreach ($marks as $index => $mark) {
        if (is_numeric($mark) && isset($participantIds[$index])) {
            $results[] = [
                'person_id' => intval($participantIds[$index]),
                'marks' => intval($mark)
            ];
        }
    }

    // Sort results by marks in descending order
    usort($results, function($a, $b) {
        return $b['marks'] - $a['marks'];
    });

    // Insert results into the Result table
    if (count($results) >= 2) {
        $winnerId = $results[0]['person_id'];
        $runnerUpId = $results[1]['person_id'];

        $query = "INSERT INTO Result (event_id, winner_id, runner_up_id) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $query)) {
            mysqli_stmt_bind_param($stmt, "iii", $eventId, $winnerId, $runnerUpId);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
            }
            mysqli_stmt_close($stmt);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Not enough participants.']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Details</title>
    <link rel="stylesheet" href="./judge_details.css">
    <style>
        .success-message {
            color: green;
            margin-top: 20px;
        }
        .error-message {
            color: red;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="navbar">
            <span class="logo">Competition Management System</span>
            <nav>
                <a href="./judge.php">Home</a>
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
            <h1>Competition Details</h1>
            <div class="competition-info">
                <?php if ($competitionDetails): ?>
                    <div>
                        <label for="competition-name">Name:</label>
                        <span id="competition-name"><?php echo htmlspecialchars($competitionDetails['name']); ?></span>
                    </div>
                    <div>
                        <label for="competition-date">Date:</label>
                        <span id="competition-date"><?php echo htmlspecialchars($competitionDetails['date']); ?></span>
                    </div>
                    <div>
                        <label for="competition-venue">Venue:</label>
                        <span id="competition-venue"><?php echo htmlspecialchars($competitionDetails['venue']); ?></span>
                    </div>
                <?php else: ?>
                    <p>Competition details not found.</p>
                <?php endif; ?>
                <div>
                    <label for="participants-list">Participants:</label>
                    <ul id="participants-list">
                        <?php
                        if (!empty($participants)) {
                            foreach ($participants as $participant) {
                                echo "<li>" . htmlspecialchars($participant['name']) . "</li>";
                            }
                        } else {
                            echo "<li>No participants found.</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <?php if ($competitionDetails): ?>
                <button id="generate-result" class="generate-result-btn">Generate Result</button>
            <?php endif; ?>
            <div id="result-entry" class="results-entry hidden">
                <h2>Result's Entry</h2>
                <form id="result-form">
                    <input type="hidden" name="event_id" value="<?php echo $compId; ?>">
                    <table>
                        <thead>
                            <tr>
                                <th>Contestants</th>
                                <th>Marks</th>
                            </tr>
                        </thead>
                        <tbody id="result-entries">
                            <!-- Dynamically generated rows -->
                        </tbody>
                    </table>
                    <button type="submit" id="save-results" class="save-results-btn">Save</button>
                </form>
            </div>
            <div id="message" class="hidden"></div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const participants = <?php echo json_encode($participants); ?>;

            document.getElementById('generate-result').addEventListener('click', function() {
                const resultEntrySection = document.getElementById('result-entry');
                const resultEntriesTable = document.getElementById('result-entries');
                resultEntriesTable.innerHTML = ''; // Clear previous entries

                participants.forEach(participant => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${participant.name}</td>
                        <td><input type="number" name="marks[]" min="0" max="100" required></td>
                        <input type="hidden" name="participant_ids[]" value="${participant.id}">
                    `;
                    resultEntriesTable.appendChild(row);
                });

                resultEntrySection.classList.remove('hidden');
            });

            document.getElementById('logout-button').addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'index.php';
                }
            });

            document.getElementById('result-form').addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const messageDiv = document.getElementById('message');
                    if (data.success) {
                        messageDiv.classList.remove('hidden');
                        messageDiv.classList.add('success-message');
                        messageDiv.classList.remove('error-message');
                        messageDiv.textContent = 'Results have been saved successfully.';
                    } else {
                        messageDiv.classList.remove('hidden');
                        messageDiv.classList.add('error-message');
                        messageDiv.classList.remove('success-message');
                        messageDiv.textContent = 'Error: ' + (data.error || 'An error occurred while saving results.');
                    }
                })
                .catch(error => {
                    const messageDiv = document.getElementById('message');
                    messageDiv.classList.remove('hidden');
                    messageDiv.classList.add('error-message');
                    messageDiv.classList.remove('success-message');
                    messageDiv.textContent = 'Error: ' + error.message;
                });
            });
        });
    </script>
</body>
</html>
