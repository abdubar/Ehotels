<?php
session_start();
require_once 'db_connection.php';

// Ensure the customer is logged in.
if (!isset($_SESSION['customer_logged_in']) || !isset($_SESSION['customer_id'])) {
    echo "<p>You must be logged in as a customer to book a room.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = intval($_POST['room_id']);
    // Use the customer_id from the session (or from POST if you've hidden it).
    $customer_id = intval($_POST['customer_id']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Create a booking description string.
    $booking_details = "Room ID: $room_id, Customer ID: $customer_id";

    // Generate a new archive_id by querying the maximum current value and adding 1.
    $result = $conn->query("SELECT MAX(archive_id) AS max_id FROM archives");
    $row = $result->fetch_assoc();
    $new_archive_id = isset($row['max_id']) ? intval($row['max_id']) + 1 : 1;

    // Insert into the archives table first.
    $archive_sql = "INSERT INTO archives (archive_id) VALUES (?)";
    $archive_stmt = $conn->prepare($archive_sql);
    $archive_stmt->bind_param("i", $new_archive_id);
    $archive_stmt->execute();

    // Insert into archives_bookings table using columns (archive_id, bookings, start_date, end_date).
    $sql = "INSERT INTO archives_bookings (archive_id, bookings, start_date, end_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<p>Prepare failed: (" . $conn->errno . ") " . $conn->error . "</p>";
        exit;
    }
    $stmt->bind_param("isss", $new_archive_id, $booking_details, $start_date, $end_date);
    if ($stmt->execute()) {
        echo "<p>Booking confirmed! <a href='search.php'>Search more rooms</a></p>";
    } else {
        echo "<p>Error creating booking: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Invalid request.</p>";
}
?>
