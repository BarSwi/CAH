<?php
ini_set('max_execution_time', 4000);
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
}
$nick = $_SESSION['user'];

session_write_close();

if(empty($_POST['time'])){
    $time = floor(microtime(true) * 1000);
}
else $time = $_POST['time'];
$id = $_POST['id'];
$player = $_POST['personal_id'];
$array_exit = [];

try{


    require_once('../connect_users.php');
    $dsn = "mysql:host=".$host.";dbname=".$db_name;
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $sql = "SELECT * FROM users WHERE id = :personal_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['personal_id' => $player]);
    if($stmt->rowCount()==0){
        echo "0";
        exit();
    }
    $row = $stmt->fetch();
    $nick = $row['login'];
    $sql = "SELECT * FROM players_in_lobby WHERE nick = '$nick' AND lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    if($stmt->rowCount()==0){
        $sql = "INSERT INTO players_in_lobby (nick, lobby_id, points, chooser, last_change) VALUES ('$nick', :id, 0, false, :time_change)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=> $id, 'time_change' => $time]);
        $sql = "UPDATE lobby SET last_change_players = '$time' WHERE lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $sql = "UPDATE lobby SET players_in_lobby = players_in_lobby+1 WHERE lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
    }
    $row = $stmt->fetch();
    $lobby = $row['lobby_id'];
    $counter = 0;
    while(true){
        $sql = "SELECT * FROM players_in_lobby WHERE nick = '$nick' AND lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $row = $stmt->fetch();
        if($row['kick']==true){
            $last_change = floor(microtime(true) * 1000);
            $sql = "DELETE FROM players_in_lobby WHERE nick = '$nick' AND lobby_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id'=>$id]);
            $sql = "UPDATE lobby SET last_change_players = '$last_change', players_in_lobby = players_in_lobby-1 WHERE lobby_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id'=>$id]);
            echo "kick";
            exit();
        }
        $counter += 1;
        $sql = "SELECT * FROM lobby WHERE lobby_id =  :id";
        $stmt =  $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        if($stmt->rowCount() == 0){
            echo "0";
            exit();
        }
        $sql = "SELECT * FROM lobby WHERE (last_change > :time_change OR last_change_players > :time_change2) AND lobby_id = :id";
        $stmt =  $pdo->prepare($sql);
        $stmt->execute(['time_change'=>$time,'time_change2'=>$time,'id'=>$id]);
        if($stmt->rowCount()>0){
            $row = $stmt->fetch();
            if($row['last_change_players']>$time){
                $sql = "SELECT * FROM players_in_lobby WHERE lobby_id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id'=>$id]);
                $array = $stmt->fetchAll();
                $sql = "SELECT * FROM lobby WHERE lobby_id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id'=>$id]);
                $row = $stmt->fetch();
                $owner = $row['owner'];
                $time = $row['last_change_players'];
                foreach($array as $player){
                    array_push($array_exit, $player['nick']);
                }
                array_push($array_exit, $time, $owner, "players");
                echo json_encode($array_exit);
                exit();
            }
            if($row['last_change']>$time){

            }
            else{
                echo "0";
                exit();
            }
        }
        if($counter == 3600){
            echo "1";
            exit();
        }
        usleep(600000);
    }

}
catch(PDOException $e){
    $error_message = $e->getMessage();
    echo $error_message;
    
}


