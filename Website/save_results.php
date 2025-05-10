<?php
include 'database.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the event ID from the hidden input field
    $eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

    // Get the marks from the form submission
    $marks = isset($_POST['marks']) ? $_POST['marks'] : [];

    // Initialize an array to store participant IDs and their marks
    $results = [];

    // Fetch the participants based on the event ID
    $query = "SELECT p.person_id FROM Attendees a
              JOIN Person p ON a.person_id = p.person_id
              WHERE a.event_id = ?";
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $eventId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $index = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            if (isset($marks[$index])) {
                $results[] = [
                    'person_id' => $row['person_id'],
                    'marks' => intval($marks[$index])
                ];
            }
            $index++;
        }
        mysqli_stmt_close($stmt);
    }

    // Sort the results based on marks in descending order
    usort($results, function($a, $b) {
        return $b['marks'] - $a['marks'];
    });

    // Check if there are at least two participants
    if (count($results) >= 2) {
        $winnerId = $results[0]['person_id'];
        $runnerUpId = $results[1]['person_id'];

        // Insert the result into the Result table
        $query = "INSERT INTO Result (event_id, winner_id, runner_up_id) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $query)) {
            mysqli_stmt_bind_param($stmt, "iii", $eventId, $winnerId, $runnerUpId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Redirect to a success page
            header("Location: result_success.php?event_id=" . $eventId);
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Error: Not enough participants.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .container p {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .container a.button {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .container a.button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <p>Results have been saved successfully.</p>
        <a href="judge_dash.php" class="button">Back to Dashboard</a>
    </div>
</body>
</html>
