<?php
include_once 'header.php';
require_once 'db_connection.php';

echo "<h2>Total Room Capacity per Hotel (View)</h2>";

$sql = "SELECT * FROM totalroomcapacityperhotel";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1'>
            <tr><th>Hotel ID</th><th>Total Capacity</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['hotel_id']) . "</td>
                <td>" . htmlspecialchars($row['total_capacity']) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data available in the view.</p>";
}

include_once 'footer.php';
?>
