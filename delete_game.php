<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['game_id'])) {
    $game_id = (int)$_POST['game_id'];

    $dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
    $db_user = 'root';
    $db_pass = 'root';

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 関連するレート情報も削除（必要に応じて）
        $stmt = $pdo->prepare('DELETE FROM user_game_rates WHERE game_id = ?');
        $stmt->execute([$game_id]);

        // ゲーム本体削除
        $stmt = $pdo->prepare('DELETE FROM games WHERE id = ?');
        $stmt->execute([$game_id]);

        header('Location: admin_menu.php');
        exit;

    } catch (PDOException $e) {
        die("データベースエラー: " . htmlspecialchars($e->getMessage()));
    }
}
?>
