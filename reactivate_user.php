<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
if ($user_id > 0) {
    $pdo = new PDO('mysql:host=localhost;dbname=bgmaster;charset=utf8mb4', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('UPDATE users SET role = "user" WHERE id = ?');
    $stmt->execute([$user_id]);
}

header('Location: admin_menu.php');
exit;
