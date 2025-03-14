<?php
include 'db.php';

if (isset($_GET['customer_id'])) {
    $customerId = $_GET['customer_id'];

    $sql = "SELECT * FROM customers WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Customer not found']);
    }
    exit;
}

$conn->close();
?>