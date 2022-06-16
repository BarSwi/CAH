<?php
require_once('connect_users.php');
$dsn = "mysql:host=".$host.";dbname=".$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$id = $_POST['id'];
try{
    $sql = "SELECT * FROM lobby WHERE ID = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $lobby = $stmt->fetch();
    $remove_id = $lobby['lobby_id'];
    if($lobby['players_in_lobby']==0){
        $sql = "DELETE FROM lobby WHERE lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$remove_id]);
        $sql = "DELETE FROM players_in_lobby WHERE lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$remove_id]);
        $sql = "DELETE FROM cards_in_lobby WHERE lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$remove_id]);
        echo '0';
        exit();
    }
    if($stmt->rowCount()==0){
        echo "0";
        exit();
    }
    if(!empty($lobby['lobby_password'])){
        $password = $_POST['password'];
        if($password!=$lobby['lobby_password']){
            echo "1";
            exit();
        }
        else{
            echo $lobby['lobby_id'];
            $_SESSION['lobby_password_ignore'] = $lobby['lobby_id'];
        }
    }
    else{
        echo $lobby['lobby_id'];
    }
}
catch(PDOException $e){
    $error_message = $e->getMessage();
}
