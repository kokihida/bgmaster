<?php
session_start();

// セッションをすべて削除
$_SESSION = []; // セッション変数を空にする
session_destroy(); // セッションファイルを削除する

// ログアウト完了ページにリダイレクト
header('Location: logout_complete.php');
exit;
