<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
} 
$id = $_POST['id'];
$nick = $_SESSION['user'];
$kick = $_POST['kick'];
$last_change = floor(microtime(true) * 1000);
require_once('../connect_users.php');
try{
    $dsn = "mysql:host=".$host.";dbname=".$db_name;
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $sql = "SELECT * FROM lobby WHERE lobby_id = :id AND owner = '$nick'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    if($stmt->rowCount()==0){
        echo "0";
        exit();
    }
    $sql = "UPDATE players_in_lobby SET kick = true WHERE nick = :kick AND lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['kick'=>$kick, 'id'=>$id]);
}
catch(PDOException $e){
    $pdo->rollBack();
    $error_message = $e->getMessage();
    echo $error_message;

}
