<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// ✅ Session check
if (!isset($_SESSION['librarians'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

// ✅ Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('No book ID provided!'); window.location.href='index.php';</script>";
    exit;
}

$id = intval($_GET['id']);
$message = "";

// ✅ Fetch current book data
$result = mysqli_query($conn, "SELECT * FROM books WHERE id = $id LIMIT 1");
if (!$result || mysqli_num_rows($result) === 0) {
    echo "<script>alert('Book not found!'); window.location.href='index.php';</script>";
    exit;
}
$row = mysqli_fetch_assoc($result);

// ✅ Handle form submission
if (isset($_POST['update'])) {
    $title          = mysqli_real_escape_string($conn, $_POST['title']);
    $author         = mysqli_real_escape_string($conn, $_POST['author']);
    $year_published = mysqli_real_escape_string($conn, $_POST['year_published']);
    $genre          = mysqli_real_escape_string($conn, $_POST['genre']);
    $isbn           = mysqli_real_escape_string($conn, $_POST['isbn']);
    $publisher      = mysqli_real_escape_string($conn, $_POST['publisher']);
    $location       = mysqli_real_escape_string($conn, $_POST['location']);
    $status         = mysqli_real_escape_string($conn, $_POST['status']);
    $cover_image    = $row['cover_image']; // default: keep old image

    // ✅ Check if new file uploaded
    if (!empty($_FILES['cover']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = basename($_FILES['cover']['name']);
        $file_tmp  = $_FILES['cover']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed)) {
            $new_name = time() . "_" . preg_replace("/[^a-zA-Z0-9_.-]/", "_", $file_name);
            $target_file = $target_dir . $new_name;
            if (move_uploaded_file($file_tmp, $target_file)) {
                // Delete old cover if exists
                if (!empty($row['cover_image']) && file_exists("uploads/" . $row['cover_image'])) {
                    unlink("uploads/" . $row['cover_image']);
                }
                $cover_image = $new_name;
            } else {
                $message = '<div class="notice error">❌ Failed to upload new cover image.</div>';
            }
        } else {
            $message = '<div class="notice error">❌ Invalid file type (JPG, PNG, GIF only).</div>';
        }
    }

    // ✅ Update record
    $update_sql = "UPDATE books SET 
        title='$title',
        author='$author',
        year_published='$year_published',
        genre='$genre',
        isbn='$isbn',
        publisher='$publisher',
        location='$location',
        status='$status',
        cover_image='$cover_image'
        WHERE id=$id";

    if (mysqli_query($conn, $update_sql)) {
        $message = '<div class="notice success">✅ Book updated successfully!</div>';
        // Refresh updated data
        $result = mysqli_query($conn, "SELECT * FROM books WHERE id = $id LIMIT 1");
        $row = mysqli_fetch_assoc($result);
    } else {
        $message = '<div class="notice error">❌ Failed to update: ' . mysqli_error($conn) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Book</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
<header class="header">
    <h2>Edit Book</h2>
    <div class="header-actions">
        <a class="btn" href="index.php">&larr; Back</a>
    </div>
</header>

<div class="card">
    <?= $message; ?>

    <form method="post" enctype="multipart/form-data" class="form-grid" novalidate>
        <div class="form-field"><label>Title</label><input type="text" name="title" value="<?= htmlspecialchars($row['title']); ?>" required pattern="[A-Za-z0-9 .,'-]{3,100}"></div>
        <div class="form-field"><label>Author</label><input type="text" name="author" value="<?= htmlspecialchars($row['author']); ?>" required pattern="[A-Za-z .'-]{3,80}"></div>
        <div class="form-field"><label>Year Published</label><input type="text" name="year_published" value="<?= htmlspecialchars($row['year_published']); ?>" required pattern="[0-9]{4}"></div>
        <div class="form-field"><label>Genre</label><input type="text" name="genre" value="<?= htmlspecialchars($row['genre']); ?>" required pattern="[A-Za-z ]{3,50}"></div>
        <div class="form-field"><label>ISBN</label><input type="text" name="isbn" value="<?= htmlspecialchars($row['isbn']); ?>" required pattern="[0-9-]{10,17}"></div>
        <div class="form-field"><label>Publisher</label><input type="text" name="publisher" value="<?= htmlspecialchars($row['publisher']); ?>" required pattern="[A-Za-z0-9 .,'-]{3,80}"></div>
        <div class="form-field"><label>Location / Shelf</label><input type="text" name="location" value="<?= htmlspecialchars($row['location']); ?>" required pattern="[A-Za-z0-9- ]{2,20}"></div>
        <div class="form-field">
            <label>Status</label>
            <select name="status" required>
                <option value="Available" <?= ($row['status'] === 'Available') ? 'selected' : ''; ?>>Available</option>
                <option value="Borrowed" <?= ($row['status'] === 'Borrowed') ? 'selected' : ''; ?>>Borrowed</option>
            </select>
        </div>
        <div class="form-field" style="grid-column:1/-1">
            <label>Replace Cover (optional)</label>
            <input type="file" name="cover" accept="image/*">
        </div>
        <div class="form-actions">
            <button type="submit" name="update" class="btn primary">Update</button>
            <a class="btn" href="index.php">Back</a>
        </div>
    </form>
</div>
</div>
</body>
</html>
