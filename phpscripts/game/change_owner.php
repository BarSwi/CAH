<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
} 
require_once('../connect_users.php');
$id = $_POST['id'];
$nick = $_SESSION['user'];
$new_owner = $_POST['new_owner'];
$last_change = floor(microtime(true) * 1000);
$dsn = "mysql:host=".$host.";dbname=".$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
try{
    $sql = "SELECT * from lobby WHERE lobby_id = :id AND owner = '$nick'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    if($stmt->rowCount()==0){
        echo '0';
        exit();
    }
    $sql = "UPDATE lobby SET owner = :new_owner, last_change_players = '$last_change' WHERE lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['new_owner'=>$new_owner, 'id'=>$id]);
}
catch(PDOException $e){
    $error_message = $e->getMessage();
    $pdo->rollBack();
    echo $error_message;
}