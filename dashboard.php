<?php
// Start session if it's required for user authentication
session_start();

// Include any necessary authentication or session checks
// if (!isset($_SESSION['admin'])) {
//     header('Location: login.php'); //Redirect to login if not logged in
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"  
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="dashboard.css">
  <title>Hijab Shop Admin Dashboard</title>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">Welcome Admin</div>
    <ul class="nav-links">
      <li><a href="dashboard.php?page=home" class="home-link"><i class="fa-solid fa-house"></i> Home</a></li>
      <li><a href="dashboard.php?page=addHijab"><i class="fa-solid fa-plus"></i> Add Hijab</a></li>
      <li><a href="dashboard.php?page=viewHijab"><i class="fa-solid fa-eye"></i> View/Edit Hijab</a></li>
      <li><a href="dashboard.php?page=inventory">Inventory</a></li>
      <li><a href="dashboard.php?page=purchase">Purchase Report</a></li>
      <li><a href="dashboard.php?page=sales">Sales Report</a></li>
      <li><a href="dashboard.php?page=users"><i class="fa-solid fa-users"></i> View Users</a></li>
      <li><a href="dashboard.php?page=addDealer">Add Dealer</a></li>
      <li><a href="dashboard.php?page=updateStock">Update Stock</a></li>
      <li><a href="dashboard.php?page=orderhistory">Order History</a></li>
    </ul>
  </div>

  <!-- Main Content Area -->
  <div class="main-content" id="main-content">
    <?php
    // Default page
    $page = 'home';
    
    // Check if a page is specified in the URL
    if(isset($_GET['page'])) {
      $page = $_GET['page'];
    }
    
    // Security check: prevent directory traversal
    $page = str_replace(['../', '..\\', '/', '\\'], '', $page);
    
    // Include the requested page
    $file_path = $page . '.php';
    if(file_exists($file_path)) {
      include($file_path);
    } else {
      echo '<div class="error-message">Page not found</div>';
    }
    ?>
  </div>

  <!-- Logout Button -->
  <button class="logout-btn" onclick="logout()">Logout</button>

  <script>
    // Make the current page's nav link active
    document.addEventListener('DOMContentLoaded', function() {
      // Get current page from URL
      const urlParams = new URLSearchParams(window.location.search);
      const currentPage = urlParams.get('page') || 'home';
      
      // Remove active class from all links
      document.querySelectorAll(".nav-links a").forEach(link => {
        link.classList.remove("active");
      });
      
      // Add active class to current page link
      document.querySelectorAll(".nav-links a").forEach(link => {
        const href = link.getAttribute("href");
        if (href && href.includes(`page=${currentPage}`)) {
          link.classList.add("active");
        }
      });
      
      // Store the current page in localStorage
      localStorage.setItem("lastContent", currentPage);
    });

    // Check for last visited page on load
    window.onload = () => {
      // Only redirect if we're on the base dashboard page with no parameters
      if (!window.location.search) {
        const isLoggedIn = sessionStorage.getItem("loggedIn"); 
        const lastContent = localStorage.getItem("lastContent");
        
        if (!isLoggedIn) {
          localStorage.removeItem("lastContent");
          sessionStorage.setItem("loggedIn", "true");
        } else if (lastContent && lastContent !== 'home') {
          // Redirect to last visited page
          window.location.href = `dashboard.php?page=${lastContent}`;
          return;
        }
      }
    };

    // Logout function
    function logout() {
      localStorage.removeItem("lastContent");
      sessionStorage.removeItem("loggedIn");
      window.location.href = "login.php";
    }
  </script>
</body>
</html>