<?php
require_once '../config/db.php';

$loginError = '';
$registerError = '';
$success = '';
$showRegisterSide = false;
$registerUsername = '';

if (isset($_GET['registered'])) {
    $success = 'Account created successfully! Please log in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'login') {
        $inputUsername = trim($_POST['username']);
        $inputPassword = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $inputUsername);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($inputPassword, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: ../dashboard.php');
            exit;
        } else {
            $loginError = 'Invalid username or password.';
        }
    } elseif ($formAction === 'register') {
        $showRegisterSide = true;
        $registerUsername = trim($_POST['username']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        if (empty($registerUsername) || empty($password)) {
            $registerError = 'All fields are required.';
        } elseif (strlen($password) < 6) {
            $registerError = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirmPassword) {
            $registerError = 'Passwords do not match.';
        } else {
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
            $checkStmt->bindParam(':username', $registerUsername);
            $checkStmt->execute();

            if ($checkStmt->fetch()) {
                $registerError = 'That username is already taken.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
                $insertStmt->bindParam(':username', $registerUsername);
                $insertStmt->bindParam(':password', $hashedPassword);
                $insertStmt->execute();

                header('Location: index.php?registered=1');
                exit;
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
    <title>Task Manager</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">

    <div class="flip-card-container">
        <div class="flip-card" id="flipCard">

            <!-- FRONT: Login -->
            <div class="flip-card-face flip-card-front">
                <div class="login-container">
                    <h2>Task Manager Login</h2>

                    <?php if ($success): ?>
                        <p class="success-msg"><?php echo htmlspecialchars($success); ?></p>
                    <?php endif; ?>

                    <?php if ($loginError): ?>
                        <p class="error-msg"><?php echo htmlspecialchars($loginError); ?></p>
                    <?php endif; ?>

                    <form method="POST" action="index.php">
                        <input type="hidden" name="form_action" value="login">

                        <label for="login_username">Username</label>
                        <input type="text" id="login_username" name="username" required>

                        <label for="login_password">Password</label>
                        <input type="password" id="login_password" name="password" required>

                        <button type="submit">Login</button>
                    </form>

                    <p class="hint">Don't have an account? <a href="#" class="auth-link" onclick="flipCard(event)">Register</a></p>
                </div>
            </div>

            <!-- BACK: Register -->
            <div class="flip-card-face flip-card-back">
                <div class="login-container">
                    <h2>Create an Account</h2>

                    <?php if ($registerError): ?>
                        <p class="error-msg"><?php echo htmlspecialchars($registerError); ?></p>
                    <?php endif; ?>

                    <form method="POST" action="index.php">
                        <input type="hidden" name="form_action" value="register">

                        <label for="reg_username">Username</label>
                        <input type="text" id="reg_username" name="username" required
                               value="<?php echo htmlspecialchars($registerUsername); ?>">

                        <label for="reg_password">Password</label>
                        <input type="password" id="reg_password" name="password" required minlength="6">

                        <label for="reg_confirm_password">Confirm Password</label>
                        <input type="password" id="reg_confirm_password" name="confirm_password" required minlength="6">

                        <button type="submit">Register</button>
                    </form>

                    <p class="hint">Already have an account? <a href="#" class="auth-link" onclick="flipCard(event)">Log in</a></p>
                </div>
            </div>

        </div>
    </div>

    <script>
        function flipCard(e) {
            e.preventDefault();
            document.getElementById('flipCard').classList.toggle('flipped');
        }

        <?php if ($showRegisterSide): ?>
        // A registration error just happened - stay flipped to the register side
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('flipCard').classList.add('flipped');
        });
        <?php endif; ?>
    </script>

</body>
</html>