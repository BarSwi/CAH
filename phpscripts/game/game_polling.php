<?php
ini_set('max_execution_time', 4000);
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false || $_SESSION['game']==false){
    echo "0";
    exit();
}
$nick = $_SESSION['user'];

session_write_close();


$time = $_POST['time'];

ignore_user_abort(false);
$array_exit = [];
try{


    require_once('../connect_users.php');
    $dsn = "mysql:host=".$host.";dbname=".$db_name;
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $sql = "SELECT * FROM players_in_lobby WHERE nick = '$nick'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
    $lobby = $row['lobby_id'];
    $counter = 0;
    while(true){
        $counter += 1;
        $sql = "SELECT * FROM lobby WHERE lobby_id = '$lobby'";
        $stmt =  $pdo->prepare($sql);
        $stmt->execute();
        if($stmt->rowCount() == 0){
            echo "0";
            exit();
        }

        $sql = "SELECT * FROM lobby WHERE (last_change > '$time' OR last_change_players > '$time') AND lobby_id = '$lobby'";
        $stmt =  $pdo->prepare($sql);
        $stmt->execute();
        if($stmt->rowCount()>0){
            $row = $stmt->fetch();
            $lobby = $row['lobby_id'];
            if($row['last_change_players']>$time){
                $sql = "SELECT * FROM players_in_lobby WHERE lobby_id = '$lobby'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $array = $stmt->fetchAll();
                $time = $row['last_change_players'];
                foreach($array as $player){
                    array_push($array_exit, $player['nick']);
                }
                array_push($array_exit, $time, "players");
                echo json_encode($array_exit);
                exit();
            }
            if($row['last_change']>$time){
                $lobby = $stmt->fetch();
                $sql = "SELECT * FROM lobby WHERE lobby_id = '$lobby'";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $array = $stmt->fetchAll();
                foreach($array as $player){
                    array_push($array_exit, $player['nick']);
                }
                $time = $lobby['last_change_players'];
                array_push($array_exit, $time, "players");
                echo json_encode($array_exit);
                exit();
            }
        }
        if($counter == 3600){
            echo "1";
            exit();
        }
        sleep(1);
    }

}
catch(PDOException $e){
    $error_message = $e->getMessage();
    
}


