<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// データベース接続
$dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
$db_user = 'root';
$db_pass = 'root';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query('SELECT id, name, image_path FROM games ORDER BY id ASC');
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('データベースエラー: ' . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ランキング選択</title>
    <style>
        body {
            background-color: #f7f7f7;
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .section {
            background: #fff;
            border: 2px solid #3498db;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 1000px;
            margin-bottom: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .game-card {
            background: #ecf5fc;
            padding: 10px;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
            width: 200px;
        }
        .game-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        .game-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .game-card p {
            margin-top: 10px;
            font-size: 18px;
            color: #333;
            font-weight: bold;
        }
        .back-link {
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .back-link:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <h1>ボードゲーム別強者ランキング</h1>

    <div class="section">
        <div class="card-container">
            <?php foreach ($games as $game): ?>
                <div class="game-card">
                    <a href="game_ranking.php?game_id=<?= htmlspecialchars($game['id']) ?>">
                        <img src="<?= htmlspecialchars($game['image_path']) ?>" alt="<?= htmlspecialchars($game['name']) ?>">
                    </a>
                    <p><?= htmlspecialchars($game['name']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <h1>プレイヤー別強者ランキング</h1>

    <div class="section">
        <div class="card-container">
            <div class="game-card">
                <a href="player_ranking.php">
                    <img src="/exercise/bgmaster/image/player_ranking.png" alt="プレイヤーランキング">
                </a>
                <p>総合プレイヤーランキングを見る</p>
            </div>
        </div>
    </div>

    <a class="back-link" href="menu.php">メニューへ戻る</a>

</body>
</html>
