<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$searchQuery = trim($_GET['query'] ?? $_GET['search'] ?? '');
$books = [];

if ($searchQuery !== '') {
    $stmt = $pdo->prepare("
        SELECT title, author, genre, number_of_copies 
        FROM books 
        WHERE title LIKE ? OR author LIKE ? OR genre LIKE ? 
        ORDER BY title ASC
    ");
    $searchTerm = "%" . $searchQuery . "%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $books = $stmt->fetchAll();
} else {
    // If no search query, select all books
    $books = $pdo->query("SELECT title, author, genre, number_of_copies FROM books ORDER BY title ASC")->fetchAll();
}
?>
<html>

<head>
    <title>Search Books - Online Library Management System</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .container {
            width: 550px;
            /* Wider layout for search results table */
        }

        .book-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            text-align: left;
            font-size: 14px;
        }

        .book-table th,
        .book-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #cbd5e0;
        }

        .book-table th {
            background-color: #f4f7fc;
            color: #2c3e50;
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-available {
            background-color: #e6f4ea;
            color: #137333;
        }

        .status-borrowed {
            background-color: #fce8e6;
            color: #c5221f;
        }

        .search-form-group {
            margin: 20px 0;
        }

        .search-form-group input[type="text"] {
            width: calc(100% - 120px);
            padding: 10px 12px;
            border: 1px solid #cbd5e0;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
            background: #fff;
            color: #2d3748;
        }

        .search-form-group input[type="text"]:focus {
            border-color: #3498db;
        }

        .search-form-group .btn {
            width: 100px;
            padding: 10px;
            margin-left: 10px;
            cursor: pointer;
        }

        .actions {
            margin-top: 25px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><i class="fa-solid fa-magnifying-glass" style="margin-right:8px;"></i>Search Books</h1>
        <p>Find books in the library catalog by Title, Author, or Genre</p>

        <form action="search_books.php" method="get" class="search-form-group" style="display: flex;">
            <input type="text" id="search" name="query" placeholder="Enter title, author or genre..." 
                   value="<?= htmlspecialchars($searchQuery) ?>" required>
            <input type="submit" value="🔍 Search" class="btn">
        </form>

        <?php if ($searchQuery !== ''): ?>
            <h3 style="margin-top: 20px; color: #2c3e50;">Search Results for "<?= htmlspecialchars($searchQuery) ?>" (<?= count($books) ?> found)</h3>
        <?php endif; ?>

        <table class="book-table">
            <thead>
                <tr>
                    <th><i class="fa-solid fa-book" style="margin-right:5px;"></i>Book Title</th>
                    <th><i class="fa-solid fa-user-pen" style="margin-right:5px;"></i>Author</th>
                    <th><i class="fa-solid fa-tags" style="margin-right:5px;"></i>Genre</th>
                    <th><i class="fa-solid fa-circle-info" style="margin-right:5px;"></i>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($books)): ?>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['genre'] ?? 'N/A') ?></td>
                            <td>
                                <?php if ($book['number_of_copies'] > 0): ?>
                                    <span class="status-badge status-available">Available (<?= $book['number_of_copies'] ?>)</span>
                                <?php else: ?>
                                    <span class="status-badge status-borrowed">Borrowed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #64748b;">No matching books found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="actions">
            <a href="dashborad.php" class="btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
    </div>
</body>

</html>
