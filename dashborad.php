<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$username = $_SESSION['username'];
$userId = $_SESSION['user_id'];

// Get statistics
$totalBooks = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$totalBorrowed = $pdo->query("SELECT COUNT(*) FROM borrowed_books WHERE status = 'borrowed'")->fetchColumn();
$totalAvailable = $pdo->query("SELECT IFNULL(SUM(number_of_copies), 0) FROM books")->fetchColumn();
$totalOverdue = $pdo->query("SELECT COUNT(*) FROM borrowed_books WHERE status = 'borrowed' AND return_date < CURRENT_DATE()")->fetchColumn();

// Get featured books (latest 4 books)
$featuredBooks = $pdo->query("SELECT * FROM books ORDER BY id DESC LIMIT 4")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Library Dashboard - manage books, search, borrow and return.">
    <title>Dashboard - Online Library Management System</title>
    <link rel="stylesheet" href="css/index.css">
</head>

<body class="dashboard-body">

    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <div class="brand-icon"></div>
            Library System
        </a>

        <div class="navbar-actions">
            <div class="navbar-user">
                User: <span id="nav-username"><?= htmlspecialchars($username) ?></span>
            </div>
            <a href="logout.php" class="btn btn-secondary" id="btn-logout" style="padding:8px 18px;font-size:0.82rem;">
                Logout
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="dashboard-content">

        <!-- Header -->
        <div class="dashboard-header">
            <h1>Library Dashboard</h1>
            <p>Welcome back, <strong id="welcome-username" style="color:#a5b4fc;"><?= htmlspecialchars($username) ?></strong>! What would you like to do today?</p>
        </div>

        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" id="search-input" placeholder="Search books by title, author or genre...">
            <button class="btn search-btn" onclick="searchBooks()" id="btn-search">Search</button>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon purple"></div>
                <div class="stat-info">
                    <div class="value"><?= number_format($totalBooks) ?></div>
                    <div class="label">Total Books</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"></div>
                <div class="stat-info">
                    <div class="value"><?= number_format($totalBorrowed) ?></div>
                    <div class="label">Borrowed</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"></div>
                <div class="stat-info">
                    <div class="value"><?= number_format($totalAvailable) ?></div>
                    <div class="label">Available Copies</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"></div>
                <div class="stat-info">
                    <div class="value"><?= number_format($totalOverdue) ?></div>
                    <div class="label">Overdue</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section-heading">
            <h2>Quick Actions</h2>
        </div>
        <div class="actions-grid">
            <a href="view_books.php" class="action-card" id="action-view-books">
                <div class="action-icon"></div>
                <span>View Books</span>
            </a>
            <a href="#" class="action-card" id="action-search-books"
                onclick="document.getElementById('search-input').focus(); return false;">
                <div class="action-icon"></div>
                <span>Search Books</span>
            </a>
            <a href="add_books.php" class="action-card" id="action-add-books">
                <div class="action-icon"></div>
                <span>Add Books</span>
            </a>
            <a href="borrow_books.php" class="action-card" id="action-my-books">
                <div class="action-icon"></div>
                <span>Borrow Books</span>
            </a>
            <a href="return_books.php" class="action-card" id="action-return">
                <div class="action-icon"></div>
                <span>Return Books</span>
            </a>
        </div>

        <!-- Featured Books -->
        <div class="section-heading">
            <h2>Featured Books</h2>
            <a href="view_books.php" class="btn btn-secondary" style="padding:7px 18px;font-size:0.82rem;">View All</a>
        </div>
        <div class="books-grid" id="books-grid">
            <?php if (!empty($featuredBooks)): ?>
                <?php foreach ($featuredBooks as $index => $book): ?>
                    <div class="book-card">
                        <div class="book-cover c<?= ($index % 4) + 1 ?>"></div>
                        <div class="book-info">
                            <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                            <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
                            <?php if ($book['number_of_copies'] > 0): ?>
                                <span class="book-badge badge-available">Available (<?= $book['number_of_copies'] ?>)</span>
                            <?php else: ?>
                                <span class="book-badge badge-borrowed">Borrowed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:var(--text-secondary);grid-column:1/-1;text-align:center;padding:20px;">No books registered yet. Add some books to get started!</p>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            2025 Online Library Management System. All rights reserved.
        </div>

    </div>

    <script>
        function searchBooks() {
            const query = document.getElementById('search-input').value.toLowerCase().trim();
            if (!query) return;
            // Redirect to search_books.php with query parameter
            window.location.href = 'search_books.php?query=' + encodeURIComponent(query);
        }

        document.getElementById('search-input').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') searchBooks();
        });
    </script>

</body>

</html>
