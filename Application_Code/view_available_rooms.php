<?php
include_once 'header.php';
require_once 'db_connection.php';

echo "<h2>Available Rooms per Area (View)</h2>";

$sql = "SELECT * FROM availableroomsperarea"; // Expecting columns: area, number_of_rooms
$result = $conn->query($sql);

if (!$result) {
    echo "<p>Error retrieving data: " . $conn->error . "</p>";
} else if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
              <th>Area</th>
              <th>Number of Rooms</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        $area = isset($row['area']) ? htmlspecialchars($row['area']) : 'Unknown';
        $rooms = isset($row['number_of_rooms']) ? htmlspecialchars($row['number_of_rooms']) : 'N/A';
        echo "<tr>
                <td>{$area}</td>
                <td>{$rooms}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data available in the view.</p>";
}

include_once 'footer.php';
?>
