<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$errors  = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $year   = trim($_POST['year'] ?? '');
    $copies = intval($_POST['copies'] ?? 1);
    $genre  = trim($_POST['genre'] ?? '');
    $desc   = trim($_POST['description'] ?? '');

    if (empty($title)) $errors[] = "Book Title is required.";
    if (empty($author)) $errors[] = "Author is required.";
    if ($copies < 1) $errors[] = "Number of copies must be at least 1.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO books (title, author, year_published, number_of_copies, genre, description) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $author, $year ? intval($year) : null, $copies, $genre ?: null, $desc ?: null])) {
            $success = "Book \"" . $title . "\" added successfully! Redirecting to dashboard...";
            header("Refresh: 1.5; url=dashborad.php");
        } else {
            $errors[] = "Failed to add the book. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Add a new book to the Online Library Management System.">
    <title>Add Book - Online Library Management System</title>
    <link rel="stylesheet" href="css/index.css">
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
        <a href="dashborad.php" class="navbar-brand">Library System</a>
        <div class="navbar-actions">
            <a href="dashborad.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="page-card" style="max-width: 500px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2>Add New Book</h2>
        <span class="page-sub" style="color: #64748b; font-size: 0.9rem; display: block; margin-bottom: 20px;">Fill in the details below to add a book to the library.</span>

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

        <form action="add_books.php" method="post">

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="book-title" style="display: block; margin-bottom: 5px; font-weight: 500;">Book Title</label>
                <input type="text" id="book-title" name="title" placeholder="Enter the book title"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="book-author" style="display: block; margin-bottom: 5px; font-weight: 500;">Author</label>
                <input type="text" id="book-author" name="author" placeholder="Enter the author name"
                       value="<?= htmlspecialchars($_POST['author'] ?? '') ?>" required
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px;">
            </div>

            <div class="form-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label for="book-year" style="display: block; margin-bottom: 5px; font-weight: 500;">Year Published</label>
                    <input type="number" id="book-year" name="year" placeholder="e.g. 2023" min="1000" max="2099"
                           value="<?= htmlspecialchars($_POST['year'] ?? '') ?>"
                           style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px;">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="book-copies" style="display: block; margin-bottom: 5px; font-weight: 500;">Number of Copies</label>
                    <input type="number" id="book-copies" name="copies" placeholder="e.g. 5" min="1"
                           value="<?= htmlspecialchars($_POST['copies'] ?? '1') ?>"
                           style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="book-genre" style="display: block; margin-bottom: 5px; font-weight: 500;">Genre</label>
                <select id="book-genre" name="genre" style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px; background: #fff;">
                    <option value="">Select genre</option>
                    <option <?= (($_POST['genre'] ?? '') === 'Fiction') ? 'selected' : '' ?>>Fiction</option>
                    <option <?= (($_POST['genre'] ?? '') === 'Non-Fiction') ? 'selected' : '' ?>>Non-Fiction</option>
                    <option <?= (($_POST['genre'] ?? '') === 'Science') ? 'selected' : '' ?>>Science</option>
                    <option <?= (($_POST['genre'] ?? '') === 'History') ? 'selected' : '' ?>>History</option>
                    <option <?= (($_POST['genre'] ?? '') === 'Technology') ? 'selected' : '' ?>>Technology</option>
                    <option <?= (($_POST['genre'] ?? '') === 'Biography') ? 'selected' : '' ?>>Biography</option>
                    <option <?= (($_POST['genre'] ?? '') === 'Education') ? 'selected' : '' ?>>Education</option>
                    <option <?= (($_POST['genre'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="book-desc" style="display: block; margin-bottom: 5px; font-weight: 500;">Description (optional)</label>
                <textarea id="book-desc" name="description" placeholder="Brief description of the book..."
                          style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 5px; height: 80px; resize: vertical;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions" style="display: flex; gap: 15px;">
                <button type="submit" class="btn" id="btn-add-book">Add Book</button>
                <a href="dashborad.php" class="btn btn-secondary" style="line-height: 2.2; text-align: center; display: inline-block;">Cancel</a>
            </div>

        </form>
    </div>

</body>

</html>
