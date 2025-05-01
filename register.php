<?php
session_start(); // 念のためセッションスタート入れとこう

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : 'user';

    $errors = [];
    if (empty($username)) {
        $errors[] = "ユーザー名を入力してください。";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "有効なメールアドレスを入力してください。";
    }
    if (empty($password) || strlen($password) < 4) {
        $errors[] = "パスワードは4文字以上で入力してください。";
    }

    if (empty($errors)) {
        $dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
        $db_user = 'root'; 
        $db_pass = 'root';

        try {
            $pdo = new PDO($dsn, $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $email, $hashed_password, $role]);

            $new_user_id = $pdo->lastInsertId();
            
            // 一般ユーザーのみゲームレートを初期化
            if ($role === 'user') {
                $stmt = $pdo->query('SELECT id FROM games');
                $games = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $stmt = $pdo->prepare('INSERT INTO user_game_rates (user_id, game_id, rate) VALUES (?, ?, 100)');
                foreach ($games as $game_id) {
                    $stmt->execute([$new_user_id, $game_id]);
                }
            }
            header("Location: login.php");
            exit;

        } catch (PDOException $e) {
            $errors[] = "登録中にエラーが発生しました：" . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規登録</title>
    <style>
        body {
            background-color: #f7f7f7;
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 300px; /* ←ちょっと狭くした！ */
        }
        .form-container h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 24px; /* ←見出しも気持ち小さく */
        }
        .form-group {
            margin-bottom: 15px; /* 間隔も少し詰めた */
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
        }
        input, select {
            width: 100%;
            padding: 8px; /* ←少しコンパクトに */
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #fafafa;
            font-size: 14px;
        }
        button {
            width: 100%;
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #2980b9;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
        }
        .login-link a {
            color: #3498db;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red;
            margin-bottom: 20px;
            font-size: 13px;
        }
    </style>

</head>
<body>

<div class="form-container">
    <h1>新規登録</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <?= htmlspecialchars($error) ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label>ユーザー名</label>
            <input type="text" name="username" required maxlength="50" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
        </div>
        <div class="form-group">
            <label>メールアドレス</label>
            <input type="email" name="email" required maxlength="100" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
        </div>
        <div class="form-group">
            <label>パスワード</label>
            <input type="password" name="password" required minlength="4" maxlength="50">
        </div>
        <div class="form-group">
            <label>権限</label>
            <select name="role" required>
                <option value="user" <?= (isset($role) && $role == 'user') ? 'selected' : '' ?>>一般ユーザー</option>
                <option value="admin" <?= (isset($role) && $role == 'admin') ? 'selected' : '' ?>>管理者</option>
            </select>
        </div>
        <button type="submit">登録する</button>
    </form>

    <div class="login-link">
        <p><a href="login.php">ログインはこちら</a></p>
    </div>
</div>

</body>
</html>
