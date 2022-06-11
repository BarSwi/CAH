<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
}
require_once('connect_users.php');
$dsn = "mysql:host=".$host.";dbname=".$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
try{
    $last_change = floor(microtime(true) * 1000);
	$delete = $last_change - 3600000;
    $pdo->beginTransaction();
    $sql = "DELETE cards_in_lobby, players_in_lobby FROM cards_in_lobby LEFT JOIN players_in_lobby ON cards_in_lobby.lobby_id = players_in_lobby.lobby_id WHERE cards_in_lobby.lobby_id IN (SELECT lobby_id FROM lobby WHERE (last_change < $delete AND (last_change_round < $delete OR last_change_round IS NULL)) OR players_in_lobby < 1)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $sql = "DELETE FROM lobby WHERE (last_change < $delete AND (last_change_round < $delete OR last_change_round IS NULL)) OR players_in_lobby < 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $sql = "SELECT * FROM lobby";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $lobbies = $stmt->fetchAll();
    echo json_encode($lobbies);
    $pdo->commit();
}
catch(PDOException $e){
    $pdo->rollBack();
    $error_message = $e->getMessage();
}
    
