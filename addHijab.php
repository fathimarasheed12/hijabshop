<?php
include 'db.php';
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // Only process the form if it was submitted

    // Collect form data only if the request method is POST
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $hijab_id = mysqli_real_escape_string($conn, $_POST['hijab_id']);
    $hijab_name = mysqli_real_escape_string($conn, $_POST['hijab_name']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $quality = mysqli_real_escape_string($conn, $_POST['quality']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_folder = 'uploads/' . $image_name;

        // Ensure the uploads directory exists
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($image_tmp_name, $image_folder)) {
            $image_path = $image_folder;
        } else {
            die('Failed to upload image.');
        }
    } else {
        die('Error uploading image.');
    }

    // Check if hijab_id already exists
    $check_sql = "SELECT COUNT(*) FROM hijabs WHERE hijab_id = '$hijab_id'";
    $check_result = $conn->query($check_sql);
    $row = $check_result->fetch_row();

    if ($row[0] > 0) {
        // If the hijab_id already exists, display an error message
        $error_message = "Error: The hijab ID '$hijab_id' is already taken. Please choose a different ID.   quality error"  ;
    } else {
        // Insert data into the database
      
        $sql = "INSERT INTO hijabs (category, hijab_id, hijab_name, color, price, quality, image, description) 
                VALUES ('$category', '$hijab_id', '$hijab_name', '$color', '$price', '$quality', '$image_path', '$description')";
        

        if ($conn->query($sql) === TRUE) {
            echo "New hijab added successfully!";
            header('location: viewHijab.php');
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="addHijab.css">
    <title>Add New Hijab</title>
</head>
<body>
   
    <div class="container">
        <h2>Add New Hijab</h2>
        <?php if(isset($error_message))
        echo "<p class ='error'>$error_message</p>"; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <label>Hijab Category</label><br><br>
            <select id="category" name="category" required>
                <option value="">Select Category</option>
                <option value="Jersey Hijab">Jersey Hijab</option>
                <option value="Wedding Hijab">Wedding Hijab</option>
                <option value="Casual Hijab">Casual Hijab</option>
                <option value="Kids Hijab">Kids Hijab</option>
            </select><br><br>
            <label>Hijab Id</label><br><br>
            <input type="text" id="hijab_id" name="hijab_id" required><br>
            <label>Hijab Name</label><br><br>
            <input type="text" id="hijab_name" name="hijab_name" required><br>
            <label>Hijab Color</label><br><br>
            <input type="text" id="color" name="color" list="colorSuggestions" required><br>
            <datalist id="colorSuggestions">
                <option value="Black">
                <option value="White">
                <option value="Red">
                <option value="Blue">

                <option value="Green">
                <option value="Yellow">
                <option value="Pink">
                <option value="Purple">
                <option value="Orange">
                <option value="Brown">
                <option value="cream">
            </datalist><br>
            <label>Hijab Price</label><br><br>
            <input type="text" id="price" name="price" required step="0.01"><br>
            <label>Hijab Quality</label><br><br>
            <input type="text" id="quality" name="quality" required><br>
            <label for="image">Choose Hijab Picture</label><br><br>
            <input type="file" id="image" name="image" accept="image/*" required><br>
            <label>Description</label><br><br>
            <textarea id="description" name="description" rows="3" required></textarea><br><br>
            <button type="submit" id="submitBtn">Add Hijab</button> 
        </form>
    </div>
   
</body>
</html>
