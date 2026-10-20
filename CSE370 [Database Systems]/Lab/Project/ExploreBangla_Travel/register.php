<?php
require_once('DBConnect.php');

$message = '';
$error = '';

if (isset($_POST['username'], $_POST['password'], $_POST['name'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $name = trim($_POST['name']);

    if ($username === '' || $password === '' || $name === '') {
        $error = "All fields are required.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, username, password, user_type) VALUES (?, ?, ?, 'traveler')");
        $stmt->bind_param('sss', $name, $username, $hashed);
        if ($stmt->execute()) {
            $message = "Registration successful! You can now login.";
        } else {
            $error = "Username already exists. Try another.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ExploreBangla Travel</title>
</head>
<body class="with-video">
    <video class="bg-video" autoplay muted loop playsinline>
        <source src="assets/travel.mp4" type="video/mp4">
    </video>
    <div class="bg-overlay"></div>

    <main class="page">
        <div class="hero">
            <div class="card glass" style="max-width:560px;width:100%;">
                <div class="hero-badge">New traveler</div>
                <h1 style="margin-top:10px;">Create your account</h1>
                <p class="muted">Book seats on Bus, Train, or Plane — with a modern seat map.</p>

                <?php if ($message): ?>
                    <div class="alert mt-12"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error mt-12"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" class="mt-16">
                    <div class="field">
                        <label for="name">Full name</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    <div class="field mt-12">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" required>
                    </div>
                    <div class="field mt-12">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>

                    <div class="actions mt-16">
                        <button type="submit" class="btn btn-primary">Register</button>
                        <a class="btn btn-ghost" href="login.php">Back to login</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
