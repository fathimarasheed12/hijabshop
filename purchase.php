<?php
include 'db.php';

$sql_hijabs = "SELECT hijab_id, hijab_name FROM hijabs";
$result_hijabs = $conn->query($sql_hijabs);

if (isset($_GET['hijab_id'], $_GET['from_date'], $_GET['to_date'])) {
    $hijabId = $_GET['hijab_id'];
    $fromDate = $_GET['from_date'];
    $toDate = $_GET['to_date'];
    
    if ($hijabId == '0') {
        $sql_purchase = "
            SELECT 
                p.purchase_id, 
                p.date, 
                p.hijab_id, 
                p.dealer_id, 
                p.quantity AS purchased_qty, 
                p.purchase_price, 
                h.hijab_name
            FROM 
                purchase p
            JOIN 
                hijabs h ON p.hijab_id = h.hijab_id
            WHERE 
                p.date BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql_purchase);
        $stmt->bind_param("ss", $fromDate, $toDate);
    } else {
        $sql_purchase = "
            SELECT 
                p.purchase_id, 
                p.date, 
                p.hijab_id, 
                p.dealer_id, 
                p.quantity AS purchased_qty, 
                p.purchase_price, 
                h.hijab_name
            FROM 
                purchase p
            JOIN 
                hijabs h ON p.hijab_id = h.hijab_id
            WHERE 
                p.hijab_id = ? AND p.date BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql_purchase);
        $stmt->bind_param("sss", $hijabId, $fromDate, $toDate);
    }
    $stmt->execute();
    $result_purchase = $stmt->get_result();
    $purchases = $result_purchase->fetch_all(MYSQLI_ASSOC);
    echo json_encode($purchases);
    exit;
} else {
    $sql_purchase = "
        SELECT 
            p.purchase_id, 
            p.date, 
            p.hijab_id, 
            p.dealer_id, 
            p.quantity AS purchased_qty, 
            p.purchase_price, 
            h.hijab_name
        FROM 
            purchase p
        JOIN 
            hijabs h ON p.hijab_id = h.hijab_id";
    $result_purchase = $conn->query($sql_purchase);
    $purchases = $result_purchase->fetch_all(MYSQLI_ASSOC);
}

if (isset($_GET['purchase_id'])) {
    $purchaseId = $_GET['purchase_id'];
    $sql = "
        SELECT 
            p.purchase_id, 
            p.hijab_id, 
            p.bill_no, 
            p.payment_type, 
            p.quantity, 
            p.purchase_price, 
            (p.quantity * p.purchase_price) AS total_amount, 
            p.dealer_id, 
            d.dealer_name, 
            d.contact_info, 
            d.address
        FROM 
            purchase p
        JOIN 
            dealer d ON p.dealer_id = d.dealer_id
        WHERE 
            p.purchase_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $purchaseId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Purchase not found']);
    }
    exit;
}

if (isset($_GET['dealer_id'])) {
    $dealerId = $_GET['dealer_id'];

    $sql = "SELECT dealer_id, dealer_name, contact_info, address FROM dealer WHERE dealer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $dealerId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Dealer not found']);
    }
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="purchase.css">
    <title>Purchase Report</title>
</head>
<body>
    <h1 class="page-title">Purchase Report</h1>
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
            <input type="date" id="from-date">
        </div>
        <div>
            <label for="to-date">To:</label>
            <input type="date" id="to-date">
        </div>
        <div>
            <label for="sort-by">Sort By:</label>
            <select id="sort-by" onchange="applySorting()">
                <option value="name">Hijab Name (A-Z)</option>
                <option value="recent">Most Recent First</option>
            </select>
        </div>
        <button onclick="loadPurchaseReport()">Filter</button>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th>Purchase ID</th>
                <th>Date</th>
                <th>Hijab Name</th>
        <th>Dealer ID</th>
        <th>Purchased Quantity</th>
        <th>Purchase Price</th>
        <th>Action</th>
    </tr>
</thead>
<tbody id="report-body">
    <?php foreach ($purchases as $purchase): ?>
        <tr>
            <td><?= $purchase['purchase_id']; ?></td>
            <td><?= $purchase['date']; ?></td>
            <td><?= $purchase['hijab_name']; ?></td>
            <td>
                <span class="dealer-id" 
                      data-dealer-id="<?= $purchase['dealer_id']; ?>" 
                      style="color:blue; cursor:pointer; text-decoration:underline;">
                    <?= $purchase['dealer_id']; ?>
                </span>
            </td>
            <td><?= $purchase['purchased_qty']; ?></td>
            <td><?= $purchase['purchase_price']; ?></td>
            <td>
                <button onclick="viewPurchaseDetails('<?= $purchase['purchase_id']; ?>')">View</button>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
</table>

<!-- Purchase Details Modal -->
<div id="purchaseModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Purchase Details</h2>
        <div id="purchasedetails"></div>
    </div>
</div>

<!-- Dealer Details Modal -->
<div id="dealerModal">
    <div class="modal-content">
        <span class="closes" onclick="closeDealerModal()">&times;</span>
        <h2>Dealer Details</h2>
        <div id="dealerDetails"></div>
    </div>
</div>

<div class="reset-button-container">
    <button class="reset-button" onclick="resetAll()">Reset</button>
</div>
<div class="reset-button-container">
    <button class="back-button" onclick="window.location.href='inventory.php'">Back to Inventory</button>
</div>
<script>
let currentPurchases = [];
window.onload = function () {
    const currentDate = new Date().toISOString().split('T')[0];
    document.getElementById('to-date').value = currentDate;
    loadPurchaseReport();
    const savedSortBy = localStorage.getItem('sortBy') || 'recent'; // Default to recent if not saved
    document.getElementById('sort-by').value = savedSortBy;
    applySorting(); // Apply sorting based on the saved value
    const savedViewType = localStorage.getItem('viewType') || 'list'; // Default to list view
    applyView(savedViewType);
    const referrer = document.referrer;
    const backButton = document.querySelector('.back-button-container');
    if (referrer.includes('inventory.php')) {
        backButton.style.display = 'block';
    } else {
        backButton.style.display = 'none';
    }
};

function loadPurchaseReport() {
    const hijabId = document.getElementById('hijab-select').value;
    const fromDate = document.getElementById('from-date').value;
    const toDate = document.getElementById('to-date').value;

    // Prevent the request if no hijab is selected
    if (hijabId === "0" && !fromDate && !toDate) {
        document.getElementById("report-body").innerHTML = "";
        return; 
    }

    const xhr = new XMLHttpRequest();
    xhr.open('GET', `?hijab_id=${hijabId}&from_date=${fromDate}&to_date=${toDate}`, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            currentPurchases = JSON.parse(xhr.responseText);
            applySorting();
        }
    };
    xhr.send();
}

function applySorting() {
    const sortBy = document.getElementById('sort-by').value;
    localStorage.setItem('sortBy', sortBy);
    let sortedPurchases = [...currentPurchases];
    
    if (sortBy === 'name') {
        sortedPurchases.sort((a, b) => a.hijab_name.localeCompare(b.hijab_name));
    } else if (sortBy === 'recent') {
        sortedPurchases.sort((a, b) => new Date(b.date) - new Date(a.date));
    }
    
    displayPurchases(sortedPurchases);
}

function displayPurchases(purchases) {
    const reportBody = document.getElementById('report-body');
    const reportHeader = document.querySelector('.report-table thead');
    reportBody.innerHTML = '';

    if (document.body.classList.contains('grid-view')) {
        // Hide table header in grid view
        if (reportHeader) {
            reportHeader.style.display = 'none';
        }

        purchases.forEach(purchase => {
            const purchaseDiv = document.createElement('div');
            purchaseDiv.classList.add('grid-item');

            purchaseDiv.innerHTML = `
                <p><strong>Purchase ID:</strong> ${purchase.purchase_id}</p>
                <p><strong>Date:</strong> ${purchase.date}</p>
                <p><strong>Hijab Name:</strong> ${purchase.hijab_name} (ID: ${purchase.hijab_id})</p>
                <p><strong>Dealer ID:</strong> 
                    <span class="dealer-id" onclick="loadDealerDetails('${purchase.dealer_id}')">
                        ${purchase.dealer_id}
                    </span>
                </p>
                <p><strong>Purchased Quantity:</strong> ${purchase.purchased_qty}</p>
                <p><strong>Purchase Price:</strong> ${purchase.purchase_price}</p>
                <button onclick="viewPurchaseDetails('${purchase.purchase_id}')">View</button>
            `;

            reportBody.appendChild(purchaseDiv);
        });

    } else {
        // Show table header in table view
        if (reportHeader) {
            reportHeader.style.display = 'table-header-group';
        }

        purchases.forEach(purchase => {
            const row = document.createElement('tr');

            row.innerHTML = `
                <td>${purchase.purchase_id}</td>
                <td>${purchase.date}</td>
                <td>${purchase.hijab_name} (ID: ${purchase.hijab_id})</td>
                <td>
                    <span class="dealer-id" onclick="loadDealerDetails('${purchase.dealer_id}')">
                        ${purchase.dealer_id}
                    </span>
                </td>
                <td>${purchase.purchased_qty}</td>
                <td>${purchase.purchase_price}</td>
                <td><button onclick="viewPurchaseDetails('${purchase.purchase_id}')">View</button></td>
            `;

            reportBody.appendChild(row);
        });
    }
}

function viewPurchaseDetails(purchaseId) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `?purchase_id=${purchaseId}`, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            const dataDiv = document.getElementById('purchasedetails');
            if (data.error) {
                dataDiv.innerHTML = '<p>Purchase details not found.</p>';
            } else {
                dataDiv.innerHTML = `
                    <p><strong>Purchase ID:</strong> ${data.purchase_id}</p>
                    <p><strong>Hijab ID:</strong> ${data.hijab_id}</p>
                    <p><strong>Bill No:</strong> ${data.bill_no}</p>
                    <p><strong>Payment Type:</strong> ${data.payment_type}</p>
                    <p><strong>Total Amount:</strong> ${data.total_amount}</p>
                    <p><strong>Dealer ID:</strong> ${data.dealer_id}</p>
                    <p><strong>Dealer Name:</strong> ${data.dealer_name}</p>
                    <p><strong>Contact Info:</strong> ${data.contact_info}</p>
                    <p><strong>Address:</strong> ${data.address}</p>
                `;
            }
            document.getElementById('purchaseModal').style.display = 'block';
        }
    };
    xhr.send();
}

function closeModal() {
    document.getElementById('purchaseModal').style.display = 'none';
}

function resetAll() {
    document.getElementById('hijab-select').value = '0';
    document.getElementById('from-date').value = '';
    document.getElementById('to-date').value = new Date().toISOString().split('T')[0];
    currentPurchases = [];
    loadPurchaseReport();
}

function loadDealerDetails(dealerId) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `?dealer_id=${dealerId}`, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            const dealer = JSON.parse(xhr.responseText);
            if (dealer.error) {
                alert(dealer.error);
                return;
            }
            document.getElementById('dealerDetails').innerHTML = `
                <p><strong>Dealer ID:</strong> ${dealer.dealer_id}</p>
                <p><strong>Name:</strong> ${dealer.dealer_name}</p>
                <p><strong>Contact:</strong> ${dealer.contact_info}</p>
                <p><strong>Address:</strong> ${dealer.address}</p>
            `;
            document.getElementById('dealerModal').style.display = 'block';
        }
    };
    xhr.send();
}

function closeDealerModal() {
    document.getElementById('dealerModal').style.display = 'none';
}
</script>
</body>
</html>
