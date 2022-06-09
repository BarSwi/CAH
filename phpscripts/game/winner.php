<?php
session_start();
ignore_user_abort(true);
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "2";
    exit();
}
$card_id = json_decode($_POST['winner']);
$card_id = $card_id[0];
$lobby_id = $_POST['id'];
$nick = $_SESSION['user'];
$time = floor(microtime(true)*1000);
try{
    require_once('../connect_users.php');
    $dsn = "mysql:host=".$host.";dbname=".$db_name;
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->beginTransaction();
    $sql = "SELECT * FROM players_in_lobby WHERE nick = '$nick' AND lobby_id = :id AND chooser = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$lobby_id]);
    if($stmt->rowCount()==0){
        echo '0';
        exit();
    }
    $player = $stmt->fetch();
    $player_id = $player['ID'];
    $sql = "SELECT * FROM cardsShuffled WHERE ID = '$card_id'";
    $stmt=$pdo->prepare($sql);
    $stmt->execute();
    $card = $stmt->fetch();
    if($card['choosen']==NULL){
        echo '1';
        exit();
    }
    $owner = $card['owner'];
    $sql  = "UPDATE players_in_lobby SET points = points + 1 WHERE nick = '$owner'";
    $stmt= $pdo->prepare($sql);
    $stmt->execute();
    $sql  = "UPDATE cardsShuffled SET winner = 1 WHERE ID = '$card_id'";
    $stmt= $pdo->prepare($sql);
    $stmt->execute();
    $sql  = "UPDATE lobby SET last_change_round = '$time' WHERE lobby_id = :id";
    $stmt= $pdo->prepare($sql);
    $stmt->execute(['id'=>$lobby_id]);
    $pdo->commit();
    $sql = "SELECT * FROM lobby WHERE lobby_id =  :id";
    $stmt =  $pdo->prepare($sql);
    $stmt->execute(['id'=>$lobby_id]);
    $row = $stmt->fetch();
    $max_points = $row['lobby_points_limit'];
    sleep(3);
    $sql = "SELECT * FROM players_in_lobby WHERE lobby_id = :id AND points = $max_points";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$lobby_id]);
    if($stmt->rowCount()==1){
        sleep(2);
        $sql = "UPDATE players_in_lobby SET points = 0 WHERE lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$lobby_id]);
        $sql = "SELECT * FROM cards_in_lobby WHERE color = 'black' AND lobby_id = :id ORDER BY RAND()";
        $stmt= $pdo->prepare($sql);
        $stmt->execute(['id'=>$lobby_id]);
        $new_cards = $stmt->fetchAll();
        $sql = "DELETE FROM cards_in_lobby WHERE color = 'black' AND lobby_id = :id";
        $stmt= $pdo->prepare($sql);
        $stmt->execute(['id'=>$lobby_id]);
        foreach($new_cards as $card){
            $color = $card['color'];
            $value = $card['value'];
            $blank_space = $card['blank_space'];
            $sql = "INSERT INTO cards_in_lobby (lobby_id, value, color, blank_space) VALUES('$lobby_id', '$value', '$color', $blank_space)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        $sql  = "UPDATE cards_in_lobby SET choosen = 1 WHERE color = 'black' AND lobby_id = :id ORDER BY RAND() LIMIT 1";
        $stmt= $pdo->prepare($sql);
        $stmt->execute(['id'=>$lobby_id]);
    }
    else{
        $sql = "SELECT * FROM cards_in_lobby WHERE color = 'black' AND lobby_id = :id AND choosen = 1";
        $stmt= $pdo->prepare($sql);
        $stmt->execute(['id'=>$lobby_id]);
        $row = $stmt->fetch();
        $black_card = $row['ID'];
        $sql  = "UPDATE cards_in_lobby SET choosen = 0 WHERE color = 'black' AND lobby_id = :id";
        $stmt= $pdo->prepare($sql);
        $stmt->execute(['id'=>$lobby_id]);
        $sql  = "UPDATE cards_in_lobby SET choosen = 1 WHERE color = 'black' AND lobby_id = :id AND ID > $black_card LIMIT 1";
        $stmt= $pdo->prepare($sql);
        $stmt->execute(['id'=>$lobby_id]);
        if($stmt->rowCount()==0){
            $sql  = "UPDATE cards_in_lobby SET choosen = 1 WHERE color = 'black' AND lobby_id = :id LIMIT 1";
            $stmt= $pdo->prepare($sql);
            $stmt->execute(['id'=>$lobby_id]);
        }
    }
    $time = floor(microtime(true)*1000);
    $sql  = "UPDATE lobby SET reset = 1 WHERE lobby_id = :id";
    $stmt= $pdo->prepare($sql);
    $stmt->execute(['id'=>$lobby_id]);
    $sql = "UPDATE players_in_lobby SET chooser = 0 WHERE lobby_id = :id AND ID = $player_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$lobby_id]);
    $sql = "UPDATE players_in_lobby SET chooser = 1 WHERE lobby_id = :id AND ID > $player_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$lobby_id]);
    if($stmt->rowCount()==0){
        $sql = "UPDATE players_in_lobby SET chooser = 1 WHERE lobby_id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$lobby_id]);
    }
    $sql  = "UPDATE cards_in_lobby SET choosen = NULL, owner = NULL WHERE lobby_id = :id AND choosen IS NOT NULL AND color = 'white'";
    $stmt= $pdo->prepare($sql);
    $stmt->execute(['id'=>$lobby_id]);
    $sql  = "DELETE FROM cardsShuffled WHERE lobby_id = :id";
    $stmt= $pdo->prepare($sql);
    $stmt->execute(['id'=>$lobby_id]);
    $sql  = "UPDATE lobby SET last_change_round = '$time' WHERE lobby_id = :id";
    $stmt= $pdo->prepare($sql);
    $stmt->execute(['id'=>$lobby_id]);
    
}
catch(PDOException $e){
    $pdo->rollBack();
    $error_message = $e->getMessage();
    echo $error_message;
}