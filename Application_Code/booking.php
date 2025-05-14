<?php
session_start();
include_once 'header.php';
require_once 'db_connection.php';

// Ensure the customer is logged in.
if (!isset($_SESSION['customer_logged_in']) || !isset($_SESSION['customer_id'])) {
    echo "<p>You must be logged in as a customer to book a room. Please <a href='customer_login.php'>login</a> or <a href='customer_register.php'>register</a>.</p>";
    include_once 'footer.php';
    exit;
}

// Get room ID from GET parameters.
if (!isset($_GET['room_id'])) {
    echo "<p>No room selected for booking.</p>";
    include_once 'footer.php';
    exit;
}
$room_id = intval($_GET['room_id']);

// Fetch room details.
$sql = "SELECT r.*, h.hotel_id, h.address AS hotel_address
        FROM rooms AS r
        JOIN hotel AS h ON r.hotel_id = h.hotel_id
        WHERE r.room_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();

if (!$room) {
    echo "<p>Invalid room ID.</p>";
    include_once 'footer.php';
    exit;
}

// Display room details.
echo "<h2>Booking Room #" . htmlspecialchars($room['room_id']) . "</h2>";
echo "<p>Capacity: " . htmlspecialchars($room['capacity']) . "</p>";
$price = isset($room['price_of_room']) ? htmlspecialchars($room['price_of_room']) : 'N/A';
echo "<p>Price: \$$price</p>";
echo "<p>Hotel Address: " . htmlspecialchars($room['hotel_address']) . "</p>";
?>

<form action="process_booking.php" method="POST">
  <!-- Hidden fields to pass room_id and customer_id automatically -->
  <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room_id); ?>">
  <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($_SESSION['customer_id']); ?>">
  
  <!-- Booking Dates -->
  <label for="start_date">Start Date:</label>
  <input type="date" name="start_date" id="start_date" required>
  
  <label for="end_date">End Date:</label>
  <input type="date" name="end_date" id="end_date" required>
  
  <button type="submit">Confirm Booking</button>
</form>

<?php
include_once 'footer.php';
?>
