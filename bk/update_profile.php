<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

mb_internal_encoding("UTF-8"); // 文字コードをUTF-8に明示
$user_id = $_SESSION['user_id'];

$dsn = 'mysql:host=localhost;dbname=bgmaster;charset=utf8mb4';
$db_user = 'root';
$db_pass = 'root';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");

    // プロフィールコメント（日本語対応済み）
    $comment = isset($_POST['profile_comment']) ? trim($_POST['profile_comment']) : '';
    $comment = mb_convert_encoding($comment, 'UTF-8', 'auto');

    // 画像アップロード処理
    $image_path = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['profile_image']['tmp_name'];
        $filename = uniqid('profile_') . '_' . basename($_FILES['profile_image']['name']);
        $destination = 'uploads/' . $filename;

        if (move_uploaded_file($tmp_name, $destination)) {
            $image_path = $destination;
        }
    }

    // SQL組み立て
    $sql = "UPDATE users SET profile_comment = :comment";
    if ($image_path) {
        $sql .= ", profile_image_path = :image";
    }
    $sql .= " WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
    if ($image_path) {
        $stmt->bindValue(':image', $image_path, PDO::PARAM_STR);
    }
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: mypage.php");
    exit;

} catch (PDOException $e) {
    die("エラー: " . htmlspecialchars($e->getMessage()));
}
