<?php
session_start();
$nick = $_SESSION['user'];
ignore_user_abort(true);
$last_change = floor(microtime(true) * 1000);
ini_set('display_errors','0');
try{
    require_once('../connect_users.php');
    $dsn = "mysql:host=".$host.";dbname=".$db_name;
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->beginTransaction();
    $sql = "SELECT * FROM players_in_lobby WHERE nick = '$nick'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
    $chooser = $row['chooser'];
    $player_id = $row['ID'];
    $lobby_id = $row['lobby_id'];
    $sql = "SELECT * FROM lobby WHERE lobby_id = '$lobby_id'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
    $round_started = $row['round_started'];
    $reset = $row['reset'];
    $game_started = $row['game_started'];
    $owner = $row['owner'];
    $last_change_players = floor(microtime(true) * 1000);;
    $last_change_lobby = $row['last_change'];
    $abs = abs($last_change_players - $last_change_lobby);
    if($abs>2000){
        $sql = "DELETE FROM players_in_lobby WHERE nick = '$nick' AND lobby_id = '$lobby_id'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $sql = "UPDATE lobby SET last_change_players = '$last_change', players_in_lobby = players_in_lobby-1 WHERE lobby_id = '$lobby_id'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();  
        $sql = "UPDATE cards_in_lobby SET owner = NULL, choosen = NULL WHERE lobby_id = :id AND owner = '$nick'";
        $stmt=$pdo->prepare($sql);
        $stmt->execute(['id'=>$lobby_id]);
        $sql  = "DELETE FROM cardsShuffled WHERE lobby_id = :id AND owner = '$nick'";
        $stmt= $pdo->prepare($sql);
        $stmt->execute(['id'=>$lobby_id]);
        $sql = "SELECT * FROM players_in_lobby WHERE nick = '$nick' AND lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $sql = "SELECT * FROM players_in_lobby WHERE lobby_id = '$lobby_id' LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        if($stmt->rowCount()!=0){
            if($nick==$owner){
                if($abs>3000){
                    $new_owner = $row['nick'];
                    $sql = "UPDATE lobby SET owner = '$new_owner' WHERE lobby_id = '$lobby_id'";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                }
            }
        }
        if($game_started==1){
            if($chooser == 1){
                $sql = "UPDATE players_in_lobby SET chooser = 1 WHERE lobby_id = '$lobby_id' AND ID > $player_id LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                if($stmt->rowCount()==0){
                    $sql = "UPDATE players_in_lobby SET chooser = 1 WHERE lobby_id = '$lobby_id' LIMIT 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                }
                $sql = "UPDATE cards_in_lobby SET choosen = NULL, owner = NULL WHERE owner = (SELECT nick FROM players_in_lobby WHERE chooser = 1 AND lobby_id = '$lobby_id') AND choosen IS NOT NULL";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }
            if($round_started == 0 && $reset == 1){
                $sql  = "UPDATE lobby SET round_started = 1 WHERE lobby_id = :id";
                $stmt= $pdo->prepare($sql);
                $stmt->execute(['id'=>$lobby_id]);
            }
        }
        $_SESSION['game']= false;
    }

    $pdo->commit();
}
catch(PDOException $e){
    $pdo->rollBack();
    $error_message = $e->getMessage();
}