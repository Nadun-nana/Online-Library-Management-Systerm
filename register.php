<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashborad.php");
    exit;
}

require_once 'db.php';

$errors  = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');

    if (empty($username)) $errors[] = "Username is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        // Check if username or email is already taken
        $stmt = $pdo->prepare("SELECT id FROM `register` WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email is already registered.";
        } else {
            // Register user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO `register` (username, password, email, phone) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $hashedPassword, $email, $phone])) {
                $success = "Account created successfully! Redirecting to login...";
                header("Refresh: 2; url=login.php");
            } else {
                $errors[] = "Failed to register user. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Library Management System</title>
    <link rel="stylesheet" href="css/index.css">
    <style>
        .alert { padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; font-size: 0.95rem; }
        .alert-error   { background: #fde8e8; color: #b91c1c; border: 1px solid #fca5a5; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert ul { margin: 5px 0 0 18px; padding: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Account</h1>
        <p class="subtitle" style="margin-bottom: 20px; text-align: center; color: #64748b;">Join the library community today</p>

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

        <form action="register.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Choose a username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Create a strong password" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone (optional):</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>

            <div class="form-actions">
                <input type="submit" value="Register" class="btn">
                <a href="index.php" class="btn btn-secondary">Back</a>
            </div>
        </form>

        <p style="margin-top: 15px; font-size: 14px;">
            Already have an account?
            <a href="login.php" style="color: #3498db; text-decoration: none;">Login Here!</a>
        </p>
    </div>
</body>
</html>
