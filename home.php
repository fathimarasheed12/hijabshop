<?php 

include 'db.php';

function getCount($conn, $table) {
    $query = "SELECT COUNT(*) AS count FROM $table";
    $result = $conn->query($query);
    if (!$result) {
        die("Query failed for table $table: " . $conn->error);
    }
    return $result->fetch_assoc()['count'];
}

function getSum($conn, $table, $column) {
    $query = "SELECT SUM($column) AS total FROM $table";
    $result = $conn->query($query);
    if (!$result) {
        die("Query failed for table $table: " . $conn->error);
    }
    return $result->fetch_assoc()['total'] ? $result->fetch_assoc()['total'] : 0;
}

// Queries to count records
$hijabCount = getCount($conn, 'hijabs');
$userCount = getCount($conn, 'users');
$dealerCount = getCount($conn, 'dealer');
$orderCount = getCount($conn, 'orders');

// Get total sold hijabs count
$soldHijabsCount = getSum($conn, 'sales', 'quantity');

// Get total purchased hijabs count
$purchasedHijabsCount = getSum($conn, 'purchase', 'quantity');

$conn->close();

?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" 
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="home.css">
    <title>Hijab Shop Dashboard</title>
</head>
<body>
    <div class="header">
        <h1>Hijab Shop Dashboard</h1>
    </div>
    <div class="container">
        <a href="viewHijab.php" class="card">
            <div class="icon"><i class="fa-solid fa-scarf"></i></div>
            <h3>Total Hijabs</h3>
            <p><?php echo $hijabCount; ?></p>
        </a>
        <a href="users.php" class="card">
            <div class="icon"><i class="fa-solid fa-users"></i></div>
            <h3>Total Users</h3>
            <p><?php echo $userCount; ?></p>
        </a>
        <a href="addDealer.php" class="card">
            <div class="icon"><i class="fa-solid fa-handshake"></i></div>
            <h3>Total Dealers</h3>
            <p><?php echo $dealerCount; ?></p>
        </a>
        
        <a href="purchase.php" class="card">
            <div class="icon"><i class="fa-solid fa-truck-loading"></i></div>
            <h3>Total Purchased Hijabs</h3>
            <p><?php echo $purchasedHijabsCount; ?></p>
        </a>

        <a href="sales.php" class="card">
            <div class="icon"><i class="fa-solid fa-scarf"></i></div>
            <h3>Total Sold Hijabs</h3>
            <p><?php echo $soldHijabsCount; ?></p>
        </a>
        <a href="orderhistory.php" class="card">
            <div class="icon"><i class="fa-solid fa-shopping-cart"></i></div>
            <h3>Total Orders</h3>
            <p><?php echo $orderCount; ?></p>
        </a>
    </div>
    <div class="footer">
        <p>&copy; 2025 Hijab Shop Inc. All Rights Reserved.</p>
    </div>
</body>
</html>