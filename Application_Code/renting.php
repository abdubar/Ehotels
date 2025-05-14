<?php
session_start();
include_once 'header.php';
require_once 'db_connection.php';

// Check employee login
if (!isset($_SESSION['employee_logged_in'])) {
    echo "<p>Only employees can rent rooms directly.</p>";
    include_once 'footer.php';
    exit;
}

echo "<h2>Rent a Room (Walk-In)</h2>";
?>
<form action="process_renting.php" method="POST">
  <label for="room_id">Room ID:</label>
  <input type="number" name="room_id" required>

  <label for="customer_id">Customer ID:</label>
  <input type="number" name="customer_id" required>

  <label for="start_date">Start Date:</label>
  <input type="date" name="start_date" required>

  <label for="end_date">End Date:</label>
  <input type="date" name="end_date" required>

  <button type="submit">Rent Now</button>
</form>

<h2>Transform Booking to Renting</h2>
<form action="process_renting.php" method="POST">
  <!-- Now the user must provide the Archive ID of the booking record -->
  <label for="booking_archive_id">Booking Archive ID:</label>
  <input type="number" name="booking_archive_id" required>

  <button type="submit" name="transform">Transform to Renting</button>
</form>

<?php
include_once 'footer.php';
?>
