<?php include_once ('db.php');?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database</title>
<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: inventory.php");
    exit;
}
?>

<a href="register.php">Register</a> | <a href="login.php">Login</a>
</body>
</html>