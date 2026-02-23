<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (!isset($_SESSION['librarians'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

$message = "";

// ✅ handle form submission
if (isset($_POST['submit'])) {

    // Collect form data safely
    $title          = mysqli_real_escape_string($conn, $_POST['title']);
    $author         = mysqli_real_escape_string($conn, $_POST['author']);
    $year_published = mysqli_real_escape_string($conn, $_POST['year_published']);
    $genre          = mysqli_real_escape_string($conn, $_POST['genre']);
    $isbn           = mysqli_real_escape_string($conn, $_POST['isbn']);
    $publisher      = mysqli_real_escape_string($conn, $_POST['publisher']);
    $location       = mysqli_real_escape_string($conn, $_POST['location']);
    $status         = mysqli_real_escape_string($conn, $_POST['status']);
    $uploaded_by    = $_SESSION['librarians'];
    $cover_image    = "";

    // ✅ Handle file upload if exists
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
                $cover_image = $new_name;
            } else {
                $message = '<div class="notice error">❌ Failed to upload cover image.</div>';
            }
        } else {
            $message = '<div class="notice error">❌ Invalid file type. Only JPG, PNG, GIF allowed.</div>';
        }
    }

    // ✅ Insert into database
    $sql = "INSERT INTO books (title, author, year_published, genre, isbn, publisher, location, status, uploaded_by, cover_image)
            VALUES ('$title', '$author', '$year_published', '$genre', '$isbn', '$publisher', '$location', '$status', '$uploaded_by', '$cover_image')";

    if (mysqli_query($conn, $sql)) {
        $message = '<div class="notice success">✅ Book added successfully!</div>';
    } else {
        $message = '<div class="notice error">❌ Error adding book: ' . mysqli_error($conn) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Book</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
<header class="header">
    <h2>Add New Book</h2>
    <div class="header-actions">
        <a class="btn" href="index.php">&larr; Back</a>
    </div>
</header>

<div class="card">
    <?= $message; ?>
    <form method="post" enctype="multipart/form-data" class="form-grid" novalidate>
        <div class="form-field"><label for="title">Title</label><input id="title" type="text" name="title" required pattern="[A-Za-z0-9 .,'-]{3,100}" placeholder="e.g. Clean Code"></div>
        <div class="form-field"><label for="author">Author</label><input id="author" type="text" name="author" required pattern="[A-Za-z .'-]{3,80}" placeholder="e.g. Robert C. Martin"></div>
        <div class="form-field"><label for="year">Year Published</label><input id="year" type="text" name="year_published" required pattern="[0-9]{4}" placeholder="e.g. 2008"></div>
        <div class="form-field"><label for="genre">Genre</label><input id="genre" type="text" name="genre" required pattern="[A-Za-z ]{3,50}" placeholder="e.g. Technology"></div>
        <div class="form-field"><label for="isbn">ISBN</label><input id="isbn" type="text" name="isbn" required pattern="[0-9-]{10,17}" placeholder="e.g. 978-967-232-1234"></div>
        <div class="form-field"><label for="publisher">Publisher</label><input id="publisher" type="text" name="publisher" required pattern="[A-Za-z0-9 .,'-]{3,80}" placeholder="e.g. Pearson"></div>
        <div class="form-field"><label for="location">Location / Shelf</label><input id="location" type="text" name="location" required pattern="[A-Za-z0-9- ]{2,20}" placeholder="e.g. R1-S3-A12"></div>
        <div class="form-field"><label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="Available">Available</option>
                <option value="Borrowed">Borrowed</option>
            </select>
        </div>
        <div class="form-field" style="grid-column:1/-1">
            <label for="cover">Book Cover</label>
            <input id="cover" type="file" name="cover" accept="image/*">
        </div>
        <div class="form-actions">
            <button type="submit" name="submit" class="btn primary">Save</button>
            <a class="btn" href="index.php">Cancel</a>
        </div>
    </form>
</div>
</div>
</body>
</html>
