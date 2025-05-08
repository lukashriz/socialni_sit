<?php
require 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// novy post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content']) && !isset($_POST['like']) && !isset($_POST['comment'])) {
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare('INSERT INTO posts (user_id, content) VALUES (?, ?)');
    $stmt->bind_param('is', $user_id, $content);
    $stmt->execute();
    $stmt->close();
    header('Location: feed.php');
    exit;
}

// like
if (isset($_POST['like'])) {
    $post_id = intval($_POST['like']);
    $user_id = $_SESSION['user_id'];
    $conn->query("INSERT IGNORE INTO likes (post_id, user_id) VALUES ($post_id, $user_id)");
    header('Location: feed.php');
    exit;
}

// komentar
if (isset($_POST['comment']) && !empty($_POST['comment_content'])) {
    $post_id = intval($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['comment_content']);
    $stmt = $conn->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $post_id, $user_id, $content);
    $stmt->execute();
    $stmt->close();
    header('Location: feed.php');
    exit;
}

// odstranit post
if (isset($_POST['delete_post'])) {
    $post_id = intval($_POST['delete_post']);
    $user_id = $_SESSION['user_id'];
    $conn->query("DELETE FROM posts WHERE id = $post_id AND user_id = $user_id");
    header('Location: feed.php');
    exit;
}

// vyhledat
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = $search ? "WHERE posts.content LIKE '%" . $conn->real_escape_string($search) . "%' OR users.username LIKE '%" . $conn->real_escape_string($search) . "%'" : '';

// vsechny posty
$posts = [];
$sql = "SELECT posts.id, posts.content, posts.created_at, users.username, users.id as user_id, users.avatar FROM posts JOIN users ON posts.user_id = users.id $where ORDER BY posts.created_at DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // like pocet
        $like_res = $conn->query("SELECT COUNT(*) as cnt FROM likes WHERE post_id = " . $row['id']);
        $row['like_count'] = $like_res->fetch_assoc()['cnt'];
        // pocet komentaru
        $comment_res = $conn->query("SELECT COUNT(*) as cnt FROM comments WHERE post_id = " . $row['id']);
        $row['comment_count'] = $comment_res->fetch_assoc()['cnt'];
        // komentare
        $comments = [];
        $c_res = $conn->query("SELECT comments.content, comments.created_at, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = " . $row['id'] . " ORDER BY comments.created_at ASC");
        while ($c = $c_res->fetch_assoc()) {
            $comments[] = $c;
        }
        $row['comments'] = $comments;
        $posts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Hlavní stránka</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow mb-8">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="text-xl font-bold text-blue-600">Sociální síť</div>
            <div class="space-x-4">
                <a href="feed.php" class="text-gray-700 hover:text-blue-600">Hlavní stránka</a>
                <a href="profile.php" class="text-gray-700 hover:text-blue-600">Profil</a>
                <a href="logout.php" class="text-red-600 hover:text-red-800">Odhlásit se</a>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 max-w-2xl">
        <form method="get" class="mb-4">
            <div class="flex">
                <input type="text" name="search" class="flex-1 rounded-l px-4 py-2 border border-gray-300 focus:outline-none" placeholder="Hledat příspěvky nebo uživatele" value="<?php echo htmlspecialchars($search); ?>">
                <button class="bg-blue-600 text-white px-4 py-2 rounded-r" type="submit">Hledat</button>
            </div>
        </form>
        <form method="post" class="mb-6 bg-white p-4 rounded shadow">
            <textarea name="content" class="w-full border border-gray-300 rounded p-2 mb-2" placeholder="Co máte na srdci?" required></textarea>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Přidat příspěvek</button>
        </form>
        <div class="space-y-6">
            <?php foreach ($posts as $post): ?>
                <div class="bg-white rounded shadow p-4">
                    <div class="flex items-center mb-2">
                        <img src="<?php echo $post['avatar'] ? htmlspecialchars($post['avatar']) : 'https://via.placeholder.com/40'; ?>" class="rounded-full w-10 h-10 mr-3">
                        <a href="profile.php?id=<?php echo $post['user_id']; ?>" class="font-semibold text-blue-600 mr-2"><?php echo htmlspecialchars($post['username']); ?></a>
                        <span class="text-gray-500 text-sm"><?php echo $post['created_at']; ?></span>
                        <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                            <form method="post" class="ml-auto" style="display:inline">
                                <input type="hidden" name="delete_post" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="ml-2 text-red-600 hover:underline">Smazat</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <div class="flex items-center space-x-4 mb-2">
                        <form method="post">
                            <input type="hidden" name="like" value="<?php echo $post['id']; ?>">
                            <button type="submit" class="text-blue-600 hover:underline">To se mi líbí (<?php echo $post['like_count']; ?>)</button>
                        </form>
                        <span>Komentáře (<?php echo $post['comment_count']; ?>)</span>
                    </div>
                    <div class="space-y-2 mb-2">
                        <?php foreach ($post['comments'] as $comment): ?>
                            <div class="bg-gray-100 rounded p-2">
                                <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($comment['username']); ?></span>:
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                <span class="text-gray-400 text-xs ml-2"><?php echo $comment['created_at']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <form method="post" class="flex">
                        <input type="hidden" name="comment" value="<?php echo $post['id']; ?>">
                        <input type="text" name="comment_content" class="flex-1 border border-gray-300 rounded-l p-2" placeholder="Přidat komentář..." required>
                        <button type="submit" class="bg-gray-200 px-4 rounded-r">Komentovat</button>
                    </form>
                </div>
            <?php endforeach; ?>
            <?php if (empty($posts)): ?>
                <p class="text-center text-gray-500">Žádné příspěvky zatím nejsou.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html> 