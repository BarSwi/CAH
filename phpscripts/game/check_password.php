<?php
try{
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "2";
    exit();
} 
require_once('../connect_users.php');
$password = $_POST['password'];
$id = $_POST['id'];
$dsn = "mysql:host=".$host.";dbname=".$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $sql = "SELECT * FROM lobby WHERE lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();
    if($password == $row['lobby_password']){
        echo '1';
        $_SESSION['lobby_password_ignore'] = $id;
    }
    else{
        echo '0';
    }
}catch(PDOException $e){
    $error_message = $e->getMessage();
    echo $error_message;

}
