<?php
include 'db.php';

// Fetch hijabs from the database
$sql = "SELECT * FROM hijabs";
$result = $conn->query($sql);

// Fetch categories for the filter
$category_sql = "SELECT DISTINCT category FROM hijabs";
$category_result = $conn->query($category_sql);
$categories = [];
if ($category_result->num_rows > 0) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="viewHijab.css">
    <title>Hijab List</title>
</head>
<body>

    <h2>Hijab List</h2>
    <div class="category-add-container">
        <select id="category-filter" onchange="filterHijabs()">
            <option value="all">All Categories</option>
            <?php foreach ($categories as $category) { ?>
                <option value="<?php echo htmlspecialchars($category); ?>">
                    <?php echo htmlspecialchars($category); ?>
                </option>
            <?php } ?>
        </select>
        <a href="addHijab.php">
            <button class="add-hijab-button">Add Hijab</button>
        </a>
    </div>

    <table>
    <thead>
        <tr>
            <th>S.No</th>
            <th>Hijab Image</th>
            <th>Hijab Name & Price</th>
            <th>Hijab Details</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="hijab-table">
        <?php
        if ($result->num_rows > 0) {
            $serial = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr class='hijab' data-category='".htmlspecialchars($row['category'])."'>
                    <td>{$serial}</td>
                    <td><img src='{$row['image']}' alt='Hijab Image'></td>
                    <td class='hijab-info'>
                        <span class='highlight-text'>{$row['hijab_name']}</span><br>
                        <span class='highlight-text'>₹{$row['price']}</span>
                    </td>
                    <td class='hijab-details'>
                        <strong>Color:</strong> {$row['color']}<br>
                        <strong>Quality:</strong> {$row['quality']}<br>
                        <strong>Description:</strong> {$row['description']}
                    </td>
                    <td>
                        <form action='editHijab.php' method='get'>
                            <input type='hidden' name='hijab_id' value='{$row['hijab_id']}'>
                            <button type='submit' class='action-button'>Edit</button>
                        </form>
                    </td>
                </tr>";
                $serial++;
            }
        } else {
            echo "<tr><td colspan='5'>No hijabs found.</td></tr>";
        }
        ?>
    </tbody>
</table>

    <script>
        function filterHijabs() {
            const selectedCategory = document.getElementById('category-filter').value.toLowerCase();
            const hijabs = document.querySelectorAll('.hijab');

            hijabs.forEach(hijab => {
                const hijabCategory = hijab.getAttribute('data-category').toLowerCase();

                const matchesCategory = selectedCategory === 'all' || hijabCategory === selectedCategory;

                if (matchesCategory) {
                    hijab.style.display = 'table-row';
                } else {
                    hijab.style.display = 'none';
                }
            });
        }
        document.getElementById('category-filter').addEventListener('change', filterHijabs);
    </script>
</body>
</html>