<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$books = $pdo->query("SELECT title, author, number_of_copies FROM books ORDER BY title ASC")->fetchAll();
?>
<html>

<head>
    <title>View Books - Online Library Management System</title>
    <link rel="stylesheet" href="css/index.css">
    <style>
        .container {
            width: 550px;
            /* Slightly wider for the table layout */
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
            /* Matches body background color */
            color: #2c3e50;
            /* Matches heading color */
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            /* Rounded pill style for modern look */
            font-size: 12px;
            font-weight: 600;
        }

        .status-available {
            background-color: #e6f4ea;
            /* Soft modern green */
            color: #137333;
            /* Dark green text */
        }

        .status-borrowed {
            background-color: #fce8e6;
            /* Soft modern red */
            color: #c5221f;
            /* Dark red text */
        }

        .search-box {
            margin: 20px 0 10px 0;
            width: 100%;
        }

        .search-box input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e0;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: #fff;
            color: #2d3748;
        }

        .search-box input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
        }

        .actions {
            margin-top: 25px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Book Catalog</h1>
        <p>List of all books and their current status</p>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search books by title or author...">
        </div>

        <table class="book-table">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($books)): ?>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
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
                        <td colspan="3" style="text-align: center; color: #64748b;">No books found in the library.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="actions">
            <a href="dashborad.php" class="btn">Back</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const tableRows = document.querySelectorAll('.book-table tbody tr');

            searchInput.addEventListener('input', () => {
                const query = searchInput.value.toLowerCase().trim();

                tableRows.forEach(row => {
                    if (row.cells.length >= 2) {
                        const title = row.cells[0].textContent.toLowerCase();
                        const author = row.cells[1].textContent.toLowerCase();

                        if (title.includes(query) || author.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
