<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

file_put_contents('debug_log.txt', "user_id={$user_id}, game_id={$game_id}\n", FILE_APPEND);

$dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
$db_user = 'root';
$db_pass = 'root';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ゲーム名取得
    $stmt = $pdo->prepare('SELECT name FROM games WHERE id = ?');
    $stmt->execute([$game_id]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);

    // レート推移取得
    $stmt = $pdo->prepare('
        SELECT rate, created_at
        FROM user_game_rate_logs
        WHERE user_id = ? AND game_id = ?
        ORDER BY created_at ASC
    ');
    $stmt->execute([$user_id, $game_id]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $rates = [];
    foreach ($logs as $log) {
        $labels[] = date('Y/m/d', strtotime($log['created_at']));
        $rates[] = (int)$log['rate'];
    }

    echo json_encode([
        'game_name' => isset($game['name']) ? $game['name'] : 'ゲーム名不明',
        'labels' => $labels,
        'rates' => $rates
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
