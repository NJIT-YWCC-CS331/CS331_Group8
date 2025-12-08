<!DOCTYPE html>
<html lang="en">
<head>
<title>W3.CSS Template</title>
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
    <a href="./account.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-white">account</a>
    <a href="./logout.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">log out</a>
  </div>

</div>

<!-- Header -->
<header class="w3-container w3-red w3-center" style="padding:128px 16px">
  <h1 class="w3-margin w3-jumbo">YOUR ACCOUNT</h1>
  <p class="w3-xlarge">cars you rent</p>
</header>

        <?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
        <?php
$cid = $_SESSION['user_id'];

$pdo = new PDO("mysql:host=localhost;dbname=mysql;charset=utf8","dbuser","dbpass");

$updateMessage = '';

// Handle form submission for updating user info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');
    
    // Basic validation
    $errors = [];
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    
    if (empty($errors)) {
        // Update user information
        $updateStmt = $pdo->prepare("
            UPDATE Customer 
            SET name = ?, email = ?, phone = ?, address = ?, date_of_birth = ?
            WHERE customer_id = ?
        ");
        try {
            $updateStmt->execute([$name, $email, $phone, $address, $date_of_birth === '' ? null : $date_of_birth, $cid]);
            $updateMessage = '<div class="w3-panel w3-green"><p>Account information updated successfully!</p></div>';
        } catch (PDOException $e) {
            $updateMessage = '<div class="w3-panel w3-red"><p>Error updating information: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
        }
    } else {
        $errorMessages = array_map('htmlspecialchars', $errors);
        $updateMessage = '<div class="w3-panel w3-red"><p>' . implode('<br>', $errorMessages) . '</p></div>';
    }
}

// Fetch user information
$userStmt = $pdo->prepare("
    SELECT name, email, phone, address, date_of_birth
    FROM Customer
    WHERE customer_id = ?
");
$userStmt->execute([$cid]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Handle case where user is not found
if (!$user) {
    $user = [];
}

// Fetch rental information
$stmt = $pdo->prepare("
    SELECT Car.*, Branch.address, Rental.start_date, Rental.end_date, Rental.daily_rate, Rental.total_cost
    FROM Rental
    JOIN Car ON Rental.car_id = Car.car_id
    JOIN Branch ON Car.branch_id = Branch.branch_id
    WHERE Rental.customer_id = ?
");
$stmt->execute([$cid]);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- User Information Card -->
<div class="w3-container" style="margin-top:20px; margin-bottom:40px; max-width:800px; margin-left:auto; margin-right:auto;">
    <?php echo $updateMessage; ?>
    <div class="w3-card-4">
        <div class="w3-container w3-red">
            <h2>Your Information</h2>
        </div>
        
        <!-- View Mode -->
        <div id="view-mode" class="w3-container" style="padding:20px;">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name'] ?? 'Not provided'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? 'Not provided'); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address'] ?? 'Not provided'); ?></p>
            <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($user['date_of_birth'] ?? 'Not provided'); ?></p>
            <button onclick="toggleEditMode()" class="w3-button w3-red w3-margin-top">Edit Info</button>
        </div>
        
        <!-- Edit Mode -->
        <div id="edit-mode" class="w3-container" style="padding:20px; display:none;">
            <form method="post" action="account.php">
                <p><label><strong>Name:</strong></label><br>
                <input type="text" name="name" class="w3-input w3-border" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required></p>
                
                <p><label><strong>Email:</strong></label><br>
                <input type="email" name="email" class="w3-input w3-border" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required></p>
                
                <p><label><strong>Phone:</strong></label><br>
                <input type="text" name="phone" class="w3-input w3-border" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"></p>
                
                <p><label><strong>Address:</strong></label><br>
                <input type="text" name="address" class="w3-input w3-border" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>"></p>
                
                <p><label><strong>Date of Birth:</strong></label><br>
                <input type="date" name="date_of_birth" class="w3-input w3-border" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>"></p>
                
                <button type="submit" name="update_info" class="w3-button w3-green w3-margin-right">Save Changes</button>
                <button type="button" onclick="toggleEditMode()" class="w3-button w3-gray">Cancel</button>
            </form>
        </div>
    </div>
</div>

<!-- Rentals Section -->
<div class="w3-container" style="margin-top:40px; margin-bottom:20px;">
    <h2 class="w3-center">Your Rented Cars</h2>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
<?php foreach ($cars as $c): ?>
    <div style="border:1px solid #ccc;padding:10px;">
        <strong><?php echo $c['brand'].' '.$c['model']; ?></strong><br>
        Start Date: <?php echo $c['start_date']; ?><br>
        End Date: <?php echo $c['end_date']; ?><br>
        Daily Rate: $<?php echo $c['daily_rate']; ?><br>
        Total Cost: $<?php echo $c['total_cost']; ?><br>

        License: <?php echo $c['license_plate']; ?><br>
        Year: <?php echo $c['year_of_manufacture']; ?><br>
        Status: <?php echo $c['rental_status']; ?><br>

        Branch: <?php echo $c['address']; ?>
        <!-- remove -->
        <form method="post" action="process_removal.php">
            <input type="hidden" name="car_id" value="<?php echo $c['car_id']; ?>">
            <button type="submit">Return Car</button>
        </form>
    </div>
<?php endforeach; ?>
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
// Used to toggle the menu on small screens when clicking on the menu button
function myFunction() {
  var x = document.getElementById("navDemo");
  if (x.className.indexOf("w3-show") == -1) {
    x.className += " w3-show";
  } else { 
    x.className = x.className.replace(" w3-show", "");
  }
}

// Toggle between view and edit mode for user information
function toggleEditMode() {
  var viewMode = document.getElementById("view-mode");
  var editMode = document.getElementById("edit-mode");
  
  if (editMode.style.display === "none" || editMode.style.display === "") {
    viewMode.style.display = "none";
    editMode.style.display = "block";
  } else {
    viewMode.style.display = "block";
    editMode.style.display = "none";
  }
}
</script>

</body>
</html>
