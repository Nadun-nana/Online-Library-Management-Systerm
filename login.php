<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashborad.php");
    exit;
}

require_once 'db.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username)) $errors[] = "Username or Email is required.";
    if (empty($password)) $errors[] = "Password is required.";

    if (empty($errors)) {
        // Look up by username OR email
        $stmt = $pdo->prepare(
            "SELECT id, username, password FROM `register`
             WHERE username = ? OR email = ? LIMIT 1"
        );
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login success — set session variables
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $success = true;
            // Redirect to dashboard
            header("Location: dashborad.php");
            exit;
        } else {
            $errors[] = "Invalid username/email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Library Management System of Nebula Institue of Technology</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .alert { padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; font-size: 0.95rem; }
        .alert-error   { background: #fde8e8; color: #b91c1c; border: 1px solid #fca5a5; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert ul { margin: 5px 0 0 18px; padding: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fa-solid fa-right-to-bracket" style="margin-right:8px;"></i>Login</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username"><i class="fa-solid fa-user" style="margin-right:5px;"></i>Username or Email:</label>
                <input type="text" id="username" name="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock" style="margin-right:5px;"></i>Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
                <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
            </div>
        </form>

        <p style="margin-top: 15px; font-size: 14px;">
            Don't have an account?
            <a href="register.php" style="color: #3498db; text-decoration: none;">Register Here!</a>
        </p>
    </div>
</body>
</html>
