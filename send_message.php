<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if ($receiver_id > 0 && !empty($message)) {
        $dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
        $db_user = 'root';
        $db_pass = 'root';

        try {
            $pdo = new PDO($dsn, $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)');
            $stmt->execute([$sender_id, $receiver_id, $message]);

            // ✅ ここで「送信しました」フラグ付きで戻る
            header('Location: profile.php?user_id=' . $receiver_id . '&sent=1');
            exit;

        } catch (PDOException $e) {
            die('データベースエラー: ' . htmlspecialchars($e->getMessage()));
        }
    } else {
        // 入力が不正な場合は戻る（オプション）
        header('Location: profile.php?user_id=' . $receiver_id . '&sent=0');
        exit;
    }
}
?>
