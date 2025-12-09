<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
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
<script defer src="https://weekoldroadkill.com/uma-app/script.js" data-website-id="0c05abaa-95e8-47be-b3e2-39ff97cabab1"></script>
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
    <a href="./rent.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-white">rent</a>
    <a href="./account.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">account</a>
    <a href="./logout.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white">log out</a>
  </div>

</div>

<!-- Header -->
<header class="w3-container w3-red w3-center" style="padding:128px 16px">
  <h1 class="w3-margin w3-jumbo">RENT CARS</h1>
  <p class="w3-xlarge">Very many cars for rent</p>
</header>


<?php
$pdo = new PDO("mysql:host=database;dbname=mysql;charset=utf8","dbuser","dbpass");

$stmt = $pdo->query("
    SELECT Car.*, Branch.address
    FROM Car
    JOIN Branch ON Car.branch_id = Branch.branch_id
");

$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;padding:20px;">
<?php foreach ($cars as $c): ?>
    <div class="w3-card-4" style="padding:10px;">
        <strong><?php echo htmlspecialchars($c['brand'].' '.$c['model']); ?></strong><br>
        License: <?php echo htmlspecialchars($c['license_plate']); ?><br>
        Year: <?php echo htmlspecialchars($c['year_of_manufacture']); ?><br>
        Status: <span class="<?php echo $c['rental_status'] === 'rented' ? 'w3-text-red' : 'w3-text-green'; ?>">
            <?php echo htmlspecialchars($c['rental_status']); ?>
        </span><br>
        Branch: <?php echo htmlspecialchars($c['address']); ?><br><br>
        <button 
            class="w3-button w3-red w3-block" 
            onclick="openRentalModal(<?php echo $c['car_id']; ?>, '<?php echo htmlspecialchars($c['brand'].' '.$c['model'], ENT_QUOTES); ?>', '<?php echo $c['rental_status']; ?>')"
            <?php echo $c['rental_status'] === 'rented' ? 'disabled' : ''; ?>
        >
            <?php echo $c['rental_status'] === 'rented' ? 'Not Available' : 'Rent this car'; ?>
        </button>
    </div>
<?php endforeach; ?>
</div>

<!-- Rental Modal -->
<div id="rentalModal" class="w3-modal">
    <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:600px">
        <div class="w3-center"><br>
            <span onclick="document.getElementById('rentalModal').style.display='none'" 
                  class="w3-button w3-xlarge w3-hover-red w3-display-topright" 
                  title="Close Modal">&times;</span>
            <h2 id="modalCarName">Rent Car</h2>
        </div>

        <form class="w3-container" method="POST" action="process_rent.php" id="rentalForm" onsubmit="return validateForm()">
            <div class="w3-section">
                <label><b>Start Date</b></label>
                <input class="w3-input w3-border w3-margin-bottom" 
                       type="date" 
                       name="start_date" 
                       id="startDate" 
                       required 
                       onchange="calculateTotal()">

                <label><b>End Date</b></label>
                <input class="w3-input w3-border w3-margin-bottom" 
                       type="date" 
                       name="end_date" 
                       id="endDate" 
                       required 
                       onchange="calculateTotal()">

                <label><b>Daily Rate ($)</b></label>
                <input class="w3-input w3-border w3-margin-bottom" 
                       type="number" 
                       name="daily_rate" 
                       id="dailyRate" 
                       min="1" 
                       step="0.01" 
                       required 
                       onchange="calculateTotal()" 
                       oninput="calculateTotal()">

                <label><b>Total Cost</b></label>
                <input class="w3-input w3-border w3-margin-bottom w3-light-grey" 
                       type="text" 
                       id="totalCostDisplay" 
                       readonly 
                       value="$0.00">
                
                <input type="hidden" name="total_cost" id="totalCost" value="0">
                <input type="hidden" name="rental_status" id="rentalStatus">
                <input type="hidden" name="car_id" id="carId">

                <div id="errorMessage" class="w3-panel w3-red w3-display-container" style="display:none;">
                    <span onclick="this.parentElement.style.display='none'"
                          class="w3-button w3-large w3-display-topright">&times;</span>
                    <p id="errorText"></p>
                </div>

                <button class="w3-button w3-block w3-red w3-section w3-padding" 
                        type="submit" 
                        id="submitBtn">Submit Rental</button>
            </div>
        </form>

        <div class="w3-container w3-border-top w3-padding-16 w3-light-grey">
            <button onclick="document.getElementById('rentalModal').style.display='none'" 
                    type="button" 
                    class="w3-button w3-red">Cancel</button>
        </div>
    </div>
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

// Open the rental modal and populate with car information
function openRentalModal(carId, carName, rentalStatus) {
    if (rentalStatus === 'rented') {
        alert('This car is currently not available for rent.');
        return;
    }
    
    document.getElementById('rentalModal').style.display = 'block';
    document.getElementById('modalCarName').textContent = 'Rent: ' + carName;
    document.getElementById('carId').value = carId;
    document.getElementById('rentalStatus').value = rentalStatus;
    
    // Set minimum date to today
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('startDate').setAttribute('min', today);
    document.getElementById('endDate').setAttribute('min', today);
    
    // Reset form
    document.getElementById('rentalForm').reset();
    document.getElementById('carId').value = carId;
    document.getElementById('rentalStatus').value = rentalStatus;
    document.getElementById('totalCostDisplay').value = '$0.00';
    document.getElementById('totalCost').value = '0';
    document.getElementById('errorMessage').style.display = 'none';
}

// Calculate total cost based on dates and daily rate
function calculateTotal() {
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;
    var dailyRate = parseFloat(document.getElementById('dailyRate').value) || 0;
    
    var errorDiv = document.getElementById('errorMessage');
    var errorText = document.getElementById('errorText');
    var submitBtn = document.getElementById('submitBtn');
    
    // Reset error state
    errorDiv.style.display = 'none';
    submitBtn.disabled = false;
    
    if (!startDate || !endDate || dailyRate <= 0) {
        document.getElementById('totalCostDisplay').value = '$0.00';
        document.getElementById('totalCost').value = '0';
        return;
    }
    
    var start = new Date(startDate);
    var end = new Date(endDate);
    
    // Validate date range
    if (end < start) {
        errorText.textContent = 'Error: End date must be on or after the start date.';
        errorDiv.style.display = 'block';
        submitBtn.disabled = true;
        document.getElementById('totalCostDisplay').value = '$0.00';
        document.getElementById('totalCost').value = '0';
        return;
    }
    
    // Calculate number of days (inclusive of both start and end dates)
    var timeDiff = end.getTime() - start.getTime();
    var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // +1 to include both days
    
    // Calculate total
    var total = daysDiff * dailyRate;
    
    document.getElementById('totalCostDisplay').value = '$' + total.toFixed(2);
    document.getElementById('totalCost').value = total.toFixed(2);
}

// Validate form before submission
function validateForm() {
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;
    var dailyRate = parseFloat(document.getElementById('dailyRate').value) || 0;
    
    if (!startDate || !endDate || dailyRate <= 0) {
        alert('Please fill in all fields.');
        return false;
    }
    
    var start = new Date(startDate);
    var end = new Date(endDate);
    
    if (end < start) {
        alert('End date must be on or after the start date.');
        return false;
    }
    
    return true;
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    var modal = document.getElementById('rentalModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

</body>
</html>
