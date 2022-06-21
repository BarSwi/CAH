<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
}
$nick = $_SESSION['user'];
require_once('../connect_users.php');
$last_change = floor(microtime(true) * 1000);
$dsn = "mysql:host=".$host.";dbname=".$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
try{
    $sql = "SELECT * FROM players_in_lobby WHERE BINARY nick = '$nick'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
    if($stmt->rowCount()==0){
        echo "0";
        exit();
    }
    $id = $row['lobby_id'];
    $sql = "SELECT * FROM lobby WHERE BINARY lobby_id = '$id'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
    if($row['game_started']==true){
        echo "0";
        exit();
    }
    if($nick != $row['owner']){
        echo "0";
        exit();
    }
    if($stmt->rowCount()==0){
        echo "0";
        exit();
    }
    if($row['players_in_lobby']<3){
        echo "0";
        exit();
    }
    else{
        $pdo->beginTransaction();
        $sql = "UPDATE cards_in_lobby SET choosen = 1 WHERE color = 'black' AND BINARY lobby_id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $sql = "UPDATE players_in_lobby SET chooser = 1 WHERE BINARY lobby_id = :id ORDER BY RAND() LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $sql = "UPDATE lobby SET last_change = '$last_change', game_started = true, round_started = true WHERE BINARY lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $pdo->commit();
    }
}
catch(PDOException $e){
    $pdo->rollBack();
    $error_message = $e->getMessage();
    echo $error_message;

}