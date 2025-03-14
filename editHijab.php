<?php
include 'db.php';
$hijab_id = $_GET['hijab_id'] ?? '';
if (!$hijab_id) {
    die("Hijab ID is required.");
}

$sql = "SELECT * FROM hijabs WHERE hijab_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hijab_id);
$stmt->execute();
$result = $stmt->get_result();
$hijab = $result->fetch_assoc();

if (!$hijab) {
    die("Hijab not found.");
}

// Fetch hijab details
$category = $hijab['category'];
$hijab_name = $hijab['hijab_name'];
$color = $hijab['color'];
$price = $hijab['price'];
$quality = $hijab['quality'];
$image = $hijab['image']; 
$description = $hijab['description'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $category = $_POST['category'];
    $hijab_name = $_POST['hijab_name'];
    $color = $_POST['color'];
    $price = $_POST['price'];
    $quality = $_POST['quality'];
    $description = $_POST['description'];

    // Handle image upload
    if ($_FILES['image']['name']) {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    // Update hijab details in the database
    $sql_update = "UPDATE hijabs SET category = ?, hijab_name = ?, color = ?, price = ?, quality = ?, image = ?, description = ? WHERE hijab_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssssss", $category, $hijab_name, $color, $price, $quality, $image, $description, $hijab_id);
    
    if ($stmt_update->execute()) {
        // Redirect to viewHijab.php after successful update
        header("Location: viewHijab.php");
        exit();
    } else {
        echo "Error updating hijab: " . $stmt_update->error;
    }

    $stmt_update->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="editHijab.css">
    <title>Edit Hijab</title>
</head>
<body>
    <div class="container">
        <h2>Edit Hijab</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <label>Hijab Category</label><br><br>
            <select id="category" name="category" required>
                <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                <option value="Jersey Hijab">Jersey Hijab</option>
                <option value="Wedding Hijab">Wedding Hijab</option>
                <option value="Casual Hijab">Casual Hijab</option>
                <option value="Kids Hijab">Kids Hijab</option>
            </select><br><br>
            
            <label>Hijab Id</label><br><br>
            <input type="text" id="hijab_id_display" placeholder="Hijab ID" value="<?php echo htmlspecialchars($hijab_id); ?>" readonly>
            <input type="hidden" name="hijab_id" value="<?php echo htmlspecialchars($hijab_id); ?>">
            
            <label>Hijab Name</label><br><br>
            <input type="text" id="hijab_name" name="hijab_name" value="<?php echo htmlspecialchars($hijab_name); ?>" required>
            
            <label>Hijab Color</label><br><br>
            <input type="text" id="color" name="color" list="colorSuggestions" value="<?php echo htmlspecialchars($color); ?>" required>
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
            </datalist><br>
            
            <label>Hijab Price</label><br><br>
            <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required>
            
            <label>Hijab Quality</label><br><br>
            <input type="text" id="quality" name="quality" value="<?php echo htmlspecialchars($quality); ?>" required>
            
            <?php if ($image): ?>
                <label>Current Image:</label><br>
                <img src="<?php echo htmlspecialchars($image); ?>" alt="Current Hijab Image"><br>
            <?php endif; ?>
            
            <label for="image">Choose New Image:</label><br><br>
            <input type="file" id="image" name="image" accept="image/*">
            
            <label>Description</label><br><br>
            <textarea id="description" name="description" rows="3" required><?php echo htmlspecialchars($description); ?></textarea>
            <br><br>
            
            <button type="submit">Update Hijab</button>
        </form>
    </div>
</body>
</html>