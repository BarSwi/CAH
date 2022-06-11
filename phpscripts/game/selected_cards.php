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
    $sql = "SELECT * FROM players_in_lobby WHERE lobby_id = :id AND chooser = 1 AND nick = '$nick'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    if($stmt->rowCount()!=0){
        echo '2';
        exit();
    }
    if($stmt)
    $sql = "SELECT * FROM lobby WHERE lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();
    $round_started = $row['round_started'];
    $reset = $row['reset']; 
    if($round_started == 0 ){
        if($reset==1){
            $sql  = "UPDATE lobby SET round_started = 1 WHERE lobby_id = :id";
            $stmt= $pdo->prepare($sql);
            $stmt->execute(['id'=>$id]);
        }
        else{
            echo '1';
            exit();
        }
    }
    $limit = $row['players_in_lobby'];
    if($limit < 3){
        $time = floor(microtime(true)*1000);
        $sql = "UPDATE lobby SET last_change = '$time', game_started = 0, reset = 0, round_started = 0 WHERE lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $sql = "UPDATE players_in_lobby SET chooser = 0, points = 0 WHERE lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $sql = "UPDATE cards_in_lobby SET choosen = NULL, owner = NULL WHERE lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
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
    // Change into 1 query instead of loop
    foreach($array as $card){
        $sql = "UPDATE cards_in_lobby SET choosen = $counter WHERE ID = :card AND owner = '$nick'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['card'=>$card]);
        $counter += 1;
    }
    $pdo->beginTransaction();
    $sql = "SELECT * FROM cards_in_lobby WHERE color = 'black' AND choosen = 1 AND lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();
    if($number != $row['blank_space']){
        echo '0';
        exit();
    }
    $sql = "UPDATE cards_in_lobby SET owner = '$nick' WHERE owner is NULL AND lobby_id = :id AND color = 'white' ORDER BY RAND() LIMIT $number";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);

    $sql = "SELECT * FROM lobby WHERE lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();
    $players = $row['players_in_lobby'];
    $sql = "SELECT * FROM cards_in_lobby WHERE color = 'white' AND choosen = 1 AND lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    if($stmt->rowCount()==$players-1){
        $sql = "INSERT INTO cardsShuffled (value, owner, choosen, lobby_id) SELECT value, owner, choosen, lobby_id FROM cards_in_lobby WHERE lobby_id=:id AND choosen IS NOT NULL AND color = 'white' ORDER BY RAND()";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $sql = "UPDATE  lobby SET round_started = 0, reset = 0 WHERE lobby_id = :id";
        $stmt= $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
    }
    $time = floor(microtime(true)*1000);
    $sql = "UPDATE lobby SET last_change_round = '$time' WHERE lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $sql = "SELECT * FROM cards_in_lobby WHERE owner = '$nick' AND lobby_id = :id AND choosen IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    $my_cards = $stmt->fetchAll();
    if($stmt->rowCount()!=10){
        // Prevention
        $sql = "UPDATE cards_in_lobby SET owner = '$nick' WHERE owner is NULL AND lobby_id = :id AND color = 'white' ORDER BY RAND() LIMIT $number";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $sql = "SELECT * FROM cards_in_lobby WHERE owner = '$nick' AND lobby_id = :id AND choosen IS NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $my_cards = $stmt->fetchAll();
    }
    foreach($my_cards as $card){
        $ID = $card['ID'];
        $value = $card['value'];
        $array_inside = [$ID, $value];
        array_push($array_exit, $array_inside);
    }
    echo json_encode($array_exit);
    $pdo->commit();
}
catch(PDOException $e){
    $pdo->rollBack();
    $error_message = $e->getMessage();
    echo $error_message;
}
