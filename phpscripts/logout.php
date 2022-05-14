<?php
ini_set('display_errors','0');
session_start();
try{
    require_once('connect_users.php');
    $dsn = "mysql:host=".$host.";dbname=".$db_name;
    $pdo = new PDO($dsn, $db_user, $db_password);
    $last_change = floor(microtime(true) * 1000);
    $nick = $_SESSION['user'];
    $pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $sql = "DELETE FROM players_in_lobby WHERE nick = '$nick'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $sql = "UPDATE lobby SET last_change_players = '$last_change' WHERE lobby_id = '$lobby_id'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
}
catch(PDOException $e){
    $error_message = $e->getMessage();

}

unset($_SESSION['login']);
unset($_SESSION['user']);
