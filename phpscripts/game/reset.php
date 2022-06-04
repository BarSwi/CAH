<?php
session_start();
ignore_user_abort(true);
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
}
$id = $_POST['id'];
try{
    require_once('../connect_users.php');
    $dsn = "mysql:host=".$host.";dbname=".$db_name;
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $sql  = "UPDATE lobby SET round_started = 1 WHERE lobby_id = :id";
    $stmt= $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    
}
catch(PDOException $e){
    $error_message = $e->getMessage();
    echo $error_message;
}