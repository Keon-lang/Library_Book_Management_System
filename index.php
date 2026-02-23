<?php
session_start();
include 'db.php'; // ✅ required for $conn

// Redirect if not logged in
if (!isset($_SESSION['librarians'])) {
    header('Location: login.php');
    exit;
}

// Search and filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$query = "SELECT * FROM books WHERE 1";
if (!empty($search)) {
    $query .= " AND (title LIKE '%$search%' OR author LIKE '%$search%' OR genre LIKE '%$search%' OR isbn LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $query .= " AND status = '$status_filter'";
}
$query .= " ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Count total books
$total_books = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM books"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Book Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrapper">
    <header class="header">
        <h2>📚 Library Book Management System</h2>
        <div class="header-actions">
            <!-- Optional: show logged-in username -->
            <span style="margin-right:15px; font-weight:600; color:#2563eb;">
                Welcome, <?= htmlspecialchars($_SESSION['librarians']); ?> 👋
            </span>
            <a class="btn primary" href="insert.php">+ Add Book</a>
            <a class="btn danger" href="logout.php">Logout</a>
        </div>
    </header>

    <!-- Search + Filter -->
    <div class="card">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap;">
            <input type="text" name="search" placeholder="Search by title, author, or genre" value="<?= htmlspecialchars($search); ?>" style="flex:1; padding:8px 10px; border:1px solid #ccc; border-radius:8px;">
            <select name="status" style="padding:8px 10px; border-radius:8px;">
                <option value="">All Status</option>
                <option value="Available" <?= ($status_filter == 'Available') ? 'selected' : ''; ?>>Available</option>
                <option value="Borrowed" <?= ($status_filter == 'Borrowed') ? 'selected' : ''; ?>>Borrowed</option>
            </select>
            <button class="btn primary" type="submit">Search</button>
            <a href="index.php" class="btn">Reset</a>
        </form>
    </div>

    <!-- Books Table -->
    <div class="card" style="margin-top:15px;">
        <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Year</th>
                    <th>Genre</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($book = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>
                        <?php if (!empty($book['cover_image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($book['cover_image']); ?>" alt="cover" width="60">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/60x80?text=No+Cover" alt="No Cover">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($book['title']); ?></td>
                    <td><?= htmlspecialchars($book['author']); ?></td>
                    <td><?= htmlspecialchars($book['year_published']); ?></td>
                    <td><?= htmlspecialchars($book['genre']); ?></td>
                    <td>
                        <?php if ($book['status'] === 'Available'): ?>
                            <span style="color:green;font-weight:bold;">Available</span>
                        <?php else: ?>
                            <span style="color:red;font-weight:bold;">Borrowed</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a class="btn info" href="view.php?id=<?= $book['id']; ?>">View</a>
                        <a class="btn warn" href="edit.php?id=<?= $book['id']; ?>">Edit</a>
                        <a class="btn danger" href="delete.php?id=<?= $book['id']; ?>" onclick="return confirm('Delete this book?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="empty">No books found.</div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>Total Books: <strong><?= $total_books; ?></strong> | Last Updated: <?= date('Y-m-d H:i:s'); ?></p>
    </div>
</div>
</body>
</html>

