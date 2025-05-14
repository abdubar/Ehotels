<?php
include_once 'header.php';
require_once 'db_connection.php';
?>

<h2>Search for Rooms</h2>
<form method="GET" action="search.php">
  <label for="start_date">Start Date:</label>
  <input type="date" name="start_date" id="start_date">
  
  <label for="end_date">End Date:</label>
  <input type="date" name="end_date" id="end_date">

  <label for="capacity">Capacity:</label>
  <input type="number" name="capacity" id="capacity" min="1">

  <label for="area">Area:</label>
  <input type="text" name="area" id="area">

  <label for="chain_id">Hotel Chain:</label>
  <select name="chain_id" id="chain_id">
    <option value="">--Any--</option>
    <?php
      $chain_res = $conn->query("SELECT chain_id FROM hotelchain");
      if ($chain_res) {
          while ($chain_row = $chain_res->fetch_assoc()) {
              $chainId = htmlspecialchars($chain_row['chain_id']);
              echo "<option value='{$chainId}'>{$chainId}</option>";
          }
      }
    ?>
  </select>

  <label for="category">Hotel Category (e.g., Luxury, Budget):</label>
  <input type="text" name="category" id="category">

  <label for="hotel_room_count">Total Number of Rooms (in the hotel):</label>
  <input type="number" name="hotel_room_count" id="hotel_room_count" min="1">

  <label for="price">Max Price:</label>
  <input type="number" name="price" id="price" min="0" step="0.01">

  <button type="submit">Search</button>
</form>

<?php
$whereClauses = [];
$sql = "SELECT r.*, h.hotel_id, h.category, h.address AS hotel_address
        FROM rooms AS r
        JOIN hotel AS h ON r.hotel_id = h.hotel_id
        WHERE 1=1";

if (!empty($_GET['capacity'])) {
    $capacity = intval($_GET['capacity']);
    $whereClauses[] = "r.capacity >= $capacity";
}

if (!empty($_GET['area'])) {
    $area = $conn->real_escape_string($_GET['area']);
    $whereClauses[] = "h.address LIKE '%$area%'";
}

if (!empty($_GET['chain_id'])) {
    $chain_id = intval($_GET['chain_id']);
    $whereClauses[] = "h.chain_id = $chain_id";
}

if (!empty($_GET['category'])) {
    $category = $conn->real_escape_string($_GET['category']);
    $whereClauses[] = "h.category LIKE '%$category%'";
}

if (!empty($_GET['hotel_room_count'])) {
    $room_count = intval($_GET['hotel_room_count']);
    $whereClauses[] = "h.number_of_rooms >= $room_count";
}

if (!empty($_GET['price'])) {
    $price = floatval($_GET['price']);
    $whereClauses[] = "r.price_of_room <= $price";
}

if (!empty($whereClauses)) {
    $sql .= " AND " . implode(" AND ", $whereClauses);
}

$result = $conn->query($sql);
if (!$result) {
    echo "<p>Error in query: " . $conn->error . "</p>";
} else if ($result->num_rows > 0) {
    echo "<h3>Search Results</h3>";
    echo "<table border='1'>
            <tr>
              <th>Room ID</th>
              <th>Capacity</th>
              <th>Price</th>
              <th>Hotel Address</th>
              <th>Category</th>
              <th>Action</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        $room_id = htmlspecialchars($row['room_id']);
        $capacity = htmlspecialchars($row['capacity']);
        $price = htmlspecialchars($row['price_of_room']);
        $hotel_address = htmlspecialchars($row['hotel_address']);
        $category = htmlspecialchars($row['category']);
        
        echo "<tr>
                <td>{$room_id}</td>
                <td>{$capacity}</td>
                <td>{$price}</td>
                <td>{$hotel_address}</td>
                <td>{$category}</td>
                <td><a href='booking.php?room_id={$room_id}'>Book Now</a></td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No rooms found matching your criteria.</p>";
}

include_once 'footer.php';
?>
