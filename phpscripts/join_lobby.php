<?php
require_once('connect_users.php');
$dsn = "mysql:host=".$host.";dbname=".$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$id = $_POST['id'];
try{
    $pdo->beginTransaction();
    $sql = "SELECT * FROM lobby WHERE ID = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    if($stmt->rowCount()==0){
        echo "0";
        exit();
    }
    $lobby = $stmt->fetch();
    if(!empty($lobby['lobby_password'])){
        $password = $_POST['password'];
        if($password!=$lobby['lobby_password']){
            echo "1";
            exit();
        }
        else{
            echo $lobby['lobby_id'];
        }
    }
    else{
        echo $lobby['lobby_id'];
    }
    $pdo->commit();
}
catch(PDOException $e){
    $pdo->rollBack();
    $error_message = $e->getMessage();
}
