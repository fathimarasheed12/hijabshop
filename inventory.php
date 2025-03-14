<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="inventory.css">
  <title>Hijab Inventory</title>

</head>
<body>
  <h1>Hijab Inventory</h1>
  <div class="notification-container"></div>
  <div class="bulk-actions">
    <button id="selectMode" class="bulk-action-btn">Select</button>
    <div id="actionButtons" class="hidden">
      <button id="viewPurchaseBtn" class="bulk-action-btn">View Purchase</button>
      <button id="viewSalesBtn" class="bulk-action-btn">View Sales</button>
      <button id="addDiscountBtn" class="bulk-action-btn">Add Discount</button>
      <button id="cancelSelect" class="bulk-action-btn">Cancel</button>
    </div>
  </div>
  <table class="inventory-table">
    <thead>
      <tr>
        <th class="checkbox-column hidden" id="checkboxHeader">
          <input type="checkbox" id="selectAll">
        </th>
        <th>S.No</th>
        <th>Hijab Image</th>
        <th>Hijab Details</th>
        <th>Discount</th>
        <th>Quantity</th>
        <th>Sold Hijabs</th>
      </tr>
    </thead>
    <tbody>
      <?php
      include 'db.php';
      
      // Modified query with proper syntax and GROUP BY
      $sql = "SELECT 
                a.hijab_id, 
                a.hijab_name, 
                a.category,  
                a.price, 
                a.image, 
                IFNULL(i.price_dis, 0) AS discount,
                (IFNULL(p.total_quantity, 0) - IFNULL(s.sold_quantity, 0)) AS inventory_quantity,
                IFNULL(s.sold_quantity, 0) AS sold_hijabs,
                IFNULL(i.dis_type, 'fixed') AS dis_type
            FROM hijabs a
            LEFT JOIN inventory i ON a.hijab_id = i.hijab_id
            LEFT JOIN (
                SELECT hijab_id, SUM(quantity) AS total_quantity
                FROM purchase
                GROUP BY hijab_id
            ) p ON a.hijab_id = p.hijab_id
            LEFT JOIN (
                SELECT hijab_id, SUM(quantity) AS sold_quantity
                FROM sales
                GROUP BY hijab_id
            ) s ON a.hijab_id = s.hijab_id";
            
      // Execute query with error handling
      $result = $conn->query($sql);
      
      // Check if the query was successful
      if ($result === false) {
        echo "<tr><td colspan='7'>Error executing query: " . $conn->error . "</td></tr>";
      } else if ($result->num_rows > 0) {
        $serialNo = 1;
        while ($row = $result->fetch_assoc()) {
          $hijabDetails = "
                    <div class='hijab-details'>
                        <div>
                            <p><strong>Name:</strong> {$row['hijab_name']}</p>
                            <p><strong>Category:</strong> {$row['category']}</p>
                            <p><strong>MRP:</strong> ₹{$row['price']}</p>
                        </div>
                    </div>";

          $discountDisplay = $row['dis_type'] === 'percentage'
            ? $row['discount'] . "%"
            : "₹" . $row['discount'];

          echo "<tr data-hijab-id='{$row['hijab_id']}' data-hijab-name='{$row['hijab_name']}'>
                            <td class='checkbox-column hidden'>
                                <input type='checkbox' class='row-checkbox'>
                            </td>
                            <td>{$serialNo}</td>
                            <td><img src='{$row['image']}' alt='{$row['hijab_name']}' class='hijab-image' /></td> 
                            <td>{$hijabDetails}</td>
                            <td>{$discountDisplay}</td> 
                            <td>{$row['inventory_quantity']}</td> 
                            <td>{$row['sold_hijabs']}</td>
                        </tr>";
          $serialNo++;
        }
      } else {
        echo "<tr><td colspan='7'>No inventory data available</td></tr>";
      }
      ?>
    </tbody>
  </table>

  <div id="discount-popup" style="display:none;">
    <div class="popup-content">
      <h2>Add Discount</h2>
      <div class="discount-type">
        <div>
          <input type="radio" id="percentage" name="discount_type" value="percentage">
          <label for="percentage">Percentage (%)</label>
        </div>
        <div>
          <input type="radio" id="fixed" name="discount_type" value="fixed" checked>
          <label for="fixed">Amount (₹)</label>
        </div>
      </div>
      <label for="discount" id="discount-label">Discount Value:</label>
      <input type="number" id="discount" min="0" required>
      <button id="apply-discount">Apply Discount</button>
      <button id="close-popup">Close</button>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    function showNotification(message, type = 'error') {
        const notificationContainer = document.querySelector('.notification-container');
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;

        notificationContainer.appendChild(notification);

        notification.offsetHeight; // Trigger reflow
        notification.classList.add('show');

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notificationContainer.removeChild(notification);
            }, 300);
        }, 3000);
    }

    const discountPopup = document.getElementById('discount-popup');
    const discountInput = document.getElementById('discount');
    const applyDiscountBtn = document.getElementById('apply-discount');
    const closePopupBtn = document.getElementById('close-popup');
    const selectModeBtn = document.getElementById('selectMode');
    const actionButtons = document.getElementById('actionButtons');
    const checkboxHeader = document.getElementById('checkboxHeader');
    const selectAllCheckbox = document.getElementById('selectAll');
    const checkboxColumns = document.querySelectorAll('tbody .checkbox-column');
    let isSelectMode = false;

    function toggleSelectMode() {
        isSelectMode = !isSelectMode;
        checkboxHeader.classList.toggle('hidden');
        checkboxColumns.forEach(col => col.classList.toggle('hidden'));
        actionButtons.classList.toggle('hidden');
        selectModeBtn.classList.toggle('hidden');

        if (!isSelectMode) {
            document.querySelectorAll('.row-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            selectAllCheckbox.checked = false;
        }
    }

    function getSelectedHijabs() {
        const selected = [];
        document.querySelectorAll('.row-checkbox:checked').forEach(checkbox => {
            const row = checkbox.closest('tr');
            selected.push({
                id: row.dataset.hijabId,
                name: row.dataset.hijabName
            });
        });
        return selected;
    }

    function updateActionButtonsVisibility() {
        const anyChecked = Array.from(document.querySelectorAll('.row-checkbox'))
            .some(checkbox => checkbox.checked);
        if (isSelectMode) {
            actionButtons.style.display = anyChecked ? 'flex' : 'flex';
        }
    }

    selectModeBtn.addEventListener('click', toggleSelectMode);
    document.getElementById('cancelSelect').addEventListener('click', toggleSelectMode);

    selectAllCheckbox.addEventListener('change', (e) => {
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
        updateActionButtonsVisibility();
    });

    document.getElementById('viewPurchaseBtn').addEventListener('click', () => {
        const selectedHijabs = getSelectedHijabs();
        if (selectedHijabs.length === 0) {
            showNotification('Please select a hijab');
            return;
        }
        if (selectedHijabs.length > 1) {
            showNotification('Please select only one hijab for viewing purchase details');
            return;
        }
        const firstHijab = selectedHijabs[0];
        window.location.href = `purchase.php?hijabId=${firstHijab.id}&hijabName=${encodeURIComponent(firstHijab.name)}`;
    });

    document.getElementById('viewSalesBtn').addEventListener('click', () => {
        const selectedHijabs = getSelectedHijabs();
        if (selectedHijabs.length === 0) {
            showNotification('Please select a hijab');
            return;
        }
        if (selectedHijabs.length > 1) {
            showNotification('Please select only one hijab for viewing sales details');
            return;
        }
        const firstHijab = selectedHijabs[0];
        window.location.href = `sales.php?hijabId=${firstHijab.id}&hijabName=${encodeURIComponent(firstHijab.name)}`;
    });

    document.getElementById('addDiscountBtn').addEventListener('click', () => {
        const selectedHijabs = getSelectedHijabs();
        if (selectedHijabs.length === 0) {
            showNotification('Please select at least one hijab');
            return;
        }
        discountPopup.style.display = 'block';
    });

    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateActionButtonsVisibility);
    });

    closePopupBtn.addEventListener('click', () => {
        discountPopup.style.display = 'none';
    });

    let discountType = 'fixed';
    document.querySelectorAll('input[name="discount_type"]').forEach(input => {
        input.addEventListener('change', () => {
            discountType = document.querySelector('input[name="discount_type"]:checked').value;
        });
    });

    applyDiscountBtn.addEventListener('click', () => {
        const discountValue = parseFloat(discountInput.value);
        const selectedHijabs = getSelectedHijabs();

        if (isNaN(discountValue) || discountValue < 0 || discountValue > 100000) {
            showNotification('Please enter a valid discount value');
            return;
        }

        let successCount = 0;
        let failureCount = 0;

        Promise.all(selectedHijabs.map(hijab =>
            fetch('apply_discount.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    'hijab_id': hijab.id,
                    'discount_value': discountValue,
                    'discount_type': discountType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    successCount++;
                } else {
                    failureCount++;
                }
            })
            .catch(() => {
                failureCount++;
            })
        )).then(() => {
            if (successCount > 0) {
                showNotification(`Discount applied successfully to ${successCount} hijab(s)`, 'success');
                discountPopup.style.display = 'none';
                window.location.reload();
            } else {
                showNotification('Error applying discount. Please try again.');
            }
        });
    });
});
</script>
</body>
</html>