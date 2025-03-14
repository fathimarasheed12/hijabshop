<?php
include 'db.php';

if (isset($_GET['customer_id'])) {
    $customerId = $_GET['customer_id'];

    $sql = "SELECT customer_name, customer_address, contact_info FROM customers WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["error" => "Customer not found"]);
    }
    exit;
}
?>