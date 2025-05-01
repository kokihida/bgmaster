<?php
session_start();

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

    $game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

    // ゲーム情報
    $stmt = $pdo->prepare('SELECT name FROM games WHERE id = ?');
    $stmt->execute([$game_id]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$game) {
        die('ゲームが見つかりません。');
    }

    // 強者ランキング取得（ユーザーIDも含む）
    $stmt = $pdo->prepare('
        SELECT u.id AS user_id, u.username, ugr.rate
        FROM user_game_rates ugr
        INNER JOIN users u ON ugr.user_id = u.id
        WHERE ugr.game_id = ? AND u.role = "user"
        ORDER BY ugr.rate DESC
        LIMIT 10
    ');
    $stmt->execute([$game_id]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('データベースエラー: ' . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($game['name']) ?> 強者ランキング</title>
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
            margin-bottom: 20px;
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
    <h1><?= htmlspecialchars($game['name']) ?> 強者ランキング</h1>

    <table>
        <thead>
            <tr>
                <th>順位</th>
                <th>ユーザー名</th>
                <th>レート</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($players as $index => $player): 
                $rank = $index + 1;
                $crown = '';
                $row_style = '';

                if ($rank == 1) {
                    $crown = '<span class="crown gold">👑</span>';
                    $row_style = 'style="background-color: #fff8dc;"';
                } elseif ($rank == 2) {
                    $crown = '<span class="crown silver">👑</span>';
                    $row_style = 'style="background-color: #e0e0e0;"';
                } elseif ($rank == 3) {
                    $crown = '<span class="crown bronze">👑</span>';
                    $row_style = 'style="background-color: #faebd7;"';
                }
            ?>
                <tr <?= $row_style ?>>
                    <td><?= $crown ?><?= $rank ?>位</td>
                    <td>
                        <a href="profile.php?user_id=<?= htmlspecialchars($player['user_id']) ?>">
                            <?= htmlspecialchars($player['username']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($player['rate']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a class="back-link" href="ranking.php">ゲーム選択画面に戻る</a>
</div>

</body>
</html>
