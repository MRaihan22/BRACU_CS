<?php
require_once('DBConnect.php');
require_once('auth.php');

$error = '';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password, name, user_type FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if ($user) {
        $ok = false;
        // Accept either hashed or legacy plain text (for first run admin)
        if (password_verify($password, $user['password'])) {
            $ok = true;
        } elseif ($password === $user['password']) {
            $ok = true;
            // Upgrade to hash
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
            $stmt2->bind_param('si', $newHash, $user['user_id']);
            $stmt2->execute();
            $stmt2->close();
        }

        if ($ok) {
            login_user($user);
            if (($user['user_type'] ?? '') === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        }
    }
    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ExploreBangla Travel</title>
</head>
<body class="with-video">
    <video class="bg-video" autoplay muted loop playsinline>
        <source src="assets/travel.mp4" type="video/mp4">
    </video>
    <div class="bg-overlay"></div>

    <main class="page">
        <div class="hero">
            <div class="card glass" style="max-width:520px;width:100%;">
                <div class="hero-badge">Welcome back</div>
                <h1 style="margin-top:10px;">Login</h1>
                <p class="muted">Explore routes across Bangladesh with comfort.</p>

                <?php if ($error): ?>
                    <div class="alert alert-error mt-12"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" class="mt-16">
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" required>
                    </div>
                    <div class="field mt-12">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>

                    <div class="actions mt-16">
                        <button type="submit" class="btn btn-primary">Login</button>
                        <a class="btn btn-ghost" href="register.php">Create account</a>
                    </div>
                </form>

                <p class="muted" style="margin-top:14px;font-size:13px;">
                    Admin demo: <b>admin / admin123</b>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
