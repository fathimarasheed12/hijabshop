<?php
// Include the database connection file
include 'db.php';

// Fetch user data from the database
$query = "SELECT id, username, email, phone, 
          CONCAT(flat_no, '<br>', street1, '<br>', street2, '<br>', area, ', ', city, ', ', landmark, ', ', state, ' - ', pincode) AS address 
          FROM users";

$result = $conn->query($query);

// Check for query errors
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" 
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="users.css">
    <title>View Users</title>
</head>
<body>
    <div class="container">
        <h1>View Users</h1>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Output each row as table data
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['username']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['phone']}</td>
                                <td style='text-align: left; white-space: normal;'>{$row['address']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>