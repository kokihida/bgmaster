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
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");


    // ユーザー情報取得
    $stmt = $pdo->prepare('SELECT username, profile_image_path, profile_comment FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        die('ユーザーが見つかりません。');
    }

    // 新着メッセージ取得
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0');
    $stmt->execute([$user_id]);
    $new_message_count = $stmt->fetchColumn();

    // ゲーム別レート取得
    $stmt = $pdo->prepare('
        SELECT g.id AS game_id, g.name AS game_name, g.image_path, ugr.rate
        FROM user_game_rates ugr
        INNER JOIN games g ON ugr.game_id = g.id
        WHERE ugr.user_id = ?
        ORDER BY g.id ASC
    ');
    $stmt->execute([$user_id]);
    $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 総合レート
    $stmt = $pdo->prepare('SELECT SUM(rate) AS total_rate FROM user_game_rates WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $total_rate = $stmt->fetchColumn();

    // ゲームごとにランキング順位も取得
    $rates_with_rank = [];
    foreach ($rates as $rate) {
        $game_id = $rate['game_id'];

        $stmt = $pdo->prepare('
            SELECT user_id FROM user_game_rates
            WHERE game_id = ?
            ORDER BY rate DESC
            LIMIT 10
        ');
        $stmt->execute([$game_id]);
        $top_players = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $rank = 'ランキング外';
        foreach ($top_players as $i => $uid) {
            if ((int)$uid === (int)$user_id) {
                $rank = ($i + 1) . '位';
                break;
            }
        }

        $rates_with_rank[] = [
            'game_id' => $rate['game_id'],
            'game_name' => $rate['game_name'],
            'image_path' => $rate['image_path'],
            'rate' => $rate['rate'],
            'rank' => $rank
        ];
    }

} catch (PDOException $e) {
    die('データベースエラー: ' . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['username']) ?>さんのマイページ</title>
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
        .mypage-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 700px;
            max-width: 95%;
            text-align: center;
        }
        h1, h2 {
            margin-bottom: 20px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f0f0f0;
        }
        .game-link {
            text-decoration: none;
            color: inherit;
        }
        .back-link {
            margin-top: 10px;
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
        form textarea {
            width: 80%;
            height: 60px;
            padding: 5px;
            margin-top: 5px;
        }

        .message-button {
            display: inline-block;
            background-color: #e67e22;
            color: #fff;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .message-button:hover {
            background-color: #d35400;
        }

    </style>
</head>
<body>

<div class="mypage-container">
    <h1><?= htmlspecialchars($user['username']) ?>さんのマイページ</h1>

    <img src="<?= htmlspecialchars($user['profile_image_path'] ?: '/exercise/bgmaster/image/default_profile.png') ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;"><br><br>

    <form method="POST" action="update_profile.php" enctype="multipart/form-data" accept-charset="UTF-8">
        <div>
            <label>プロフィール画像:</label><br>
            <input type="file" name="profile_image">
        </div>
        <div>
            <label>一言コメント:</label><br>
            <textarea name="profile_comment" maxlength="255"><?= htmlspecialchars($user['profile_comment']) ?></textarea>
        </div>
        <button type="submit">プロフィールを更新</button>

        <div style="margin-top: 20px;">
                <a href="message_box.php" class="message-button">
            📨 メッセージボックス
        </a>
</div>

    </form>

    <?php if ($new_message_count > 0): ?>
        <p style="color: red;">
            📩 あなた宛に <strong><?= htmlspecialchars($new_message_count) ?>件</strong> の新しいメッセージがあります！<br>
            <a href="message_box.php">メッセージを確認する</a>
        </p>
    <?php endif; ?>

    <h2>ゲーム別レート一覧</h2>
    <table>
        <thead>
            <tr>
                <th>ゲーム</th>
                <th>レート（順位）</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rates_with_rank as $item): ?>
                <tr>
                    <td>
                        <?php if (!empty($item['image_path'])): ?>
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['game_name']) ?>" style="width:40px; height:40px; vertical-align:middle; margin-right:8px;">
                        <?php endif; ?>
                        <a href="#" class="game-link" data-game-id="<?= htmlspecialchars($item['game_id']) ?>">
                            <?= htmlspecialchars($item['game_name']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($item['rate']) ?>（<?= htmlspecialchars($item['rank']) ?>）</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>総合レート：<?= htmlspecialchars($total_rate) ?></h2>
    <a class="back-link" href="menu.php">メニューに戻る</a>
</div>

<!-- グラフモーダル -->
<div id="graphModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); text-align:center; padding-top:50px;">
    <div style="display:inline-block; background:#fff; padding:30px; border-radius:12px; position:relative; width: 700px; max-width: 90%;">
        <button onclick="closeModal()" style="position:absolute; top:10px; right:10px; background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
        <canvas id="graphCanvas" width="650" height="400"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartInstance = null;

document.querySelectorAll('.game-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const gameId = this.getAttribute('data-game-id');
        openGraphModal(gameId);
    });
});

function openGraphModal(gameId) {
    fetch('get_rate_data.php?game_id=' + gameId)
        .then(response => response.json())
        .then(data => {
            if (chartInstance) chartInstance.destroy();
            const ctx = document.getElementById('graphCanvas').getContext('2d');
            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: data.game_name,
                        data: data.rates,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        pointRadius: 4,
                        tension: 0.4
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: false }
                    }
                }
            });
            document.getElementById('graphModal').style.display = 'block';
        })
        .catch(err => {
            console.error(err);
            alert('データ取得に失敗しました');
        });
}

function closeModal() {
    document.getElementById('graphModal').style.display = 'none';
}
</script>

</body>
</html>
