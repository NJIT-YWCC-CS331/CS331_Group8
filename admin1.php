<!DOCTYPE html>
<html lang="en">
<head>
<title>Admin - Users List</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
body,h1,h2,h3,h4,h5,h6 {font-family: "Lato", sans-serif}
.w3-bar,h1,button {font-family: "Montserrat", sans-serif}
.fa-anchor,.fa-coffee {font-size:200px}
table {
    border-collapse: collapse;
    width: 100%;
}
th, td {
    text-align: left;
    padding: 12px;
    border-bottom: 1px solid #ddd;
}
th {
    background-color: #f44336;
    color: white;
}
tr:hover {
    background-color: #f5f5f5;
}
.empty-state {
    text-align: center;
    padding: 40px;
    color: #666;
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="w3-top">
  <div class="w3-bar w3-red w3-card w3-left-align w3-large">
    <a class="w3-bar-item w3-button w3-hide-medium w3-hide-large w3-right w3-padding-large w3-hover-white w3-large w3-red" href="javascript:void(0);" onclick="myFunction()" title="Toggle Navigation Menu"><i class="fa fa-bars"></i></a>
    <a href="./index.php" class="w3-bar-item w3-button w3-padding-large w3-hover-white">Home</a>
    <a href="./login.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">login</a>
    <a href="./rent.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">rent</a>
    <a href="./account.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">account</a>
    <a href="./admin1.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-white">Admin Users</a>
    <a href="./admin2.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">Admin Transactions</a>
    <a href="./logout.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">log out</a>
  </div>
</div>

<!-- Header -->
<header class="w3-container w3-red w3-center" style="padding:128px 16px">
  <h1 class="w3-margin w3-jumbo">ADMIN - USERS</h1>
  <p class="w3-xlarge">All registered users in the system</p>
</header>

<?php
session_start();

// Check if user is logged in
// Note: This app does not have a role-based system in the database.
// In a production environment, you should add a 'role' column to the Customer table
// and check if the user has admin privileges before allowing access to this page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Connect to database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mysql;charset=utf8","dbuser","dbpass");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query all customers with their rental count
    $stmt = $pdo->query("
        SELECT 
            c.customer_id,
            c.name,
            c.email,
            c.phone,
            c.address,
            c.date_of_birth,
            COUNT(r.rental_id) as total_rentals,
            COALESCE(SUM(r.total_cost), 0) as total_spent
        FROM Customer c
        LEFT JOIN Rental r ON c.customer_id = r.customer_id
        GROUP BY c.customer_id, c.name, c.email, c.phone, c.address, c.date_of_birth
        ORDER BY c.customer_id
    ");
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . htmlspecialchars($e->getMessage());
    $users = [];
}
?>

<div class="w3-container" style="margin-top:20px; padding:20px;">
    <?php if (isset($error)): ?>
        <div class="w3-panel w3-red">
            <h3>Error!</h3>
            <p><?php echo $error; ?></p>
        </div>
    <?php elseif (empty($users)): ?>
        <div class="empty-state">
            <i class="fa fa-users" style="font-size:100px; color:#ccc;"></i>
            <h2>No Users Found</h2>
            <p>There are no registered users in the system yet.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Date of Birth</th>
                        <th>Total Rentals</th>
                        <th>Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['customer_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['date_of_birth'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['total_rentals']); ?></td>
                            <td>$<?php echo number_format($user['total_spent'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top:20px;">
            <p><strong>Total Users:</strong> <?php echo count($users); ?></p>
        </div>
    <?php endif; ?>
</div>


<!-- Footer -->
<footer class="w3-container w3-padding-64 w3-center w3-opacity">  
  <div class="w3-xlarge w3-padding-32">
    <i class="fa fa-facebook-official w3-hover-opacity"></i>
    <i class="fa fa-instagram w3-hover-opacity"></i>
    <i class="fa fa-snapchat w3-hover-opacity"></i>
    <i class="fa fa-pinterest-p w3-hover-opacity"></i>
    <i class="fa fa-twitter w3-hover-opacity"></i>
    <i class="fa fa-linkedin w3-hover-opacity"></i>
 </div>
 <p>Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>
</footer>

<script>
// Mobile menu toggle function
function myFunction() {
  // Mobile menu functionality placeholder
  // The navbar uses W3.CSS responsive classes for mobile display
}
</script>

</body>
</html>
