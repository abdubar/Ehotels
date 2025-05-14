<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['employee_logged_in'])) {
    echo "<p>Unauthorized access.</p>";
    exit;
}

// Direct renting (without transforming an existing booking)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['transform'])) {
    $room_id = intval($_POST['room_id']);
    $customer_id = intval($_POST['customer_id']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Create a renting description string
    $renting_details = "Room ID: $room_id, Customer ID: $customer_id, Start: $start_date, End: $end_date";

    // Generate new archive_id
    $result = $conn->query("SELECT MAX(archive_id) AS max_id FROM archives");
    $row = $result->fetch_assoc();
    $new_archive_id = isset($row['max_id']) ? intval($row['max_id']) + 1 : 1;

    // Insert into archives table
    $archive_sql = "INSERT INTO archives (archive_id) VALUES (?)";
    $archive_stmt = $conn->prepare($archive_sql);
    $archive_stmt->bind_param("i", $new_archive_id);
    $archive_stmt->execute();

    // Insert into archives_rentings table; note that our schema only has (archive_id, rentings)
    $sql = "INSERT INTO archives_rentings (archive_id, rentings) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $new_archive_id, $renting_details);
    if ($stmt->execute()) {
        echo "<p>Room rented successfully!</p>";
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}

// Transform booking to renting
if (isset($_POST['transform'])) {
    // Here, we expect the user to provide the Archive ID of the booking to transform.
    $archive_id = intval($_POST['booking_archive_id']);

    // Retrieve booking info from archives_bookings
    $booking_sql = "SELECT bookings, start_date, end_date FROM archives_bookings WHERE archive_id = ?";
    $stmt = $conn->prepare($booking_sql);
    $stmt->bind_param("i", $archive_id);
    $stmt->execute();
    $booking_result = $stmt->get_result()->fetch_assoc();

    if (!$booking_result) {
        echo "<p>Invalid archive ID for booking.</p>";
        exit;
    }

    // Create a renting description from the booking info
    $renting_details = "Transformed Renting: " . $booking_result['bookings'] .
                       ", From: " . $booking_result['start_date'] .
                       " To: " . $booking_result['end_date'];

    // Insert into archives_rentings using the same archive_id
    $sql = "INSERT INTO archives_rentings (archive_id, rentings) VALUES (?, ?)";
    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("is", $archive_id, $renting_details);
    if ($stmt2->execute()) {
        echo "<p>Booking (Archive ID: $archive_id) transformed to renting successfully!</p>";
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}
?>
