<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
} 
require_once('../connect_users.php');
$id = $_POST['id'];
$nick = $_SESSION['user'];
$kick = $_POST['kick'];
$last_change = floor(microtime(true) * 1000);
$dsn = "mysql:host=".$host.";dbname=".$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
try{
    $pdo->beginTransaction();
    $sql = "SELECT * FROM lobby WHERE lobby_id = :id AND owner = '$nick'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    if($stmt->rowCount()==0){
        echo "0";
        exit();
    }
    $sql = "DELETE FROM players_in_lobby WHERE nick = :kick AND lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['kick'=>$kick, 'id'=>$id]);
    $sql = "UPDATE cards_in_lobby SET owner = NULL, choosen = NULL, winner = NULL WHERE lobby_id = :id AND owner = '$kick'";
    $stmt=$pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $sql = "UPDATE lobby SET last_change_players = '$last_change' WHERE lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $sql = "UPDATE lobby SET players_in_lobby = players_in_lobby-1 WHERE lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $pdo->commit();
}
catch(PDOException $e){
    $pdo->rollBack();
    $error_message = $e->getMessage();
    echo $error_message;

}
