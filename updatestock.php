<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hijabshop"; // Updated database name

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Output the filtered rows (if AJAX request)
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    foreach ($stocks as $stock) {
        echo '<tr>';
        echo '<td>' . $stock['purchase_id'] . '</td>';
        echo '<td>' . $stock['hijab_name'] . '</td>';
        echo '<td>' . $stock['dealer_id'] . '</td>';
        echo '<td>' . $stock['quantity'] . '</td>';
        echo '<td>' . $stock['purchase_price'] . '</td>';
        echo '<td>';
        echo '<button class="btn btn-warning btn-sm edit-btn" data-toggle="modal" data-target="#editStockModal" 
            data-purchase_id="' . $stock['purchase_id'] . '"
            data-hijab_id="' . $stock['hijab_id'] . '"
            data-dealer_id="' . $stock['dealer_id'] . '"
            data-quantity="' . $stock['quantity'] . '"
            data-purchase_price="' . $stock['purchase_price'] . '"
            data-bill_no="' . $stock['bill_no'] . '"
            data-date="' . $stock['date'] . '"
            data-payment_type="' . $stock['payment_type'] . '">Edit</button>';
        echo '<form method="POST" action="" style="display:inline;">
                  <input type="hidden" name="delete_purchase_id" value="' . $stock['purchase_id'] . '">
                  <button type="submit" class="btn btn-danger btn-sm" name="delete_stock">Delete</button>
              </form>';
        echo '</td>';
        echo '</tr>';
    }
    exit; // Exit after returning the rows
}

// Fetch dealers for the dealer dropdown
$dealers = [];
$sql = "SELECT dealer_id, dealer_name FROM dealer";
$result = $conn->query($sql);
if ($result === false) {
    die("Error fetching dealers: " . $conn->error);
}
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dealers[] = $row;
    }
}

// Fetch hijabs for the hijab ID dropdown
$hijabs = [];
$sql = "SELECT hijab_id, hijab_name FROM hijabs";
$result = $conn->query($sql);
if ($result === false) {
    die("Error fetching hijabs: " . $conn->error);
}
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $hijabs[] = $row;
    }
}

// Handle form submission for adding or updating stock
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Add or update stock
    if (isset($_POST['update_stock'])) {
        $dealer_id = $conn->real_escape_string($_POST["dealer_id"]);
        $purchase_id = $conn->real_escape_string($_POST["purchase_id"]);
        $date = $conn->real_escape_string($_POST["date"]);
        $bill_no = $conn->real_escape_string($_POST["bill_no"]);
        $payment_type = $conn->real_escape_string($_POST["payment_type"]);
        $hijab_id = $conn->real_escape_string($_POST["hijab_id"]);
        $quantity = $conn->real_escape_string($_POST["quantity"]);
        $purchase_price = $conn->real_escape_string($_POST["purchase_price"]);

        $sql = "UPDATE purchase SET date='$date', bill_no='$bill_no', payment_type='$payment_type', 
                hijab_id='$hijab_id', dealer_id='$dealer_id', quantity='$quantity', purchase_price='$purchase_price' 
                WHERE purchase_id='$purchase_id'";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success'>Stock updated successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
    // Add new stock
    elseif (isset($_POST['add_stock'])) {
        $dealer_id = $conn->real_escape_string($_POST["dealer_id"]);
        $purchase_id = $conn->real_escape_string($_POST["purchase_id"]);
        $date = $conn->real_escape_string($_POST["date"]);
        $bill_no = $conn->real_escape_string($_POST["bill_no"]);
        $payment_type = $conn->real_escape_string($_POST["payment_type"]);
        $hijab_id = $conn->real_escape_string($_POST["hijab_id"]);
        $quantity = $conn->real_escape_string($_POST["quantity"]);
        $purchase_price = $conn->real_escape_string($_POST["purchase_price"]);

        $sql = "INSERT INTO purchase (purchase_id, date, bill_no, payment_type, hijab_id, dealer_id, quantity, purchase_price) 
                VALUES ('$purchase_id', '$date', '$bill_no', '$payment_type', '$hijab_id', '$dealer_id', $quantity, $purchase_price)";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success'>Stock added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
    // Handle deletion of stock
    elseif (isset($_POST['delete_stock'])) {
        $purchase_id = $conn->real_escape_string($_POST['delete_purchase_id']);

        // Perform the delete query
        $sql = "DELETE FROM purchase WHERE purchase_id='$purchase_id'";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success'>Stock deleted successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

// Fetch all stock data with hijab name
$stocks = [];
$sql = "SELECT p.*, h.hijab_name 
        FROM purchase p 
        JOIN hijabs h ON p.hijab_id = h.hijab_id";
$result = $conn->query($sql);
if ($result === false) {
    die("Error fetching stock data: " . $conn->error);
}
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $stocks[] = $row;
    }
}

// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="updatestock.css">
</head>
<body>
    <div class="container">
        <h1>Stock Management</h1>

        <!-- Add Stock Button -->
        <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#addStockModal">Add Stock</button>

        <!-- Add Stock Modal -->
        <div class="modal fade" id="addStockModal" tabindex="-1" role="dialog" aria-labelledby="addStockModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStockModalLabel">Add New Stock</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="dealer_id">Dealer</label>
                                <select class="custom-select" id="dealer_id" name="dealer_id" required>
                                    <option value="" disabled selected>Select Dealer</option>
                                    <?php foreach ($dealers as $dealer): ?>
                                        <option value="<?= $dealer['dealer_id'] ?>"><?= $dealer['dealer_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="purchase_id">Purchase ID</label>
                                <input type="text" class="form-control" id="purchase_id" name="purchase_id" required>
                            </div>
                            <div class="form-group">
                                <label for="date">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label for="bill_no">Bill Number</label>
                                <input type="text" class="form-control" id="bill_no" name="bill_no" required>
                            </div>
                            <div class="form-group">
                                <label for="payment_type">Payment Type</label>
                                <select class="custom-select" id="payment_type" name="payment_type" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Credit Card">Credit Card</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="hijab_id">Hijab ID</label>
                                <select class="custom-select" id="hijab_id" name="hijab_id" required>
                                    <option value="" disabled selected>Select Hijab</option>
                                    <?php foreach ($hijabs as $hijab): ?>
                                        <option value="<?= $hijab['hijab_id'] ?>"><?= $hijab['hijab_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required>
                            </div>
                            <div class="form-group">
                                <label for="purchase_price">Purchase Price</label>
                                <input type="text" class="form-control" id="purchase_price" name="purchase_price" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" name="add_stock">Add Stock</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Stock Modal -->
        <div class="modal fade" id="editStockModal" tabindex="-1" role="dialog" aria-labelledby="editStockModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editStockModalLabel">Edit Stock</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <input type="hidden" id="edit_purchase_id" name="purchase_id">
                            <div class="form-group">
                                <label for="edit_dealer_id">Dealer</label>
                                <select class="custom-select" id="edit_dealer_id" name="dealer_id" required>
                                    <option value="" disabled>Select Dealer</option>
                                    <?php foreach ($dealers as $dealer): ?>
                                        <option value="<?= $dealer['dealer_id'] ?>"><?= $dealer['dealer_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_date">Date</label>
                                <input type="date" class="form-control" id="edit_date" name="date" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_bill_no">Bill Number</label>
                                <input type="text" class="form-control" id="edit_bill_no" name="bill_no" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_payment_type">Payment Type</label>
                                <select class="custom-select" id="edit_payment_type" name="payment_type" required>
                                    <option value="Cash">Cash</option>
                                    <option value="Credit Card">Credit Card</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_hijab_id">Hijab ID</label>
                                <select class="custom-select" id="edit_hijab_id" name="hijab_id" required>
                                    <option value="" disabled>Select Hijab</option>
                                    <?php foreach ($hijabs as $hijab): ?>
                                        <option value="<?= $hijab['hijab_id'] ?>"><?= $hijab['hijab_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_quantity">Quantity</label>
                                <input type="number" class="form-control" id="edit_quantity" name="quantity" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_purchase_price">Purchase Price</label>
                                <input type="text" class="form-control" id="edit_purchase_price" name="purchase_price" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" name="update_stock">Update Stock</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Table -->
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Purchase ID</th>
                    <th>Hijab Name</th>
                    <th>Dealer</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="stocksTableBody">
                <?php foreach ($stocks as $stock): ?>
                    <tr>
                        <td><?= $stock['purchase_id'] ?></td>
                        <td><?= $stock['hijab_name'] ?></td>
                        <td><?= $stock['dealer_id'] ?></td>
                        <td><?= $stock['quantity'] ?></td>
                        <td><?= $stock['purchase_price'] ?></td>
                        <td>
                            <!-- Edit Button -->
                            <button class="btn btn-warning btn-sm edit-btn" data-toggle="modal" data-target="#editStockModal" 
                                    data-purchase_id="<?= $stock['purchase_id'] ?>"
                                    data-hijab_id="<?= $stock['hijab_id'] ?>"
                                    data-dealer_id="<?= $stock['dealer_id'] ?>"
                                    data-quantity="<?= $stock['quantity'] ?>"
                                    data-purchase_price="<?= $stock['purchase_price'] ?>"
                                    data-bill_no="<?= $stock['bill_no'] ?>"
                                    data-date="<?= $stock['date'] ?>"
                                    data-payment_type="<?= $stock['payment_type'] ?>">Edit</button>

                            <!-- Delete Button -->
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="delete_purchase_id" value="<?= $stock['purchase_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" name="delete_stock">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
    <script>
        // Edit Modal Handling
        $('#editStockModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var purchase_id = button.data('purchase_id');
            var hijab_id = button.data('hijab_id');
            var dealer_id = button.data('dealer_id');
            var quantity = button.data('quantity');
            var purchase_price = button.data('purchase_price');
            var bill_no = button.data('bill_no');
            var date = button.data('date');
            var payment_type = button.data('payment_type');

            // Update the modal fields with the data
            $('#edit_purchase_id').val(purchase_id);
            $('#edit_hijab_id').val(hijab_id);
            $('#edit_dealer_id').val(dealer_id);
            $('#edit_quantity').val(quantity);
            $('#edit_purchase_price').val(purchase_price);
            $('#edit_bill_no').val(bill_no);
            $('#edit_date').val(date);
            $('#edit_payment_type').val(payment_type);
        });
    </script>
</body>
</html>