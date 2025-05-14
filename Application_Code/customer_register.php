<?php
session_start();
include_once 'header.php';
require_once 'db_connection.php';

// If the registration form is submitted...
if (isset($_POST['register'])) {
    // Get and sanitize input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $address = trim($_POST['address']);
    $government_id = trim($_POST['government_id']);
    $password = trim($_POST['password']);
    
    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Automatically set the registration date to today
    $registration_date = date("Y-m-d");
    
    // Prepare SQL to insert a new customer record
    $sql = "INSERT INTO customer (first_name, last_name, address, government_issued_id, date_of_registration, password)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<p>Error preparing statement: " . $conn->error . "</p>";
    } else {
        $stmt->bind_param("ssssss", $first_name, $last_name, $address, $government_id, $registration_date, $hashedPassword);
        if ($stmt->execute()) {
            echo "<p>Registration successful! Your Customer ID is: " . $conn->insert_id . "</p>";
            echo "<p><a href='customer_login.php'>Click here to login</a></p>";
        } else {
            echo "<p>Error registering customer: " . $stmt->error . "</p>";
        }
    }
}
?>

<h2>Customer Registration</h2>
<form action="customer_register.php" method="POST">
    <label>First Name:</label>
    <input type="text" name="first_name" required><br>
    
    <label>Last Name:</label>
    <input type="text" name="last_name" required><br>
    
    <label>Address:</label>
    <input type="text" name="address" required><br>
    
    <label>Government-issued ID:</label>
    <input type="text" name="government_id" required><br>
    
    <label>Password:</label>
    <input type="password" name="password" required><br>
    
    <button type="submit" name="register">Register</button>
</form>

<?php
include_once 'footer.php';
?>
