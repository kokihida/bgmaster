<?php
session_start();

// ログイン＋管理者チェック
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// POSTデータ受け取り
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$new_rate = isset($_POST['new_rate']) ? (int)$_POST['new_rate'] : null;

// 入力チェック
if ($user_id <= 0 || $game_id <= 0 || $new_rate === null) {
    die('不正な入力データです。');
}

// DB接続
$dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
$db_user = 'root';
$db_pass = 'root';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // レート更新
    $stmt = $pdo->prepare('
        UPDATE user_game_rates
        SET rate = ?, updated_at = NOW()
        WHERE user_id = ? AND game_id = ?
    ');
    $stmt->execute([$new_rate, $user_id, $game_id]);
    
    // ここで履歴テーブルに記録する
    $stmt = $pdo->prepare('
    INSERT INTO user_game_rate_logs (user_id, game_id, rate)
    VALUES (?, ?, ?)
    ');
    $stmt->execute([$user_id, $game_id, $new_rate]);

    // レート変更後は、プレイヤー詳細画面に戻す
    header('Location: player_detail.php?user_id=' . $user_id);
    exit;

} catch (PDOException $e) {
    die('データベースエラー: ' . htmlspecialchars($e->getMessage()));
}
?>
