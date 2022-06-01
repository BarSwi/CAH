<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
}
$array = json_decode($_POST['array']);
$id = $_POST['id'];
$number = count($array);
$array_exit= [];
$nick = $_SESSION['user'];
try{
    require_once('../connect_users.php');
    $dsn = "mysql:host=".$host.";dbname=".$db_name;
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->beginTransaction();
    $sql = "SELECT * FROM cards_in_lobby WHERE color = 'black' AND choosen = 1 AND lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();
    if($number != $row['blank_space']){
        echo '0';
        exit();
    }
    $sql = "SELECT * FROM cards_in_lobby WHERE owner = '$nick' AND lobby_id = :id AND color = 'white' AND choosen IS NOT NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    if($stmt->rowCount()!=0){
        echo '0';
        exit();
    }
    $counter = 1;
    foreach($array as $card){
        $sql = "UPDATE cards_in_lobby SET choosen = $counter WHERE ID = :card";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['card'=>$card]);
        $counter += 1;
    }
    $sql = "SELECT * FROM cards_in_lobby WHERE owner IS NULL AND lobby_id = :id AND color = 'white' LIMIT $number";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $rows = $stmt->fetchAll();
    foreach($rows as $row){
        $ID = $row['ID'];
        $value = $row['value'];
        $array_inside = [$ID, $value];
        $sql = "UPDATE cards_in_lobby SET owner = '$nick' WHERE ID = '$ID'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        array_push($array_exit, $array_inside);
    }
    $sql = "SELECT * FROM lobby WHERE lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();
    $players = $row['players_in_lobby'];
    $sql = "SELECT * FROM cards_in_lobby WHERE color = 'white' AND choosen = 1 AND lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    if($stmt->rowCount()==$players-1){
        $sql = "UPDATE  lobby SET round_started = 0 WHERE lobby_id = :id";
        $stmt= $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $sql = "UPDATE  lobby SET reset = 0 WHERE lobby_id = :id";
        $stmt= $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
    }
    $time = floor(microtime(true) * 1000);
    $sql = "UPDATE lobby SET last_change_round = '$time' WHERE lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    echo json_encode($array_exit);
    $pdo->commit();
}
catch(PDOException $e){
    $pdo->rollBack();
    $error_message = $e->getMessage();
    echo $error_message;
}
