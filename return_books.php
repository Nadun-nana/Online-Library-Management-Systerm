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
    $borrowId         = trim($_POST['borrow_id'] ?? '');
    $actualReturnDate = trim($_POST['actual_return_date'] ?? '');

    if (empty($borrowId)) $errors[] = "Please select a borrowed book.";
    if (empty($actualReturnDate)) $errors[] = "Actual return date is required.";

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Verify the borrow record belongs to this user and is still borrowed
            $stmt = $pdo->prepare("
                SELECT bb.id, bb.book_id, bb.return_date, b.title 
                FROM borrowed_books bb
                JOIN books b ON bb.book_id = b.id
                WHERE bb.id = ? AND bb.user_id = ? AND bb.status = 'borrowed'
                FOR UPDATE
            ");
            $stmt->execute([$borrowId, $userId]);
            $borrow = $stmt->fetch();

            if (!$borrow) {
                $errors[] = "No active borrow record found for this selection.";
            } else {
                // Calculate penalty
                $expectedDate = new DateTime($borrow['return_date']);
                $actualDate   = new DateTime($actualReturnDate);
                
                // Clear times for exact date diff
                $expectedDate->setTime(0, 0);
                $actualDate->setTime(0, 0);
                
                $penalty = 0;
                $lateDays = 0;
                if ($actualDate > $expectedDate) {
                    $diff = $expectedDate->diff($actualDate);
                    $lateDays = $diff->days;
                    $penalty = $lateDays * 5; // 5 LKR per day
                }

                // Update borrow record status to returned
                $stmt = $pdo->prepare("UPDATE borrowed_books SET status = 'returned', return_date = ? WHERE id = ?");
                $stmt->execute([$actualReturnDate, $borrowId]);

                // Increment book copy count
                $stmt = $pdo->prepare("UPDATE books SET number_of_copies = number_of_copies + 1 WHERE id = ?");
                $stmt->execute([$borrow['book_id']]);

                $pdo->commit();
                
                if ($penalty > 0) {
                    $success = "Book \"" . $borrow['title'] . "\" returned successfully! Late penalty: " . $penalty . " LKR (" . $lateDays . " days late). Redirecting to dashboard...";
                } else {
                    $success = "Book \"" . $borrow['title'] . "\" returned successfully on time! Redirecting to dashboard...";
                }
                header("Refresh: 2; url=dashborad.php");
            }
        } catch (\Exception $e) {
            $pdo->rollBack();
            $errors[] = "Database transaction failed: " . $e->getMessage();
        }
    }
}

// Fetch user's borrowed books
$stmt = $pdo->prepare("
    SELECT bb.id AS borrow_id, bb.return_date, b.title, b.author 
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.id
    WHERE bb.user_id = ? AND bb.status = 'borrowed'
    ORDER BY bb.borrow_date DESC
");
$stmt->execute([$userId]);
$borrowedList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Books - Online Library Management System</title>
    <link rel="stylesheet" href="css/index.css">
    <style>
        .alert { padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; font-size: 0.95rem; }
        .alert-error   { background: #fde8e8; color: #b91c1c; border: 1px solid #fca5a5; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert ul { margin: 5px 0 0 18px; padding: 0; }
        .penalty-highlight {
            font-weight: bold;
            color: #c5221f;
            background-color: #fce8e6 !important;
        }
    </style>
</head>

<body class="dashboard-body">

    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashborad.php" class="navbar-brand">Library System</a>
        <div class="navbar-actions">
            <a href="dashborad.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </nav>

    <div class="page-card" style="max-width: 500px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h1>Return Book</h1>
        <p class="page-sub" style="color: #64748b; font-size: 0.9rem; display: block; margin-bottom: 20px;">Return a borrowed book and calculate any overdue penalty fees.</p>

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

        <form action="return_books.php" method="post">
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="borrow_id" style="display: block; margin-bottom: 5px; font-weight: 500;">Select Borrowed Book:</label>
                <select id="borrow_id" name="borrow_id" required onchange="updateDueDate()"
                        style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px; background: #fff;">
                    <option value="">-- Choose a Borrowed Book --</option>
                    <?php foreach ($borrowedList as $item): ?>
                        <option value="<?= $item['borrow_id'] ?>" data-due="<?= $item['return_date'] ?>">
                            <?= htmlspecialchars($item['title']) ?> (Borrowed from <?= htmlspecialchars($item['author']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="return_date" style="display: block; margin-bottom: 5px; font-weight: 500;">Expected Return Date:</label>
                <input type="date" id="return_date" readonly
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px; background: #f4f7fc; cursor: not-allowed;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="actual_return_date" style="display: block; margin-bottom: 5px; font-weight: 500;">Actual Return Date:</label>
                <input type="date" id="actual_return_date" name="actual_return_date" required onchange="calculatePenalty()"
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="penalty" style="display: block; margin-bottom: 5px; font-weight: 500;">Penalty Fee (5 LKR per day):</label>
                <input type="text" id="penalty" readonly class="penalty-highlight" value="0 LKR"
                       style="width: 100%; padding: 10px; border: 1px solid #fca5a5; border-radius: 5px; font-weight: bold; outline: none;">
            </div>

            <div class="form-actions" style="display: flex; gap: 15px;">
                <input type="submit" value="Return Book" class="btn">
                <a href="dashborad.php" class="btn btn-secondary" style="line-height: 2.2; text-align: center; display: inline-block;">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        const returnDateInput = document.getElementById('return_date');
        const actualReturnDateInput = document.getElementById('actual_return_date');
        const penaltyInput = document.getElementById('penalty');
        const selectElement = document.getElementById('borrow_id');

        function updateDueDate() {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const dueDate = selectedOption.getAttribute('data-due');
                returnDateInput.value = dueDate;
            } else {
                returnDateInput.value = '';
            }
            calculatePenalty();
        }

        function calculatePenalty() {
            const returnDateVal = returnDateInput.value;
            const actualReturnDateVal = actualReturnDateInput.value;

            if (!returnDateVal || !actualReturnDateVal) {
                penaltyInput.value = "0 LKR";
                penaltyInput.style.color = '#2e7d32';
                penaltyInput.style.backgroundColor = '#e6f4ea';
                return;
            }

            const returnDate = new Date(returnDateVal);
            const actualReturnDate = new Date(actualReturnDateVal);

            // Set to midnight to calculate exact calendar day difference
            returnDate.setHours(0, 0, 0, 0);
            actualReturnDate.setHours(0, 0, 0, 0);

            const timeDiff = actualReturnDate.getTime() - returnDate.getTime();
            const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

            if (dayDiff > 0) {
                const penalty = dayDiff * 5;
                penaltyInput.value = `${penalty} LKR (${dayDiff} day${dayDiff === 1 ? '' : 's'} late)`;
                penaltyInput.style.color = '#c5221f';
                penaltyInput.style.backgroundColor = '#fce8e6';
            } else {
                penaltyInput.value = "0 LKR";
                penaltyInput.style.color = '#2e7d32';
                penaltyInput.style.backgroundColor = '#e6f4ea';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date();
            const formatDate = (date) => {
                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0');
                const dd = String(date.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            };
            actualReturnDateInput.value = formatDate(today);
            calculatePenalty();
        });
    </script>
</body>

</html>
