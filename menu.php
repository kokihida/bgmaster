<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'ゲスト';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>メニュー</title>
    <style>
    body {
        background-color: #f7f7f7;
        font-family: "Segoe UI", "Helvetica Neue", sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .menu-container {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        width: 350px;
        text-align: center;
    }

    h1 {
        margin-bottom: 20px;
        font-size: 24px;
        color: #333;
    }

    h2 {
        margin-bottom: 20px;
        font-size: 20px;
        color: #666;
    }

    /* 吹き出しポップアップ */
    .tooltip-wrapper {
        position: relative;
        display: inline-block;
    }

    .tooltip-bubble {
        position: absolute;
        left: 100%;
        top: 10%;
        transform: translateY(-50%);
        background-color: #e74c3c;
        color: white;
        padding: 20px 30px;           /* ← 大きめに */
        border-radius: 20px;          /* ← 丸く */
        font-size: 18px;              /* ← しっかり文字 */
        font-weight: bold;
        white-space: nowrap;
        z-index: 10;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease-in-out;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    }

    .tooltip-wrapper:hover .tooltip-bubble {
        opacity: 1;
    }

    .tooltip-bubble::before {
        content: "";
        position: absolute;
        left: -20px;
        top: 50%;
        transform: translateY(-50%);
        border: 20px solid transparent;
        border-right-color: #e74c3c;
    }    
    .admin-link {
        margin-bottom: 20px;
        display: inline-block;
        background-color: #e67e22;
        padding: 8px 15px;
        border-radius: 5px;
        color: white;
        font-size: 13px;
        text-decoration: none;
    }

    .admin-link:hover {
        background-color: #d35400;
    }
    
    .menu-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column; /* ← これが最重要 */
    align-items: center;     /* 中央揃え */
    gap: 15px;               /* ボタン間隔 */
}

.menu-list li {
    width: 100%;
    text-align: center;
}

.menu-list a {
    display: flex;                     /* アイコンと文字を横並びに */
    align-items: center;
    justify-content: center;
    gap: 10px;                         /* アイコンと文字の間隔 */
    width: 90%;
    max-width: 300px;
    margin: 15px auto;
    background-color: #3498db;
    color: #fff;
    padding: 16px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 18px;
    font-weight: bold;
    transition: background-color 0.3s;
}

.menu-list a:hover {
    background-color: #2980b9;
}
</style>
</head>
<body>

<div class="menu-container">
    <h1>ようこそ、<?= htmlspecialchars($username) ?>さん！</h1>

    <?php if ($role === 'admin'): ?>
        <a class="admin-link" href="admin_menu.php">⚡ 管理者メニューへ</a>
    <?php endif; ?>

    <h2>メニュー</h2>
    <ul class="menu-list">
        <li class="tooltip-wrapper">
            <a href="ranking.php">
                ランキングを見る 👑
            </a>
            <span class="tooltip-bubble">他のプレイヤーの強さがわかるランキング一覧</span>
        </li>
        <li class="tooltip-wrapper">
            <a href="mypage.php">
                マイページ 👤
            </a>
            <span class="tooltip-bubble">自分のレートやプロフィールを確認・編集</span>
        </li>
        <li>
            <a href="logout.php">ログアウト 🚪</a>
        </li>
    </ul>
</div>

</body>
</html>
