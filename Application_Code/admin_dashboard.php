<?php
session_start();
include_once 'header.php';
require_once 'db_connection.php';

// Verify that the employee is logged in.
if (!isset($_SESSION['employee_logged_in'])) {
    echo "<p>You must be logged in as an employee to access this page.</p>";
    include_once 'footer.php';
    exit;
}

// Determine which section to display based on a GET parameter.
$section = isset($_GET['section']) ? $_GET['section'] : 'customers';

// Navigation for the dashboard:
echo "<nav style='margin-bottom:20px;'>";
echo "<a href='admin_dashboard.php?section=customers' style='color:#333;'>Manage Customers</a> | ";

// Only managers see these extra options.
if ($_SESSION['manager_role'] == 1) {
    echo "<a href='admin_dashboard.php?section=employees' style='color:#333;'>Manage Employees</a> | ";
    echo "<a href='admin_dashboard.php?section=hotels' style='color:#333;'>Manage Hotels</a> | ";
    echo "<a href='admin_dashboard.php?section=rooms' style='color:#333;'>Manage Rooms</a> | ";
}
echo "<a href='admin_dashboard.php?section=bookings' style='color:#333;'>Manage Bookings/Rentings</a> | ";
echo "<a href='admin_dashboard.php?section=payments' style='color:#333;'>Payment Processing</a>";
echo "</nav>";

echo "<h1>Admin Dashboard</h1>";

/* ====================================================
   SECTION: Manage Customers
   ==================================================== */
if ($section == 'customers') {
    echo "<h2>Manage Customers</h2>";

    // Handle adding a new customer.
    if (isset($_POST['add_customer'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $address = trim($_POST['address']);
        $government_id = trim($_POST['government_id']);
        $registration_date = $_POST['registration_date'];

        $insert_sql = "INSERT INTO customer (first_name, last_name, address, government_issued_id, date_of_registration)
                       VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        if ($stmt) {
            $stmt->bind_param("sssss", $first_name, $last_name, $address, $government_id, $registration_date);
            if ($stmt->execute()) {
                echo "<p>New customer added successfully!</p>";
            } else {
                echo "<p>Error inserting customer: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p>Prepare failed: " . $conn->error . "</p>";
        }
    }

    // Handle deletion.
    if (isset($_GET['delete_customer_id'])) {
        $delete_id = intval($_GET['delete_customer_id']);
        $delete_sql = "DELETE FROM customer WHERE customer_id = ?";
        $stmt = $conn->prepare($delete_sql);
        if ($stmt) {
            $stmt->bind_param("i", $delete_id);
            if ($stmt->execute()) {
                echo "<p>Customer deleted successfully!</p>";
            } else {
                echo "<p>Error deleting customer: " . $stmt->error . "</p>";
            }
        }
    }

    // Display customers.
    $result = $conn->query("SELECT * FROM customer");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                  <th>ID</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Address</th>
                  <th>Government ID</th>
                  <th>Reg. Date</th>
                  <th>Action</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['customer_id']) . "</td>
                    <td>" . htmlspecialchars($row['first_name']) . "</td>
                    <td>" . htmlspecialchars($row['last_name']) . "</td>
                    <td>" . htmlspecialchars($row['address']) . "</td>
                    <td>" . htmlspecialchars($row['government_issued_id']) . "</td>
                    <td>" . htmlspecialchars($row['date_of_registration']) . "</td>
                    <td><a href='admin_dashboard.php?section=customers&delete_customer_id=" . htmlspecialchars($row['customer_id']) . "'>Delete</a></td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No customers found.</p>";
    }
    ?>
    <h3>Add New Customer</h3>
    <form method="POST" action="admin_dashboard.php?section=customers">
      <label>First Name: </label><input type="text" name="first_name" required>
      <label>Last Name: </label><input type="text" name="last_name" required>
      <label>Address: </label><input type="text" name="address" required>
      <label>Government-issued ID: </label><input type="text" name="government_id" required>
      <label>Registration Date: </label><input type="date" name="registration_date" required>
      <button type="submit" name="add_customer">Add Customer</button>
    </form>
    <?php
}

/* ====================================================
   SECTION: Manage Employees (Manager Only)
   ==================================================== */
elseif ($section == 'employees') {
    if ($_SESSION['manager_role'] != 1) {
        echo "<p>You do not have permission to manage employees.</p>";
    } else {
        echo "<h2>Manage Employees</h2>";

        // Handle adding a new employee.
        if (isset($_POST['add_employee'])) {
            $first_name = trim($_POST['first_name']);
            $middle_name = trim($_POST['middle_name']);
            $last_name = trim($_POST['last_name']);
            $address = trim($_POST['address']);
            $sin = trim($_POST['sin']);
            $government_id = trim($_POST['government_id']);
            // Checkbox: if checked, manager_role becomes 1; else 0.
            $manager_role = isset($_POST['manager_role']) ? 1 : 0;
            $hotel_id = intval($_POST['hotel_id']);

            $insert_sql = "INSERT INTO employee (first_name, middle_name, last_name, address, sin, government_issued_id, manager_role, hotel_id)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            if ($stmt) {
                $stmt->bind_param("ssssssii", $first_name, $middle_name, $last_name, $address, $sin, $government_id, $manager_role, $hotel_id);
                if ($stmt->execute()) {
                    echo "<p>New employee added successfully!</p>";
                } else {
                    echo "<p>Error inserting employee: " . $stmt->error . "</p>";
                }
            } else {
                echo "<p>Prepare failed: " . $conn->error . "</p>";
            }
        }

        // Handle deletion.
        if (isset($_GET['delete_employee_id'])) {
            $delete_id = intval($_GET['delete_employee_id']);
            $delete_sql = "DELETE FROM employee WHERE employee_id = ?";
            $stmt = $conn->prepare($delete_sql);
            if ($stmt) {
                $stmt->bind_param("i", $delete_id);
                if ($stmt->execute()) {
                    echo "<p>Employee deleted successfully!</p>";
                } else {
                    echo "<p>Error deleting employee: " . $stmt->error . "</p>";
                }
            }
        }

        // Display employees.
        $result = $conn->query("SELECT * FROM employee");
        if ($result && $result->num_rows > 0) {
            echo "<table border='1'>
                    <tr>
                      <th>ID</th>
                      <th>First Name</th>
                      <th>Middle Name</th>
                      <th>Last Name</th>
                      <th>Address</th>
                      <th>SIN</th>
                      <th>Government ID</th>
                      <th>Manager?</th>
                      <th>Hotel ID</th>
                      <th>Action</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['employee_id']) . "</td>
                        <td>" . htmlspecialchars($row['first_name']) . "</td>
                        <td>" . htmlspecialchars($row['middle_name']) . "</td>
                        <td>" . htmlspecialchars($row['last_name']) . "</td>
                        <td>" . htmlspecialchars($row['address']) . "</td>
                        <td>" . htmlspecialchars($row['sin']) . "</td>
                        <td>" . htmlspecialchars($row['government_issued_id']) . "</td>
                        <td>" . ($row['manager_role'] ? "Yes" : "No") . "</td>
                        <td>" . htmlspecialchars($row['hotel_id']) . "</td>
                        <td><a href='admin_dashboard.php?section=employees&delete_employee_id=" . htmlspecialchars($row['employee_id']) . "'>Delete</a></td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No employees found.</p>";
        }
        ?>
        <h3>Add New Employee</h3>
        <form method="POST" action="admin_dashboard.php?section=employees">
          <label>First Name: </label><input type="text" name="first_name" required>
          <label>Middle Name: </label><input type="text" name="middle_name">
          <label>Last Name: </label><input type="text" name="last_name" required>
          <label>Address: </label><input type="text" name="address" required>
          <label>SIN: </label><input type="text" name="sin" required>
          <label>Government-issued ID: </label><input type="text" name="government_id" required>
          <label>Hotel ID (where employee works): </label><input type="number" name="hotel_id" required>
          <label>Manager Role: </label><input type="checkbox" name="manager_role" value="1"> (Check if manager)
          <button type="submit" name="add_employee">Add Employee</button>
        </form>
        <?php
    }
}

/* ====================================================
   SECTION: Manage Hotels (Manager Only)
   ==================================================== */
elseif ($section == 'hotels') {
    if ($_SESSION['manager_role'] != 1) {
        echo "<p>You do not have permission to manage hotels.</p>";
    } else {
        echo "<h2>Manage Hotels</h2>";

        // Handle adding a new hotel.
        if (isset($_POST['add_hotel'])) {
            $chain_id = intval($_POST['chain_id']);
            $number_of_rooms = intval($_POST['number_of_rooms']);
            $address = trim($_POST['address']);
            $category = trim($_POST['category']);

            $insert_sql = "INSERT INTO hotel (chain_id, number_of_rooms, address, category)
                           VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            if ($stmt) {
                $stmt->bind_param("iiss", $chain_id, $number_of_rooms, $address, $category);
                if ($stmt->execute()) {
                    echo "<p>New hotel added successfully!</p>";
                } else {
                    echo "<p>Error inserting hotel: " . $stmt->error . "</p>";
                }
            }
        }

        // Handle deletion.
        if (isset($_GET['delete_hotel_id'])) {
            $delete_id = intval($_GET['delete_hotel_id']);
            $delete_sql = "DELETE FROM hotel WHERE hotel_id = ?";
            $stmt = $conn->prepare($delete_sql);
            if ($stmt) {
                $stmt->bind_param("i", $delete_id);
                if ($stmt->execute()) {
                    echo "<p>Hotel deleted successfully!</p>";
                } else {
                    echo "<p>Error deleting hotel: " . $stmt->error . "</p>";
                }
            }
        }

        // Display hotels.
        $result = $conn->query("SELECT * FROM hotel");
        if ($result && $result->num_rows > 0) {
            echo "<table border='1'>
                    <tr>
                      <th>Hotel ID</th>
                      <th>Chain ID</th>
                      <th>Number of Rooms</th>
                      <th>Address</th>
                      <th>Category</th>
                      <th>Action</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['hotel_id']) . "</td>
                        <td>" . htmlspecialchars($row['chain_id']) . "</td>
                        <td>" . htmlspecialchars($row['number_of_rooms']) . "</td>
                        <td>" . htmlspecialchars($row['address']) . "</td>
                        <td>" . htmlspecialchars($row['category']) . "</td>
                        <td><a href='admin_dashboard.php?section=hotels&delete_hotel_id=" . htmlspecialchars($row['hotel_id']) . "'>Delete</a></td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hotels found.</p>";
        }
        ?>
        <h3>Add New Hotel</h3>
        <form method="POST" action="admin_dashboard.php?section=hotels">
          <label>Chain ID: </label><input type="number" name="chain_id" required>
          <label>Number of Rooms: </label><input type="number" name="number_of_rooms" required>
          <label>Address: </label><input type="text" name="address" required>
          <label>Category (e.g., Luxury, Budget): </label><input type="text" name="category" required>
          <button type="submit" name="add_hotel">Add Hotel</button>
        </form>
        <?php
    }
}

/* ====================================================
   SECTION: Manage Rooms (Manager Only)
   ==================================================== */
elseif ($section == 'rooms') {
    if ($_SESSION['manager_role'] != 1) {
        echo "<p>You do not have permission to manage rooms.</p>";
    } else {
        echo "<h2>Manage Rooms</h2>";

        // Handle adding a new room.
        if (isset($_POST['add_room'])) {
            $hotel_id = intval($_POST['hotel_id']);
            $price_of_room = floatval($_POST['price_of_room']);
            $capacity = intval($_POST['capacity']);
            $type_of_view = trim($_POST['type_of_view']);
            $options_to_extend = trim($_POST['options_to_extend']);

            $insert_sql = "INSERT INTO rooms (hotel_id, price_of_room, capacity, type_of_view, options_to_extend)
                           VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            if ($stmt) {
                $stmt->bind_param("idiss", $hotel_id, $price_of_room, $capacity, $type_of_view, $options_to_extend);
                if ($stmt->execute()) {
                    echo "<p>New room added successfully!</p>";
                } else {
                    echo "<p>Error inserting room: " . $stmt->error . "</p>";
                }
            }
        }

        // Handle deletion.
        if (isset($_GET['delete_room_id'])) {
            $delete_id = intval($_GET['delete_room_id']);
            $delete_sql = "DELETE FROM rooms WHERE room_id = ?";
            $stmt = $conn->prepare($delete_sql);
            if ($stmt) {
                $stmt->bind_param("i", $delete_id);
                if ($stmt->execute()) {
                    echo "<p>Room deleted successfully!</p>";
                } else {
                    echo "<p>Error deleting room: " . $stmt->error . "</p>";
                }
            }
        }

        // Display rooms.
        $result = $conn->query("SELECT * FROM rooms");
        if ($result && $result->num_rows > 0) {
            echo "<table border='1'>
                    <tr>
                      <th>Room ID</th>
                      <th>Hotel ID</th>
                      <th>Price of Room</th>
                      <th>Capacity</th>
                      <th>Type of View</th>
                      <th>Options to Extend</th>
                      <th>Action</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['room_id']) . "</td>
                        <td>" . htmlspecialchars($row['hotel_id']) . "</td>
                        <td>" . htmlspecialchars($row['price_of_room']) . "</td>
                        <td>" . htmlspecialchars($row['capacity']) . "</td>
                        <td>" . htmlspecialchars($row['type_of_view']) . "</td>
                        <td>" . htmlspecialchars($row['options_to_extend']) . "</td>
                        <td><a href='admin_dashboard.php?section=rooms&delete_room_id=" . htmlspecialchars($row['room_id']) . "'>Delete</a></td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No rooms found.</p>";
        }
        ?>
        <h3>Add New Room</h3>
        <form method="POST" action="admin_dashboard.php?section=rooms">
          <label>Hotel ID: </label><input type="number" name="hotel_id" required>
          <label>Price of Room: </label><input type="number" step="0.01" name="price_of_room" required>
          <label>Capacity: </label><input type="number" name="capacity" required>
          <label>Type of View: </label><input type="text" name="type_of_view" required>
          <label>Options to Extend: </label><input type="text" name="options_to_extend">
          <button type="submit" name="add_room">Add Room</button>
        </form>
        <?php
    }
}

/* ====================================================
   SECTION: Manage Bookings / Rentings
   ==================================================== */
elseif ($section == 'bookings') {
    echo "<h2>Manage Bookings and Rentings</h2>";

    // Display archived bookings.
    echo "<h3>Archived Bookings</h3>";
    $result = $conn->query("SELECT * FROM archives_bookings");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                  <th>Archive ID</th>
                  <th>Booking Details</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['archive_id']) . "</td>
                    <td>" . htmlspecialchars($row['bookings']) . "</td>
                    <td>" . htmlspecialchars($row['start_date']) . "</td>
                    <td>" . htmlspecialchars($row['end_date']) . "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No archived bookings found.</p>";
    }

    // Form for direct booking insertion.
    if (isset($_POST['create_booking'])) {
        $room_id = intval($_POST['room_id']);
        $customer_id = intval($_POST['customer_id']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        $booking_details = "Room ID: $room_id, Customer ID: $customer_id";

        // Generate a new archive_id.
        $result = $conn->query("SELECT MAX(archive_id) AS max_id FROM archives");
        $row = $result->fetch_assoc();
        $new_archive_id = isset($row['max_id']) ? intval($row['max_id']) + 1 : 1;

        // Insert into archives.
        $archive_sql = "INSERT INTO archives (archive_id) VALUES (?)";
        $archive_stmt = $conn->prepare($archive_sql);
        $archive_stmt->bind_param("i", $new_archive_id);
        $archive_stmt->execute();

        // Insert into archives_bookings.
        $sql = "INSERT INTO archives_bookings (archive_id, bookings, start_date, end_date)
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $new_archive_id, $booking_details, $start_date, $end_date);
        if ($stmt->execute()) {
            echo "<p>Booking created with Archive ID: $new_archive_id.</p>";
        } else {
            echo "<p>Error creating booking: " . $conn->error . "</p>";
        }
    }
    ?>
    <h3>Create New Booking</h3>
    <form method="POST" action="admin_dashboard.php?section=bookings">
      <label>Room ID:</label><input type="number" name="room_id" required>
      <label>Customer ID:</label><input type="number" name="customer_id" required>
      <label>Start Date:</label><input type="date" name="start_date" required>
      <label>End Date:</label><input type="date" name="end_date" required>
      <button type="submit" name="create_booking">Create Booking</button>
    </form>

    <!-- Form to transform an existing booking into a renting -->
    <?php
    if (isset($_POST['transform_booking'])) {
        $archive_id = intval($_POST['archive_id']);
        // Retrieve booking details.
        $booking_sql = "SELECT bookings, start_date, end_date FROM archives_bookings WHERE archive_id = ?";
        $stmt = $conn->prepare($booking_sql);
        $stmt->bind_param("i", $archive_id);
        $stmt->execute();
        $booking_result = $stmt->get_result()->fetch_assoc();
        if (!$booking_result) {
            echo "<p>Invalid Archive ID.</p>";
        } else {
            $renting_details = "Transformed Renting: " . $booking_result['bookings'] .
                                ", From: " . $booking_result['start_date'] .
                                " To: " . $booking_result['end_date'];
            // Insert into archives_rentings using the same archive_id.
            $sql = "INSERT INTO archives_rentings (archive_id, rentings) VALUES (?, ?)";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param("is", $archive_id, $renting_details);
            if ($stmt2->execute()) {
                echo "<p>Booking (Archive ID: $archive_id) transformed to renting successfully!</p>";
            } else {
                echo "<p>Error transforming booking: " . $conn->error . "</p>";
            }
        }
    }
    ?>
    <h3>Transform Booking to Renting</h3>
    <form method="POST" action="admin_dashboard.php?section=bookings">
      <label>Booking Archive ID:</label>
      <input type="number" name="archive_id" required>
      <button type="submit" name="transform_booking">Transform to Renting</button>
    </form>
    <?php
}

/* ====================================================
   SECTION: Payment Processing
   ==================================================== */
elseif ($section == 'payments') {
    echo "<h2>Payment Processing</h2>";

    // Since the project does not require storing a persistent payment history,
    // we simulate payment processing without querying or inserting into a payments table.
    if (isset($_POST['record_payment'])) {
        $renting_archive_id = intval($_POST['renting_archive_id']);
        $amount = floatval($_POST['amount']);
        $payment_date = $_POST['payment_date'];
        
        // Simulate payment processing.
        echo "<p>Payment of $$amount for renting Archive ID $renting_archive_id on $payment_date has been recorded (simulation only, no DB storage).</p>";
    }
    ?>
    <h3>Record a Payment</h3>
    <form method="POST" action="admin_dashboard.php?section=payments">
      <label>Renting Archive ID:</label>
      <input type="number" name="renting_archive_id" required>
      <label>Amount:</label>
      <input type="number" step="0.01" name="amount" required>
      <label>Payment Date:</label>
      <input type="date" name="payment_date" required>
      <button type="submit" name="record_payment">Record Payment</button>
    </form>
    <?php
}

include_once 'footer.php';
?>
