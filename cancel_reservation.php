<?php
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rental_id'])) {
    $rental_id = $_POST['rental_id'];

    $update_query = "UPDATE rentals SET rental_status_id = 3 WHERE rental_id = ? AND rental_status_id != 3";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $rental_id);

    if ($stmt->execute()) {
        header("Location: myreservations.php?message=Reservation Cancelled");
        exit();
    } else {
        echo "<p>Error cancelling reservation: " . $stmt->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p>Invalid request.</p>";
}
?>