<?php
require 'config.php';
session_start();

//uzivatelske id
$user_id = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user_id'];
$is_own = ($user_id === $_SESSION['user_id']);

// update profilu
if ($is_own && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = trim($_POST['bio']);
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar = 'uploads/avatar_' . $user_id . '.' . $ext;
        move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar);
    }
    $stmt = $conn->prepare('UPDATE users SET bio = ?, avatar = IFNULL(?, avatar) WHERE id = ?');
    $stmt->bind_param('ssi', $bio, $avatar, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: profile.php');
    exit;
}

// ziskat udaje uziatele
$stmt = $conn->prepare('SELECT username, bio, avatar FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($username, $bio, $avatar);
$stmt->fetch();
$stmt->close();

// ziskat posty uziatele
$posts = [];
$stmt = $conn->prepare('SELECT content, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($content, $created_at);
while ($stmt->fetch()) {
    $posts[] = ['content' => $content, 'created_at' => $created_at];
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
    <a href="feed.php" class="btn btn-link">&larr; Back to Feed</a>
    <div class="card mb-4">
        <div class="card-body">
            <img src="<?php echo $avatar ? htmlspecialchars($avatar) : 'https://via.placeholder.com/100'; ?>" class="rounded-circle mb-3" width="100" height="100">
            <h3><?php echo htmlspecialchars($username); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($bio)); ?></p>
            <?php if ($is_own): ?>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-2">
                    <label>Bio</label>
                    <textarea name="bio" class="form-control"><?php echo htmlspecialchars($bio); ?></textarea>
                </div>
                <div class="mb-2">
                    <label>Avatar</label>
                    <input type="file" name="avatar" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <h4>Posts</h4>
    <?php foreach ($posts as $post): ?>
        <div class="card mb-2">
            <div class="card-body">
                <div class="text-muted small mb-1"><?php echo $post['created_at']; ?></div>
                <div><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($posts)): ?>
        <p>No posts yet.</p>
    <?php endif; ?>
</body>
</html> 