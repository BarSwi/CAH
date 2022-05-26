<?php
session_start();
require_once('../connect_users.php');
$nick = $_SESSION['user'];
$id = $_POST['id'];
try{
    $dsn = "mysql:host=".$host.";dbname=".$db_name;
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $sql = "SELECT * FROM lobby WHERE lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=> $id]);
    $lobby = $stmt->fetch();
    $owner = $lobby['owner'];
    $sql = "SELECT * FROM users WHERE login = '$nick'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
    $array_exit = [];
    array_push($array_exit, $row['id'], $row['login'], $owner, $_SESSION['lang']);
    if($lobby['game_started']==1){
        $sql = "SELECT * FROM cards_in_lobby WHERE lobby_id = :id AND color = 'black' AND choosen = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        if($stmt->rowCount()!=0){
            $row = $stmt->fetch();
            array_push($array_exit, $row['blank_space']);
        }
        $sql = "SELECT * FROM players_in_lobby WHERE lobby_id = :id AND chooser = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $row = $stmt->fetch();
        array_push($array_exit, $row['nick']);
    }
    echo json_encode($array_exit);
}
catch(PDOException $e){
    $error_message = $e->getMessage();
}