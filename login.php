<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    $errors = [];

    if (empty($email) || empty($password)) {
        $errors[] = "メールアドレスとパスワードを入力してください。";
    }

    if (empty($errors)) {
        $dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
        $db_user = 'root';
        $db_pass = 'root';

        try {
            $pdo = new PDO($dsn, $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header("Location: menu.php");
                exit;
            } else {
                $errors[] = "メールアドレスまたはパスワードが正しくありません。";
            }
        } catch (PDOException $e) {
            $errors[] = "ログイン中にエラーが発生しました：" . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
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
            width: 300px;
        }
        .form-container h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 24px;
        }
        .error-message {
            color: red;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 8px;
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
        .register-link {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
        }
        .register-link a {
            color: #3498db;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h1>ログイン</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <?= htmlspecialchars($error) ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label>メールアドレス</label>
            <input type="email" name="email" required maxlength="100">
        </div>

        <div class="form-group">
            <label>パスワード</label>
            <input type="password" name="password" required minlength="4" maxlength="50">
        </div>

        <button type="submit">ログインする</button>
    </form>

    <div class="register-link">
        <a href="register.php">新規登録はこちら</a>
    </div>
</div>

</body>
</html>
