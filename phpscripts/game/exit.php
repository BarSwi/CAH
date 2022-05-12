<?php
session_start();
try{
require_once('../connect_users.php');
$dsn = "mysql:host=".$host.";dbname=".$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$nick = $_SESSION['user'];
$pdo->beginTransaction();
$sql = "SELECT * FROM players_in_lobby WHERE nick = '$nick'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$row = $stmt->fetch();
$lobby_id = $row['lobby_id'];
$sql = "DELETE FROM players_in_lobby WHERE nick = '$nick'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$pdo->commit();
}
catch(PDOException $e){
$pdo->rollBack();
$error_message = $e->getMessage();
echo $error_message;
}