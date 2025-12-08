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

        <?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=mysql;charset=utf8", "dbuser", "dbpass");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$message = '';
$message2 = '';

// insert rental record with customer_id from session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (!isset($_POST['start_date']) || !isset($_POST['end_date']) || 
        !isset($_POST['daily_rate']) || !isset($_POST['car_id']) || 
        !isset($_POST['rental_status'])) {
        $message = 'Error';
        $message2 = 'Missing required fields. Please try again.';
    } else {
        $start_date = trim($_POST['start_date']);
        $end_date = trim($_POST['end_date']);
        $daily_rate = trim($_POST['daily_rate']);
        $customer_id = $_SESSION['user_id'];
        $car_id = trim($_POST['car_id']);
        $rental_status = trim($_POST['rental_status']);

        // Validate date format
        $start_date_obj = DateTime::createFromFormat('Y-m-d', $start_date);
        $end_date_obj = DateTime::createFromFormat('Y-m-d', $end_date);

        if (!$start_date_obj || !$end_date_obj) {
            $message = 'Error';
            $message2 = 'Invalid date format. Please use a valid date.';
        } 
        // Validate date range - end date must be >= start date
        elseif ($end_date_obj < $start_date_obj) {
            $message = 'Error';
            $message2 = 'End date must be on or after the start date.';
        }
        // Validate daily rate
        elseif (!is_numeric($daily_rate) || $daily_rate <= 0) {
            $message = 'Error';
            $message2 = 'Daily rate must be a positive number.';
        }
        // Check if car is already rented
        elseif ($rental_status === 'rented') {
            $message = 'Error';
            $message2 = 'Car is not available for rent!';
        }
        else {
            // Recompute total cost server-side to prevent tampering
            // Calculate number of days (inclusive of both start and end dates)
            $interval = $start_date_obj->diff($end_date_obj);
            $days = $interval->days + 1; // +1 to include both start and end dates
            $total_cost = $days * floatval($daily_rate);

            // Double-check car availability in database before inserting
            $stmt = $pdo->prepare("SELECT rental_status FROM Car WHERE car_id = ?");
            $stmt->execute([$car_id]);
            $car = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$car) {
                $message = 'Error';
                $message2 = 'Car not found.';
            } elseif ($car['rental_status'] === 'rented') {
                $message = 'Error';
                $message2 = 'Car is not available for rent!';
            } else {
                // Update car status to rented
                $rental_status = 'rented';
                $stmt = $pdo->prepare("UPDATE Car SET rental_status = ? WHERE car_id = ?");
                $stmt->execute([$rental_status, $car_id]);

                // Insert rental record with recomputed total_cost
                $stmt = $pdo->prepare("INSERT INTO Rental (start_date, end_date, daily_rate, total_cost, customer_id, car_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$start_date, $end_date, $daily_rate, $total_cost, $customer_id, $car_id]);

                $message = 'Success!';
                $message2 = 'Your rental has been processed successfully. Total cost: $' . number_format($total_cost, 2);
            }
        }
    }
}

?>
<h1 class="w3-margin w3-jumbo"><?php echo $message; ?></h1>
<p class="w3-xlarge"><?php echo $message2; ?></p>
<a href="./account.php">
  <button class="w3-button w3-black w3-padding-large w3-large w3-margin-top" onclick="javascript:history.back()">Go back</button>
</a>
</header>

<!-- Rental ( -->
<!--     rental_id INT PRIMARY KEY, -->
<!--     start_date DATE, -->
<!--     end_date DATE, -->
<!--     daily_rate DECIMAL(10,2), -->
<!--     total_cost DECIMAL(10,2), -->
<!--     customer_id VARCHAR(255), -->
<!--     car_id INT, -->
<!--     FOREIGN KEY (customer_id) REFERENCES Customer(customer_id), -->
<!--     FOREIGN KEY (car_id) REFERENCES Car(car_id) -->
<!-- ) -->


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
</script>

</body>
</html>
