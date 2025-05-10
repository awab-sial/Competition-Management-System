<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $person_id = $_POST['person-id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO organizer (person_id, status) VALUES (?, ?)");
    $stmt->bind_param("is", $person_id, $status);
    
    if ($stmt->execute()) {
        header('Location: admin_organizer_mod.php');
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
