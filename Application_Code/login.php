<?php
session_start();
include_once 'header.php';
require_once 'db_connection.php';
?>
<h2>Employee Login</h2>
<form action="login.php" method="POST">
  <label for="username">Username (SSN/SIN):</label>
  <input type="text" name="username" id="username" required>
<button type="submit" name="login">Login</button>
</form>
<?php
if (isset($_POST['login'])) {
    // Sanitize the input
    $username = trim($_POST['username']);
// Query to retrieve the employee's id and manager_role from employee table using sin
    $sql = "SELECT employee_id, manager_role FROM employee WHERE sin = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<p>Error preparing statement: " . $conn->error . "</p>";
        include_once 'footer.php';
        exit;
    }
$stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
// If one record is found, login is successful
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION['employee_logged_in'] = true;
        $_SESSION['employee_username'] = $username;
        $_SESSION['manager_role'] = $row['manager_role'];  // manager_role, 1 if manager, 0 if not
        echo "<p>Login successful. <a href='admin_dashboard.php'>Go to Dashboard</a></p>";
    } else {
        echo "<p>Invalid SSN/SIN.</p>";
    }
}
include_once 'footer.php';
?>
