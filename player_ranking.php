<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// DB接続
$dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
$db_user = 'root';
$db_pass = 'root';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query('
        SELECT 
            u.id, 
            u.username, 
            SUM(ugr.rate) AS total_rate
        FROM users u
        JOIN user_game_rates ugr ON u.id = ugr.user_id
        WHERE u.role = "user"
        GROUP BY u.id, u.username
        ORDER BY total_rate DESC
        LIMIT 10
    ');
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "user"');
    $player_count = $stmt->fetchColumn();

} catch (PDOException $e) {
    die('データベースエラー: ' . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>総合レートランキング</title>
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
        .ranking-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 600px;
            text-align: center;
        }
        .ranking-container h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .ranking-container h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #777;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f0f0f0;
        }
        .crown {
            font-size: 20px;
            margin-right: 5px;
        }
        .gold { color: gold; }
        .silver { color: silver; }
        .bronze { color: peru; }
        .back-link {
            margin-top: 20px;
            display: inline-block;
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<div class="ranking-container">
    <h1>総合レートランキング TOP10</h1>
    <h2>登録プレイヤー数：<?= htmlspecialchars($player_count) ?>人</h2>

    <table>
        <thead>
            <tr>
                <th>順位</th>
                <th>ユーザー名</th>
                <th>総合レート</th>
            </tr>
        </thead>
        <tbody>
            <?php $rank = 1; ?>
            <?php foreach ($players as $player): ?>
                <tr style="background-color: <?= $rank == 1 ? '#fff8dc' : ($rank == 2 ? '#e0e0e0' : ($rank == 3 ? '#faebd7' : '#fff')) ?>;">
                    <td>
                        <?php if ($rank == 1): ?>
                            <span class="crown gold">👑</span>1位
                        <?php elseif ($rank == 2): ?>
                            <span class="crown silver">👑</span>2位
                        <?php elseif ($rank == 3): ?>
                            <span class="crown bronze">👑</span>3位
                        <?php else: ?>
                            <?= $rank ?>位
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="profile.php?user_id=<?= htmlspecialchars($player['id']) ?>">
                            <?= htmlspecialchars($player['username']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($player['total_rate']) ?></td>
                </tr>
                <?php $rank++; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a class="back-link" href="ranking.php">ゲーム選択画面に戻る</a>
</div>

</body>
</html>
