<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Online Library Management System — Borrow, search and manage books with ease.">
    <title>Online Library Management System</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="container">

        <!-- Logo -->
        <div class="logo-icon"></div>
        <h1><i class="fa-solid fa-book-open" style="margin-right:10px;"></i>Library System</h1>
        <p class="subtitle">Your digital gateway to thousands of books</p>

        <?php if ($isLoggedIn): ?>
            <p>Welcome back, <strong style="color:#a5b4fc;"><?= htmlspecialchars($username) ?></strong>!</p>
            <p>You are currently logged in.</p>
            <div class="buttons" style="display: flex; gap: 15px; margin-top: 20px; justify-content: center;">
                <a href="dashborad.php" class="btn">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
                <a href="logout.php" class="btn btn-secondary">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        <?php else: ?>
            <p>Welcome to the <strong style="color:#a5b4fc;">Online Library Management System</strong></p>
            <p>Please login or create an account to continue.</p>

            <div class="buttons" style="display: flex; gap: 15px; margin-top: 20px; justify-content: center;">
                <a href="login.php" class="btn" id="btn-login">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
                <a href="register.php" class="btn btn-secondary" id="btn-register">
                    <i class="fa-solid fa-user-plus"></i> Register
                </a>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>
