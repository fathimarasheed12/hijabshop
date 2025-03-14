<?php
include 'db.php';

$sql_hijabs = "SELECT hijab_id, hijab_name FROM hijabs";
$result_hijabs = $conn->query($sql_hijabs);

if (isset($_GET['hijab_id'], $_GET['from_date'], $_GET['to_date'])) {
    $hijabId = $_GET['hijab_id'];
    $fromDate = $_GET['from_date'];
    $toDate = $_GET['to_date'];
    
    if ($hijabId == '0') {
        $sql_sales = "
            SELECT 
                s.sale_id, 
                s.date, 
                s.hijab_id, 
                s.customer_id, 
                s.quantity AS sold_qty, 
                s.sold_price, 
                h.hijab_name
            FROM 
                sales s
            JOIN 
                hijabs h ON s.hijab_id = h.hijab_id
            WHERE 
                s.date BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql_sales);
        $stmt->bind_param("ss", $fromDate, $toDate);
    } else {
        $sql_sales = "
            SELECT 
                s.sale_id, 
                s.date, 
                s.hijab_id, 
                s.customer_id, 
                s.quantity AS sold_qty, 
                s.sold_price, 
                h.hijab_name
            FROM 
                sales s
            JOIN 
                hijabs h ON s.hijab_id = h.hijab_id
            WHERE 
                s.hijab_id = ? AND s.date BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql_sales);
        $stmt->bind_param("sss", $hijabId, $fromDate, $toDate);
    }
    $stmt->execute();
    $result_sales = $stmt->get_result();
    $sales = $result_sales->fetch_all(MYSQLI_ASSOC);
    echo json_encode($sales);
    exit;
} else {
    $sql_sales = "
        SELECT 
            s.sale_id, 
            s.date, 
            s.hijab_id, 
            s.customer_id, 
            s.quantity AS sold_qty, 
            s.sold_price, 
            h.hijab_name
        FROM 
            sales s
        JOIN 
            hijabs h ON s.hijab_id = h.hijab_id";
    $result_sales = $conn->query($sql_sales);
    $sales = $result_sales->fetch_all(MYSQLI_ASSOC);
}

if (isset($_GET['sale_id'])) {
    $saleId = $_GET['sale_id'];
    $sql = "
        SELECT 
            s.sale_id, 
            s.hijab_id, 
            s.bill_no, 
            s.payment_type, 
            s.quantity, 
            s.sold_price, 
            (s.quantity * s.sold_price) AS total_amount, 
            c.customer_name, 
            c.customer_address, 
            c.contact_info
        FROM 
            sales s
        JOIN 
            customers c ON s.customer_id = c.customer_id
        WHERE 
            s.sale_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $saleId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Sale not found']);
    }
    exit;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
  />
  <link rel="stylesheet" href="sales.css">
  <title>Sales Report</title>
  
</head>
<body>
  <h1 class="page-title">Sales Report</h1>
  <div class="filter-section">
    <div>
      <label for="hijab-select">Hijab Name:</label>
      <select id="hijab-select" name="hijab" onchange="filterSalesByHijab()">
        <option value="0">Select Record (All Hijabs)</option>
        <?php
          $selectedHijabName = isset($_GET['hijabName']) ? $_GET['hijabName'] : '';
          while ($row = $result_hijabs->fetch_assoc()):
            $isSelected = ($row['hijab_name'] === $selectedHijabName) ? 'selected' : '';
        ?>
          <option value="<?= $row['hijab_id']; ?>" <?= $isSelected; ?>>
            <?= $row['hijab_name']; ?> (ID: <?= $row['hijab_id']; ?>)
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div>
      <label for="from-date">From:</label>
      <input type="date" id="from-date" />
    </div>
    <div>
      <label for="to-date">To:</label>
      <input type="date" id="to-date" />
    </div>
    <div>
      <label for="sort-by">Sort By:</label>
      <select id="sort-by" onchange="applySorting()">
        <option value="name">Hijab Name (A-Z)</option>
        <option value="recent">Most Recent First</option>
      </select>
    </div>
    <button onclick="loadSalesReport()">Filter</button>
  </div>
  <table class="report-table">
    <thead>
      <tr>
        <th>Sale ID</th>
        <th>Date</th>
        <th>Hijab Name</th>
        <th>Customer ID</th>
        <th>Sold Quantity</th>
        <th>Sold Price</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="sales-table-body">
      <?php foreach ($sales as $sale): ?>
        <tr>
          <td><?= $sale['sale_id']; ?></td>
          <td><?= $sale['date']; ?></td>
          <td><?= $sale['hijab_name']; ?></td>
          <td>
            <span class="customer-id" 
                  data-customer-id="<?= $sale['customer_id']; ?>" 
                  style="color:blue; cursor:pointer; text-decoration:underline;">
              <?= $sale['customer_id']; ?>
            </span>
          </td>
          <td><?= $sale['sold_qty']; ?></td>
          <td><?= $sale['sold_price']; ?></td>
          <td>
            <button onclick="viewSaleDetails('<?= $sale['sale_id']; ?>')">View</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Customer Details Modal -->
  <div id="customerModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div class="modal-content" style="background:white; padding:20px; width:50%; margin:10% auto; border-radius:8px;">
      <span class="close" onclick="closeCustomerModal()" style="float:right; cursor:pointer; color:red; font-size:22px;">&times;</span>
      <h2>Customer Details</h2>
      <p><strong>Name:</strong> <span id="customerName"></span></p>
      <p><strong>Address:</strong> <span id="customerAddress"></span></p>
      <p><strong>Contact Info:</strong> <span id="customerContact"></span></p>
    </div>
  </div>

  <!-- Sale Details Modal -->
  <div id="salesModal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h2>Sale Details</h2>
      <div id="saledetails"></div>
    </div>
  </div>

  <div class="reset-button-container">
    <button class="reset-button" onclick="resetAll()">Reset</button>
  </div>
  <div class="reset-button-container">
    <button class="back-button" onclick="window.location.href='inventory.php'">Back to Inventory</button>
  </div>
  <script>
    let currentSales = [];

    window.onload = function () {
      const currentDate = new Date().toISOString().split('T')[0];
      document.getElementById('to-date').value = currentDate;
      loadSalesReport();
      const savedSortBy = localStorage.getItem('sortBy') || 'recent';
      document.getElementById('sort-by').value = savedSortBy;
      applySorting();
      
      // Apply saved view type
      const savedViewType = localStorage.getItem('viewType') || 'list';
      document.body.classList.remove('list-view', 'grid-view');
      document.body.classList.add(savedViewType + '-view');
      
      const referrer = document.referrer;
      const backButton = document.querySelector('.back-button-container');
      if (referrer.includes('inventory.php')) {
        backButton.style.display = 'block';
      } else {
        backButton.style.display = 'none';
      }
    };

    function loadSalesReport() {
      const hijabId = document.getElementById('hijab-select').value;
      const fromDate = document.getElementById('from-date').value;
      const toDate = document.getElementById('to-date').value;

      if (hijabId === "0" && !fromDate && !toDate) {
        document.getElementById("sales-table-body").innerHTML = "";
        return;
      }

      const xhr = new XMLHttpRequest();
      xhr.open('GET', `?hijab_id=${hijabId}&from_date=${fromDate}&to_date=${toDate}`, true);
      xhr.onload = function () {
        if (xhr.status === 200) {
          currentSales = JSON.parse(xhr.responseText);
          applySorting();
        }
      };
      xhr.send();
    }

    function applySorting() {
      const sortBy = document.getElementById('sort-by').value;
      localStorage.setItem('sortBy', sortBy);
      let sortedSales = [...currentSales];
      
      if (sortBy === 'name') {
        sortedSales.sort((a, b) => a.hijab_name.localeCompare(b.hijab_name));
      } else if (sortBy === 'recent') {
        sortedSales.sort((a, b) => new Date(b.date) - new Date(a.date));
      }
      
      displaySales(sortedSales);
    }

    function displaySales(sales) {
      const reportBody = document.getElementById('sales-table-body');
      const reportHeader = document.querySelector('.report-table thead');
      reportBody.innerHTML = '';

      if (document.body.classList.contains('grid-view')) {
        if (reportHeader) {
          reportHeader.style.display = 'none';
        }
        sales.forEach(sale => {
          const saleDiv = `
            <div class="grid-item">
              <p><strong>Sale ID:</strong> ${sale.sale_id}</p>
              <p><strong>Date:</strong> ${sale.date}</p>
              <p><strong>Hijab Name:</strong> ${sale.hijab_name} (ID: ${sale.hijab_id})</p>
              <p>
                <strong>Customer ID:</strong>
                <span class="customer-id" onclick="fetchCustomerDetails(this)" data-customer-id="${sale.customer_id}" style="color:blue; cursor:pointer; text-decoration:underline;">
                  ${sale.customer_id}
                </span>
              </p>
              <p><strong>Sold Quantity:</strong> ${sale.sold_qty}</p>
              <p><strong>Sold Price:</strong> ${sale.sold_price}</p>
              <button onclick="viewSaleDetails('${sale.sale_id}')">View</button>
            </div>
          `;
          reportBody.innerHTML += saleDiv;
        });
      } else {
        if (reportHeader) {
          reportHeader.style.display = 'table-header-group';
        }
        sales.forEach(sale => {
          const row = `
            <tr>
              <td>${sale.sale_id}</td>
              <td>${sale.date}</td>
              <td>${sale.hijab_name} (ID: ${sale.hijab_id})</td>
              <td>
                <span class="customer-id" onclick="fetchCustomerDetails(this)" data-customer-id="${sale.customer_id}" style="color:blue; cursor:pointer; text-decoration:underline;">
                  ${sale.customer_id}
                </span>
              </td>
              <td>${sale.sold_qty}</td>
              <td>${sale.sold_price}</td>
              <td><button onclick="viewSaleDetails('${sale.sale_id}')">View</button></td>
            </tr>
          `;
          reportBody.innerHTML += row;
        });
      }
    }

    function viewSaleDetails(saleId) {
      const xhr = new XMLHttpRequest();
      xhr.open('GET', `?sale_id=${saleId}`, true);
      xhr.onload = function () {
        if (xhr.status === 200) {
          const data = JSON.parse(xhr.responseText);
          const dataDiv = document.getElementById('saledetails');
          if (data.error) {
            dataDiv.innerHTML = '<p>Sale details not found.</p>';
          } else {
            dataDiv.innerHTML = `
              <p><strong>Sale ID:</strong> ${data.sale_id}</p>
              <p><strong>Hijab ID:</strong> ${data.hijab_id}</p>
              <p><strong>Bill No:</strong> ${data.bill_no}</p>
              <p><strong>Payment Type:</strong> ${data.payment_type}</p>
              <p><strong>Total Amount:</strong> ${data.total_amount}</p>
              <p><strong>Customer Name:</strong> ${data.customer_name}</p>
              <p><strong>Contact Info:</strong> ${data.contact_info}</p>
              <p><strong>Address:</strong> ${data.customer_address}</p>
            `;
          }
          document.getElementById('salesModal').style.display = 'block';
        }
      };
      xhr.send();
    }

    function closeModal() {
      document.getElementById('salesModal').style.display = 'none';
    }

    function resetAll() {
      document.getElementById('hijab-select').value = '0';
      document.getElementById('from-date').value = '';
      document.getElementById('to-date').value = new Date().toISOString().split('T')[0];
      currentSales = [];
      loadSalesReport();
    }

    function fetchCustomerDetails(element) {
      const customerId = element.getAttribute("data-customer-id");
      fetch("fetch_customer.php?customer_id=" + customerId)
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            alert("Customer not found!");
          } else {
            document.getElementById("customerName").innerText = data.customer_name;
            document.getElementById("customerAddress").innerText = data.customer_address;
            document.getElementById("customerContact").innerText = data.contact_info;
            document.getElementById("customerModal").style.display = "block";
          }
        })
        .catch(error => console.error("Error fetching customer details:", error));
    }

    function closeCustomerModal() {
      document.getElementById("customerModal").style.display = "none";
    }
  </script>
</body>
</html>