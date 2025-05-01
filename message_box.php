<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// DB接続
$dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
$db_user = 'root';
$db_pass = 'root';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 受信メッセージ取得（最新順）
    $stmt = $pdo->prepare('
        SELECT m.*, u.username AS sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.receiver_id = ?
        ORDER BY m.created_at DESC
    ');
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // すべてのメッセージを既読にする
    $pdo->prepare('UPDATE messages SET is_read = 1 WHERE receiver_id = ?')->execute([$user_id]);

} catch (PDOException $e) {
    die('データベースエラー: ' . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>メッセージボックス</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f4f4f4;
            padding: 30px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .message {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }

        .message:last-child {
            border-bottom: none;
        }

        .sender {
            font-weight: bold;
            color: #3498db;
        }

        .time {
            font-size: 12px;
            color: #888;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }

        .back-link:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>📩 メッセージボックス</h1>

    <?php if (empty($messages)): ?>
        <p style="text-align: center;">まだメッセージは届いていません。</p>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
    <div class="message">
        <div class="sender"><?= htmlspecialchars($msg['sender_name']) ?> さんから</div>
        <div class="time"><?= htmlspecialchars($msg['created_at']) ?></div>
        <div class="body"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>

        <!-- 返信フォーム -->
        <form method="POST" action="send_message.php" style="margin-top: 10px;">
            <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($msg['sender_id']) ?>">
            <textarea name="message" rows="2" style="width: 100%;" placeholder="返信メッセージを入力" required></textarea>
            <button type="submit" style="margin-top: 5px;">返信する</button>
        </form>
    </div>
<?php endforeach; ?>
    <?php endif; ?>

    <div style="text-align:center;">
        <a class="back-link" href="mypage.php">マイページに戻る</a>
    </div>
</div>

</body>
</html>
