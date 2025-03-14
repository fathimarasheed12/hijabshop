<?php
// Handle form submission via AJAX
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "hijabshop";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_POST["action"] === "add") {
        $dealer_id = $conn->real_escape_string($_POST["dealer_id"]);
        $dealer_name = $conn->real_escape_string($_POST["dealer_name"]);
        $contact_info = $conn->real_escape_string($_POST["contact_info"]);
        $address_line1 = $conn->real_escape_string($_POST["address_line1"]);
        $address_line2 = $conn->real_escape_string($_POST["address_line2"]);
        $city = $conn->real_escape_string($_POST["city"]);
        $state = $conn->real_escape_string($_POST["state"]);
        $zip_code = $conn->real_escape_string($_POST["zip_code"]);
        $country = $conn->real_escape_string($_POST["country"]);
        $address = "$address_line1, $address_line2, $city, $state, $zip_code, $country";

        $sql = "INSERT INTO dealer (dealer_id, dealer_name, contact_info, address) 
                VALUES ('$dealer_id', '$dealer_name', '$contact_info', '$address')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["success" => true, "message" => "Dealer added successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
        }
    } elseif ($_POST["action"] === "edit") {
        $dealer_id = $conn->real_escape_string($_POST["dealer_id"]);
        $dealer_name = $conn->real_escape_string($_POST["dealer_name"]);
        $contact_info = $conn->real_escape_string($_POST["contact_info"]);
        $address_line1 = $conn->real_escape_string($_POST["address_line1"]);
        $address_line2 = $conn->real_escape_string($_POST["address_line2"]);
        $city = $conn->real_escape_string($_POST["city"]);
        $state = $conn->real_escape_string($_POST["state"]);
        $zip_code = $conn->real_escape_string($_POST["zip_code"]);
        $country = $conn->real_escape_string($_POST["country"]);
        $address = "$address_line1, $address_line2, $city, $state, $zip_code, $country";

        $sql = "UPDATE dealer 
                SET dealer_name='$dealer_name', contact_info='$contact_info', address='$address'
                WHERE dealer_id='$dealer_id'";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["success" => true, "message" => "Dealer updated successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
        }
    } elseif ($_POST["action"] === "get") {
        $result = $conn->query("SELECT * FROM dealer");
        $dealers = [];
        while ($row = $result->fetch_assoc()) {
            $dealers[] = $row;
        }
        echo json_encode($dealers);
    }
    elseif ($_POST["action"] === "delete") {
        $dealer_id = $conn->real_escape_string($_POST["dealer_id"]);
    
        $sql = "DELETE FROM dealer WHERE dealer_id='$dealer_id'";
    
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["success" => true, "message" => "Dealer deleted successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
        }
    }
    

    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Management</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="adddealer.css">
    
</head>
<body>
<div class="container">
    <h1 class="text-center">Dealer Management</h1>
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addDealerModal">Add Dealer</button>

    <!-- Dealer List -->
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Dealer ID</th>
            <th>Dealer Name</th>
            <th>Contact Info</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="dealerList">
        <!-- Dealers will be loaded here -->
        </tbody>
    </table>
</div>

<!-- Add Dealer Modal -->
<div class="modal fade" id="addDealerModal" tabindex="-1" role="dialog" aria-labelledby="addDealerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDealerModalLabel">Add Dealer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="dealerForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="dealer_id">Dealer ID</label>
                        <input type="text" class="form-control" id="dealer_id" name="dealer_id" required>
                    </div>
                    <div class="form-group">
                        <label for="dealer_name">Dealer Name</label>
                        <input type="text" class="form-control" id="dealer_name" name="dealer_name" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_info">Contact Info</label>
                        <input type="text" class="form-control" id="contact_info" name="contact_info" required>
                    </div>
                    <div class="form-group">
                        <label for="address_line1">Address Line 1</label>
                        <input type="text" class="form-control" id="address_line1" name="address_line1" required>
                    </div>
                    <div class="form-group">
                        <label for="address_line2">Address Line 2</label>
                        <input type="text" class="form-control" id="address_line2" name="address_line2">
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" class="form-control" id="city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" class="form-control" id="state" name="state" required>
                    </div>
                    <div class="form-group">
                        <label for="zip_code">ZIP Code</label>
                        <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" class="form-control" id="country" name="country" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Dealer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load dealers
function loadDealers() {
    $.post("adddealer.php", { action: "get" }, function (data) {
        const dealers = JSON.parse(data);
        let rows = "";
        dealers.forEach((dealer) => {
            rows += `
                <tr>
                    <td>${dealer.dealer_id}</td>
                    <td>${dealer.dealer_name}</td>
                    <td>${dealer.contact_info}</td>
                    <td>${dealer.address}</td>
            
                    <td>
    <div class="d-flex">
        <button class="btn btn-warning btn-sm edit-btn mr-2" data-id="${dealer.dealer_id}" 
            data-name="${dealer.dealer_name}" data-contact="${dealer.contact_info}" 
            data-address1="${dealer.address.split(", ")[0]}" 
            data-address2="${dealer.address.split(", ")[1]}" 
            data-city="${dealer.address.split(", ")[2]}" 
            data-state="${dealer.address.split(", ")[3]}" 
            data-zip="${dealer.address.split(", ")[4]}" 
            data-country="${dealer.address.split(", ")[5]}">
            Edit
        </button>
        <button class="btn btn-danger btn-sm delete-btn" data-id="${dealer.dealer_id}">
            Delete
        </button>
    </div>
</td>

                </tr>
            `;
        });
        $("#dealerList").html(rows);
    });
}

// Add/Edit dealer
$("#dealerForm").on("submit", function (e) {
    e.preventDefault();
    const action = $("#dealerForm").data("action") || "add"; // Default action to "add"
$.post("adddealer.php", $("#dealerForm").serialize() + `&action=${action}`, function (response) {

        const result = JSON.parse(response);
        alert(result.message);
        if (result.success) {
            $("#addDealerModal").modal("hide");
            loadDealers();
        }
    });
});

// Handle Edit Button Click
$(document).on("click", ".edit-btn", function () {
    const dealer = $(this).data();
    $("#dealer_id").val(dealer.id).prop("readonly", true);
    $("#dealer_name").val(dealer.name);
    $("#contact_info").val(dealer.contact);
    $("#address_line1").val(dealer.address1);
    $("#address_line2").val(dealer.address2);
    $("#city").val(dealer.city);
    $("#state").val(dealer.state);
    $("#zip_code").val(dealer.zip);
    $("#country").val(dealer.country);
    $("#addDealerModalLabel").text("Edit Dealer");
    $("#dealerForm").data("action", "edit");
    $("#addDealerModal").modal("show");
});

// Handle Delete Button Click
$(document).on("click", ".delete-btn", function () {
    const dealer_id = $(this).data("id");
    if (confirm("Are you sure you want to delete this dealer?")) {
        $.post("adddealer.php", { action: "delete", dealer_id: dealer_id }, function (response) {
            const result = JSON.parse(response);
            alert(result.message);
            if (result.success) {
                loadDealers();
            }
        });
    }
});

// Reset Modal on Close
$("#addDealerModal").on("hidden.bs.modal", function () {
    $("#dealerForm")[0].reset();
    $("#dealer_id").prop("readonly", false);
    $("#addDealerModalLabel").text("Add Dealer");
    $("#dealerForm").data("action", "add");
});

// Initial load
loadDealers();

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>