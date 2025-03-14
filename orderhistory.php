<?php
include 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch order history with discount details
$sql = "SELECT o.order_id, o.bill_no, o.customer_name, o.contact_info, o.customer_address, 
               o.hijab_id, o.quantity, o.price AS original_price, o.order_date, 
               i.dis_type, i.price_dis, h.image
        FROM orders o
        JOIN inventory i ON o.hijab_id = i.hijab_id
        JOIN hijabs h ON o.hijab_id = h.hijab_id
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);

// Grouping orders by order_id
$orders = [];
while ($row = $result->fetch_assoc()) {
    $order_id = $row['order_id'];

    // Calculate discounted price
    $after_discount = $row['original_price'];
    if ($row['dis_type'] === 'percentage') {
        $after_discount -= ($row['original_price'] * $row['price_dis']) / 100;
    } elseif ($row['dis_type'] === 'fixed') {
        $after_discount -= $row['price_dis'];
    }
    $after_discount = max($after_discount, 0);

    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            'order_id' => $row['order_id'],
            'bill_no' => $row['bill_no'],
            'customer_name' => $row['customer_name'],
            'contact_info' => $row['contact_info'],
            'customer_address' => $row['customer_address'],
            'order_date' => $row['order_date'],
            'hijabs' => []
        ];
    }

    $orders[$order_id]['hijabs'][] = [
        'hijab_id' => $row['hijab_id'],
        'quantity' => $row['quantity'],
        'original_price' => $row['original_price'],
        'discounted_price' => $after_discount,
        'image' => $row['image']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="orderhistory.css">
    <title>Order History</title>
    <script>
        function filterOrders() {
            const searchTerm = document.getElementById('search').value.toLowerCase();
            const orders = document.querySelectorAll('.order');
            
            orders.forEach(order => {
                const orderId = order.getAttribute('data-order-id').toLowerCase();
                const billNo = order.getAttribute('data-bill-no').toLowerCase();
                const customerName = order.getAttribute('data-customer-name').toLowerCase();
                
                const matchesSearch = orderId.includes(searchTerm) || 
                                    billNo.includes(searchTerm) ||
                                    customerName.includes(searchTerm);
                
                order.style.display = matchesSearch ? 'table-row' : 'none';
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Order History</h1>
        <div class="search-bar">
            <input type="text" id="search" oninput="filterOrders()" placeholder="Search by Order ID, Hijab name, Bill No, or Customer Name...">
        </div>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Bill No</th>
                    <th>Customer Name</th>
                    <th>Contact Info</th>
                    <th>Address</th>
                    <th>Hijabs (ID / Image)</th>
                    <th>Quantity</th>
                    <th>Price (Original / Discounted)</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr class="order" 
                            data-order-id="<?= $order['order_id']; ?>" 
                            data-bill-no="<?= $order['bill_no']; ?>"
                            data-customer-name="<?= $order['customer_name']; ?>">
                            <td><?= $order['order_id']; ?></td>
                            <td><?= $order['bill_no']; ?></td>
                            <td><?= $order['customer_name']; ?></td>
                            <td><?= $order['contact_info']; ?></td>
                            <td class="address"><?= $order['customer_address']; ?></td>
                            <td>
                                <?php foreach ($order['hijabs'] as $index => $hijab): ?>
                                    <?= $hijab['hijab_id']; ?> 
                                    <br><img src="<?= $hijab['image']; ?>" alt="Hijab Image">
                                    <?php if ($index < count($order['hijabs']) - 1) echo "<br><hr>"; ?>
                                    <br>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach ($order['hijabs'] as $index => $hijab): ?>
                                    <?= $hijab['quantity']; ?>
                                    <?php if ($index < count($order['hijabs']) - 1) echo "<hr>"; ?>
                                    <br>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach ($order['hijabs'] as $index => $hijab): ?>
                                    ₹<?= number_format($hijab['original_price'], 2); ?>
                                    <?php if ($hijab['discounted_price'] < $hijab['original_price']): ?>
                                        / <span class="discounted-price">₹<?= number_format($hijab['discounted_price'], 2); ?></span>
                                    <?php endif; ?>
                                    <?php if ($index < count($order['hijabs']) - 1) echo "<hr>"; ?>
                                    <br>
                                <?php endforeach; ?>
                            </td>
                            <td><?= $order['order_date']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No orders found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php $conn->close(); ?>