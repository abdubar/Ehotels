<?php
session_start();
include_once 'header.php';
require_once 'db_connection.php';

// If the login form is submitted...
if (isset($_POST['login'])) {
    // Sanitize input
    $government_id = trim($_POST['government_id']);
    $password = trim($_POST['password']);
    
    // Prepare a query to fetch the customer record using the government-issued ID
    $sql = "SELECT customer_id, password FROM customer WHERE government_issued_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<p>Error preparing statement: " . $conn->error . "</p>";
    } else {
        $stmt->bind_param("s", $government_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // If one record is found, verify the password.
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['customer_logged_in'] = true;
                $_SESSION['customer_id'] = $row['customer_id'];
                echo "<p>Login successful! <a href='search.php'>Go to Search Menu</a></p>";
            } else {
                echo "<p>Invalid password.</p>";
            }
        } else {
            echo "<p>Invalid Government-issued ID.</p>";
        }
    }
}
?>

<h2>Customer Login</h2>
<form action="customer_login.php" method="POST">
    <label>Government-issued ID:</label>
    <input type="text" name="government_id" required><br>
    
    <label>Password:</label>
    <input type="password" name="password" required><br>
    
    <button type="submit" name="login">Login</button>
</form>

<?php
include_once 'footer.php';
?>
