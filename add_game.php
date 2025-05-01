<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// 入力チェック
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $game_name = isset($_POST['game_name']) ? trim($_POST['game_name']) : '';
    $image_path = isset($_POST['image_path']) ? trim($_POST['image_path']) : '';


    if ($game_name === '') {
        die('ゲーム名を入力してください。');
    }

    // DB接続
    $dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
    $db_user = 'root';
    $db_pass = 'root';

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // トランザクション開始（安全にまとめる）
        $pdo->beginTransaction();

        // 1. gamesテーブルにゲームを追加
        $stmt = $pdo->prepare('INSERT INTO games (name, image_path) VALUES (?, ?)');
        $stmt->execute([$game_name, $image_path]);
        
        // 新しく追加されたゲームIDを取得
        $new_game_id = $pdo->lastInsertId();

        // 2. usersテーブルから全ユーザーIDを取得
        $stmt = $pdo->query('SELECT id FROM users WHERE role = "user"');
        $user_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 3. user_game_ratesに初期レート100で登録
        $stmt = $pdo->prepare('INSERT INTO user_game_rates (user_id, game_id, rate) VALUES (?, ?, 100)');
        foreach ($user_ids as $user_id) {
            $stmt->execute([$user_id, $new_game_id]);
        }

        // トランザクション成功
        $pdo->commit();

        // 成功したら管理者メニューに戻る
        header('Location: admin_menu.php');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack(); // 失敗したら全部取り消す
        die('データベースエラー: ' . htmlspecialchars($e->getMessage()));
    }
} else {
    header('Location: admin_menu.php');
    exit;
}
