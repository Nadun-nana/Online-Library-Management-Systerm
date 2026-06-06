<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$errors  = [];
$success = "";
$userId  = $_SESSION['user_id'];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId     = trim($_POST['book_id'] ?? '');
    $borrowDate = trim($_POST['borrow_date'] ?? '');
    $returnDate = trim($_POST['return_date'] ?? '');

    if (empty($bookId)) $errors[] = "Please select a book.";
    if (empty($borrowDate)) $errors[] = "Borrow date is required.";
    if (empty($returnDate)) $errors[] = "Return date is required.";

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Check if the book is available
            $stmt = $pdo->prepare("SELECT id, title, number_of_copies FROM books WHERE id = ? FOR UPDATE");
            $stmt->execute([$bookId]);
            $book = $stmt->fetch();

            if (!$book) {
                $errors[] = "The selected book does not exist.";
            } elseif ($book['number_of_copies'] < 1) {
                $errors[] = "Sorry, \"" . $book['title'] . "\" is currently out of stock.";
            } else {
                // Insert borrow record
                $stmt = $pdo->prepare("INSERT INTO borrowed_books (book_id, user_id, borrow_date, return_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
                $stmt->execute([$bookId, $userId, $borrowDate, $returnDate]);

                // Update book copies
                $stmt = $pdo->prepare("UPDATE books SET number_of_copies = number_of_copies - 1 WHERE id = ?");
                $stmt->execute([$bookId]);

                $pdo->commit();
                $success = "Successfully borrowed \"" . $book['title'] . "\"! Redirecting to dashboard...";
                header("Refresh: 1.5; url=dashborad.php");
            }
        } catch (\Exception $e) {
            $pdo->rollBack();
            $errors[] = "Database transaction failed: " . $e->getMessage();
        }
    }
}

// Fetch list of available books
$books = $pdo->query("SELECT id, title, author, number_of_copies FROM books WHERE number_of_copies > 0 ORDER BY title ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Books - Online Library Management System</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .alert { padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; font-size: 0.95rem; }
        .alert-error   { background: #fde8e8; color: #b91c1c; border: 1px solid #fca5a5; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert ul { margin: 5px 0 0 18px; padding: 0; }
    </style>
</head>

<body class="dashboard-body">

    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashborad.php" class="navbar-brand"><i class="fa-solid fa-book-open" style="margin-right:7px;"></i>Library System</a>
        <div class="navbar-actions">
            <a href="dashborad.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </nav>

    <div class="page-card" style="max-width: 500px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2><i class="fa-solid fa-hand-holding-heart" style="margin-right:7px;"></i>Borrow Book</h2>
        <span class="page-sub" style="color: #64748b; font-size: 0.9rem; display: block; margin-bottom: 20px;">Choose a book from our catalog to borrow.</span>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="borrow_books.php" method="post">
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="book_id" style="display: block; margin-bottom: 5px; font-weight: 500;"><i class="fa-solid fa-book" style="margin-right:5px;"></i>Select Book:</label>
                <select id="book_id" name="book_id" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px; background: #fff;">
                    <option value="">-- Choose a Book --</option>
                    <?php foreach ($books as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['title']) ?> by <?= htmlspecialchars($b['author']) ?> (<?= $b['number_of_copies'] ?> copies available)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="borrow_date" style="display: block; margin-bottom: 5px; font-weight: 500;"><i class="fa-solid fa-calendar-day" style="margin-right:5px;"></i>Date of Borrowing:</label>
                <input type="date" id="borrow_date" name="borrow_date" required
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="return_date" style="display: block; margin-bottom: 5px; font-weight: 500;"><i class="fa-solid fa-calendar-check" style="margin-right:5px;"></i>Expected Return Date:</label>
                <input type="date" id="return_date" name="return_date" required
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px;">
            </div>

            <div class="form-actions" style="display: flex; gap: 15px;">
                <input type="submit" value="Borrow Book" class="btn">
                <a href="dashborad.php" class="btn btn-secondary" style="line-height: 2.2; text-align: center; display: inline-block;"><i class="fa-solid fa-xmark"></i> Cancel</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date();
            const borrowDateInput = document.getElementById('borrow_date');
            const returnDateInput = document.getElementById('return_date');

            const formatDate = (date) => {
                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            };

            // Pre-fill borrow date to today if empty
            if (!borrowDateInput.value) {
                borrowDateInput.value = formatDate(today);
            }

            // Pre-fill return date to 14 days from today if empty
            if (!returnDateInput.value) {
                const returnDate = new Date(today);
                returnDate.setDate(today.getDate() + 14);
                returnDateInput.value = formatDate(returnDate);
            }
        });
    </script>
</body>

</html>
