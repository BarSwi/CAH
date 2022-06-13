<?php
session_start();
//ini_set('display_errors','0');
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
}
$nick = $_SESSION['user'];
require_once('../connect_users.php');
$dsn = "mysql:host=".$host.";dbname=".$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$sql = "SELECT * FROM players_in_lobby WHERE nick = '$nick' AND reroll = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
if($stmt->rowCount()==0){
    echo '0';
    exit();
}
try{
    $pdo->beginTransaction();
    $sql = "UPDATE cards_in_lobby SET owner = NULL WHERE owner = '$nick' AND choosen IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $sql = "UPDATE cards_in_lobby SET owner = '$nick' WHERE color = 'white' AND owner IS NULL ORDER BY RAND() LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $sql = "UPDATE players_in_lobby SET reroll = 0 WHERE nick = '$nick'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $sql = "SELECT * FROM cards_in_lobby WHERE owner = '$nick' AND choosen IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $cards = $stmt->fetchAll();
    $array_exit = [];
    foreach($cards as $card){
        array_push($array_exit, [$card['ID'], $card['value']]);
    }
    $pdo->commit();
    echo json_encode($array_exit);
}
catch(PDOException $e){
    $pdo->rollBack();
    $error_message = $e->getMessage();
    echo $error_message;
}
