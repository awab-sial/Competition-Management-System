<?php
// Include the database connection file
include 'database.php';

if (isset($_GET['id'])) {
    $competitionID = $_GET['id'];

    // Fetch competition details from the database
    $competitionDetailsQuery = "SELECT name, DATE_FORMAT(date, '%d %b %Y') AS start_date, DATE_FORMAT(date, '%d %b %Y') AS end_date, 
                                starting_time, ending_time, venue FROM Competition WHERE event_id = $competitionID";
    $competitionDetailsResult = $conn->query($competitionDetailsQuery);

    if ($competitionDetailsResult->num_rows == 1) {
        $row = $competitionDetailsResult->fetch_assoc();
        // Return competition details as JSON
        echo json_encode($row);
    } else {
        // Return an empty JSON object if competition details are not found
        echo json_encode((object)[]);
    }
} else {
    // Return an empty JSON object if competition ID is not provided
    echo json_encode((object)[]);
}
?>
