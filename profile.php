<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

$dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
$db_user = 'root';
$db_pass = 'root';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('SELECT username, profile_comment, profile_image_path FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('ユーザーが見つかりません。');
    }

} catch (PDOException $e) {
    die('データベースエラー: ' . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['username']) ?>さんのプロフィール</title>
    <style>
        body {
            background-color: #f7f7f7;
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
        }
        .profile-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 600px;
            max-width: 95%;
            text-align: center;
        }
        img.profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }
        textarea {
            width: 90%;
            height: 100px;
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: vertical;
        }
        button {
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        a.back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #3498db;
        }
        .popup-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #d6e9c6;
        }
    </style>
</head>
<body>
<div class="profile-container">
    <h1><?= htmlspecialchars($user['username']) ?>さんのプロフィール</h1>

    <?php if (isset($_GET['sent']) && $_GET['sent'] === '1'): ?>
        <div class="popup-message" id="popup">📨 メッセージを送信しました</div>
    <?php endif; ?>

    <img src="<?= htmlspecialchars($user['profile_image_path'] ?: '/exercise/bgmaster/image/default_profile.png') ?>" 
         alt="プロフィール画像" class="profile-img">

    <p><?= nl2br(htmlspecialchars($user['profile_comment'] ?: 'コメント未設定です')) ?></p>

    <form action="send_message.php" method="POST">
        <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($user_id) ?>">
        <textarea name="message" placeholder="メッセージを入力" required></textarea><br>
        <button type="submit">メッセージを送る</button>
    </form>

    <a class="back-link" href="ranking.php">ランキング一覧に戻る</a>
</div>

<script>
    // 一定時間後にポップアップを非表示にする
    const popup = document.getElementById('popup');
    if (popup) {
        setTimeout(() => {
            popup.style.display = 'none';
        }, 3000);
    }
</script>
</body>
</html>
