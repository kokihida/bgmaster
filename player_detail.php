<?php
session_start();

// ログイン＋管理者チェック
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// user_id受け取り
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($user_id <= 0) {
    die('不正なユーザーIDです');
}

// DB接続
$dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
$db_user = 'root';
$db_pass = 'root';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ユーザー情報取得
    $stmt = $pdo->prepare('SELECT username FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('ユーザーが見つかりませんでした。');
    }

    // ゲーム別レート取得
    $stmt = $pdo->prepare('
        SELECT g.id AS game_id, g.name AS game_name, ugr.rate 
        FROM user_game_rates ugr
        INNER JOIN games g ON ugr.game_id = g.id
        WHERE ugr.user_id = ?
        ORDER BY g.id ASC
    ');
    $stmt->execute([$user_id]);
    $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('データベースエラー: ' . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['username']) ?>さんのレート管理</title>
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

        .detail-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 700px;
            max-width: 95%;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f0f0f0;
        }

        input[type="number"] {
            padding: 6px;
            width: 100px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #3498db;
            color: #fff;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        .back-link {
            margin-top: 30px;
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

<div class="detail-container">
    <h1><?= htmlspecialchars($user['username']) ?>さんのレート管理</h1>

    <table>
        <thead>
            <tr>
                <th>ゲーム名</th>
                <th>現在のレート</th>
                <th>レート更新</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rates as $rate): ?>
                <tr>
                    <td><?= htmlspecialchars($rate['game_name']) ?></td>
                    <td><?= htmlspecialchars($rate['rate']) ?></td>
                    <td>
                        <form method="POST" action="update_game_rate.php" style="display:flex; gap:10px; justify-content:center;">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                            <input type="hidden" name="game_id" value="<?= htmlspecialchars($rate['game_id']) ?>">
                            <input type="number" name="new_rate" required placeholder="新しいレート">
                            <button type="submit">更新</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- 管理操作ボタン -->
    <div style="margin-top: 40px;">
        <form method="POST" action="deactivate_user.php" onsubmit="return confirm('このユーザーを一時利用停止にしますか？');" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
            <button type="submit" style="background-color:#e67e22;">一時利用停止</button>
        </form>

        <form method="POST" action="delete_user.php" onsubmit="return confirm('このユーザーを完全に削除してもよろしいですか？（元に戻せません）');" style="display:inline; margin-left: 15px;">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
            <button type="submit" style="background-color:#c0392b;">ユーザー削除</button>
        </form>
    </div>
    <a class="back-link" href="admin_menu.php">管理者メニューへ戻る</a>
</div>

</body>
</html>
