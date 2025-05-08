<?php
require 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $bio = trim($_POST['bio']);
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar = 'uploads/avatar_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar);
    }
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // pokud email nebo uz. jmeno jiz existuje
    $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $message = 'Uživatelské jméno nebo email již existuje!';
    } else {
        $stmt = $conn->prepare('INSERT INTO users (username, email, password_hash, bio, avatar) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sssss', $username, $email, $password_hash, $bio, $avatar);
        if ($stmt->execute()) {
            $message = 'Registrace proběhla úspěšně! <a href="login.php">Login here</a>.';
        } else {
            $message = 'Registrace se nezdařila!';
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrace</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
    <h2>Registrace</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Uživatelské jméno</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Heslo</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Bio</label>
            <textarea name="bio" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Avatar</label>
            <input type="file" name="avatar" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Registrovat</button>
        <a href="login.php" class="btn btn-link">Přihlásit se</a>
    </form>
</body>
</html> 