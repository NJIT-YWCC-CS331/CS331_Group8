<!DOCTYPE html>
<html lang="en">
<head>
<title>Admin - Transactions</title>
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
.section-header {
    margin-top: 30px;
    margin-bottom: 15px;
    padding: 10px;
    background-color: #f44336;
    color: white;
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
    <a href="./admin1.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">Admin Users</a>
    <a href="./admin2.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-white">Admin Transactions</a>
    <a href="./logout.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">log out</a>
  </div>
</div>

<!-- Header -->
<header class="w3-container w3-red w3-center" style="padding:128px 16px">
  <h1 class="w3-margin w3-jumbo">ADMIN - TRANSACTIONS</h1>
  <p class="w3-xlarge">All rentals and payments in the system</p>
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
    
    // Query all rentals with customer and car details
    $rentalsStmt = $pdo->query("
        SELECT 
            r.rental_id,
            r.customer_id,
            c.name as customer_name,
            c.email as customer_email,
            r.car_id,
            car.brand,
            car.model,
            car.license_plate,
            r.start_date,
            r.end_date,
            r.daily_rate,
            r.total_cost
        FROM Rental r
        JOIN Customer c ON r.customer_id = c.customer_id
        JOIN Car car ON r.car_id = car.car_id
        ORDER BY r.rental_id DESC
    ");
    
    $rentals = $rentalsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query all payments with rental details
    $paymentsStmt = $pdo->query("
        SELECT 
            p.payment_id,
            p.payment_date,
            p.amount,
            p.method,
            p.rental_id,
            r.customer_id,
            c.name as customer_name,
            c.email as customer_email
        FROM Payment p
        JOIN Rental r ON p.rental_id = r.rental_id
        JOIN Customer c ON r.customer_id = c.customer_id
        ORDER BY p.payment_id DESC
    ");
    
    $payments = $paymentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . htmlspecialchars($e->getMessage());
    $rentals = [];
    $payments = [];
}
?>

<div class="w3-container" style="margin-top:20px; padding:20px;">
    <?php if (isset($error)): ?>
        <div class="w3-panel w3-red">
            <h3>Error!</h3>
            <p><?php echo $error; ?></p>
        </div>
    <?php else: ?>
        
        <!-- Rentals Section -->
        <div class="section-header">
            <h2 style="margin:0;">Rental Transactions</h2>
        </div>
        
        <?php if (empty($rentals)): ?>
            <div class="empty-state">
                <i class="fa fa-car" style="font-size:100px; color:#ccc;"></i>
                <h3>No Rentals Found</h3>
                <p>There are no rental transactions in the system yet.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Rental ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Car</th>
                            <th>License Plate</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Daily Rate</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rentals as $rental): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rental['rental_id']); ?></td>
                                <td><?php echo htmlspecialchars($rental['customer_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($rental['customer_email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($rental['brand'] . ' ' . $rental['model']); ?></td>
                                <td><?php echo htmlspecialchars($rental['license_plate']); ?></td>
                                <td><?php echo htmlspecialchars($rental['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($rental['end_date']); ?></td>
                                <td>$<?php echo number_format($rental['daily_rate'], 2); ?></td>
                                <td>$<?php echo number_format($rental['total_cost'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:20px;">
                <p><strong>Total Rentals:</strong> <?php echo count($rentals); ?></p>
                <p><strong>Total Revenue from Rentals:</strong> $<?php 
                    echo number_format(array_sum(array_column($rentals, 'total_cost')), 2); 
                ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Payments Section -->
        <div class="section-header">
            <h2 style="margin:0;">Payment Transactions</h2>
        </div>
        
        <?php if (empty($payments)): ?>
            <div class="empty-state">
                <i class="fa fa-credit-card" style="font-size:100px; color:#ccc;"></i>
                <h3>No Payments Found</h3>
                <p>There are no payment transactions in the system yet.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Rental ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Payment Date</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                                <td><?php echo htmlspecialchars($payment['rental_id']); ?></td>
                                <td><?php echo htmlspecialchars($payment['customer_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($payment['customer_email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                                <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($payment['method'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:20px;">
                <p><strong>Total Payments:</strong> <?php echo count($payments); ?></p>
                <p><strong>Total Payment Amount:</strong> $<?php 
                    echo number_format(array_sum(array_column($payments, 'amount')), 2); 
                ?></p>
            </div>
        <?php endif; ?>
        
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
