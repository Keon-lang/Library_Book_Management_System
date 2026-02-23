<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// ✅ Only allow logged-in users
if (!isset($_SESSION['librarians'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

// ✅ Ensure ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('No book ID provided!'); window.location.href='index.php';</script>";
    exit;
}

$id = intval($_GET['id']);

// ✅ Fetch book info first (to delete cover image)
$result = mysqli_query($conn, "SELECT cover_image FROM books WHERE id = $id LIMIT 1");
if ($result && mysqli_num_rows($result) > 0) {
    $book = mysqli_fetch_assoc($result);

    // ✅ Delete cover image file if exists
    if (!empty($book['cover_image']) && file_exists("uploads/" . $book['cover_image'])) {
        unlink("uploads/" . $book['cover_image']);
    }

    // ✅ Delete record from database
    $delete = mysqli_query($conn, "DELETE FROM books WHERE id = $id");

    if ($delete) {
        echo "<script>alert('✅ Book deleted successfully!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('❌ Failed to delete book: " . mysqli_error($conn) . "'); window.location.href='index.php';</script>";
    }
} else {
    echo "<script>alert('Book not found!'); window.location.href='index.php';</script>";
}
?>
