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

$book_id = intval($_GET['id']);

// ✅ Retrieve book data by ID
$sql = "SELECT * FROM books WHERE id = $book_id LIMIT 1";
$result = mysqli_query($conn, $sql);

// ✅ Handle missing book
if (!$result || mysqli_num_rows($result) === 0) {
    echo "<script>alert('Book not found!'); window.location.href='index.php';</script>";
    exit;
}

$book = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Details</title>
<link rel="stylesheet" href="style.css">
<style>
.view-card {
    display: flex;
    gap: 30px;
    align-items: flex-start;
    flex-wrap: wrap;
}
.view-card img {
    width: 240px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,.1);
}
.book-info {
    flex: 1;
    min-width: 260px;
}
.book-info h3 {
    margin-top: 0;
    color: #1e3a8a;
}
.book-info p {
    margin: 6px 0;
}
.book-info span {
    font-weight: 800;
    color: #374151;
}
.badge.success {
    color: green;
    font-weight: bold;
}
.badge.danger {
    color: red;
    font-weight: bold;
}
</style>
</head>
<body>
<div class="wrapper">
<header class="header">
    <h2>Book Details</h2>
    <div class="header-actions">
        <a class="btn" href="index.php">&larr; Back</a>
        <a class="btn warn" href="edit.php?id=<?= (int)$book['id']; ?>">Edit</a>
    </div>
</header>

<div class="card view-card">
    <div class="book-cover">
        <?php if (!empty($book['cover_image'])): ?>
            <img src="uploads/<?= htmlspecialchars($book['cover_image']); ?>" alt="Book Cover">
        <?php else: ?>
            <img src="https://via.placeholder.com/240x320?text=No+Cover" alt="No Cover">
        <?php endif; ?>
    </div>
    <div class="book-info">
        <h3><?= htmlspecialchars($book['title']); ?></h3>
        <p><span>Author:</span> <?= htmlspecialchars($book['author']); ?></p>
        <p><span>ISBN:</span> <?= htmlspecialchars($book['isbn']); ?></p>
        <p><span>Publisher:</span> <?= htmlspecialchars($book['publisher']); ?></p>
        <p><span>Year:</span> <?= htmlspecialchars($book['year_published']); ?></p>
        <p><span>Genre:</span> <?= htmlspecialchars($book['genre']); ?></p>
        <p><span>Location:</span> <?= htmlspecialchars($book['location']); ?></p>
        <p><span>Status:</span>
            <?php if (($book['status'] ?? 'Available') === 'Available'): ?>
                <span class="badge success">Available</span>
            <?php else: ?>
                <span class="badge danger">Borrowed</span>
            <?php endif; ?>
        </p>
        <p><span>Uploaded By:</span> <?= htmlspecialchars($book['uploaded_by']); ?></p>
        <p><span>Date Added:</span> <?= htmlspecialchars($book['created_at']); ?></p>
    </div>
</div>
</div>
</body>
</html>
