<?php
// 文字化け防止
header('Content-Type: text/html; charset=UTF-8');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: menu.php');
    exit;
}

$dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
$db_user = 'root';
$db_pass = 'root';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // すべての user 権限または disabled 権限を取得
    $stmt = $pdo->prepare('SELECT id, username, email, role FROM users WHERE role IN ("user", "disabled") ORDER BY id ASC');
    $stmt->execute();
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 既存ゲーム一覧取得
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
    <title>管理者メニュー</title>
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
        .admin-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 900px;
            max-width: 95%;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            color: #333;
        }
        .section {
            margin-bottom: 40px;
            padding: 20px;
            border: 2px solid #3498db;
            border-radius: 8px;
            background-color: #ecf5fc;
        }
        .section h2 {
            margin-top: 0;
            color: #2980b9;
            font-size: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        th {
            background-color: #f0f0f0;
        }
        form div {
            margin-bottom: 15px;
        }
        input[type="text"], input[type="file"] {
            padding: 8px;
            width: 80%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        a.back-link, a.button-link {
            display: inline-block;
            background-color: #3498db;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        a.back-link:hover, a.button-link:hover {
            background-color: #2980b9;
        }

        .disabled-row {
            background-color: #f9f9f9;
            color: #888;
        }

        .disabled-row td:not(.reactivate-cell) {
            opacity: 0.5;
        }
        .reactivate-button {
            background-color: #3498db;
            color: #fff;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .reactivate-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>管理者メニュー</h1>

    <div class="section">
        <h2>大会作成・メンバー募集（現在開発中）</h2>
        <a class="button-link" href="create_tournament.php">大会作成ページへ</a>
    </div>

    <div class="section">
        <h2>プレイヤーのレート管理</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ユーザー名</th>
                    <th>メールアドレス</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($players as $player): ?>
                    <tr class="<?= $player['role'] === 'disabled' ? 'disabled-row' : '' ?>">
                        <td><?= htmlspecialchars($player['id']) ?></td>
                        <td>
                            <a href="player_detail.php?user_id=<?= htmlspecialchars($player['id']) ?>">
                                <?= htmlspecialchars($player['username']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($player['email']) ?></td>
                        <?php if ($player['role'] === 'disabled'): ?>
                            <td class="reactivate-cell">
                                <form method="POST" action="reactivate_user.php" onsubmit="return confirm('このユーザーを再び有効にしますか？');">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($player['id']) ?>">
                                    <button type="submit" class="reactivate-button">再有効化</button>
                                </form>
                            </td>
                        <?php else: ?>
                            <td>-</td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>ボードゲームの編集・追加</h2>
        <form method="POST" action="add_game.php">
            <div>
                <input type="text" name="game_name" placeholder="ゲーム名を入力" required>
            </div>
            <div>
                <input type="text" name="image_path" placeholder="例: /exercise/bgmaster/image/newgame.jpg" required>
            </div>
            <div>
                <button type="submit">ゲームを追加する</button>
            </div>
        </form>

        <h3>既存ゲーム一覧</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ゲーム名</th>
                    <th>画像パス</th>
                    <th>編集</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): ?>
                    <tr>
                        <form method="POST" action="update_game_info.php" style="display: contents;">
                            <input type="hidden" name="game_id" value="<?= htmlspecialchars($game['id']) ?>">
                            <td><?= htmlspecialchars($game['id']) ?></td>
                            <td><input type="text" name="game_name" value="<?= htmlspecialchars($game['name']) ?>" required></td>
                            <td><input type="text" name="image_path" value="<?= htmlspecialchars($game['image_path']) ?>" required></td>
                            <td>
                                <button type="submit" style="margin-right: 10px;">更新</button>
                            </td>
                        </form>
                        <form method="POST" action="delete_game.php" onsubmit="return confirm('本当にこのゲームを削除しますか？');" style="display: inline;">
                            <input type="hidden" name="game_id" value="<?= htmlspecialchars($game['id']) ?>">
                            <td style="padding: 12px;">
                                <button type="submit" style="background-color:#e74c3c; color:white; border:none; padding:6px 12px; border-radius:5px; cursor:pointer;">削除</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <a class="back-link" href="menu.php">メニューに戻る</a>
</div>

</body>
</html>
