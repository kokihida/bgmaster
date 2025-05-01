<?php
session_start();

// 管理者チェック
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// 必要なデータ受け取り
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$game_name = isset($_POST['game_name']) ? trim($_POST['game_name']) : '';
$image_path = isset($_POST['image_path']) ? trim($_POST['image_path']) : '';

// バリデーション
if ($game_id <= 0 || empty($game_name)) {
    die('無効な入力です。');
}

// DB接続
$dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
$db_user = 'root';
$db_pass = 'root';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('UPDATE games SET name = :name, image_path = :image_path WHERE id = :id');
    $stmt->bindValue(':name', $game_name);
    $stmt->bindValue(':image_path', $image_path);
    $stmt->bindValue(':id', $game_id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: admin_menu.php');
    exit;

} catch (PDOException $e) {
    die('データベースエラー: ' . htmlspecialchars($e->getMessage()));
}
?>
