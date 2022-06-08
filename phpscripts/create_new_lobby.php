<?php
//ini_set('display_errors','0');
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
}
$title = $_POST['title'];
$password = $_POST['password'];
$last_change = floor(microtime(true) * 1000);
$flag = true;
$max_players = $_POST['players'];
$max_points = $_POST['points'];
$decks = json_decode($_POST['array']);
if(empty($title) || !is_numeric($max_players) || !is_numeric($max_points) || $max_players < 3 || $max_players > 9 || $max_points < 3){
    echo "0";
    exit();
}
$black_cards = 0;
$white_cards = 0;
require_once "connect_users.php";
$dsn = "mysql:host=".$host.';dbname='.$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
try{
    foreach($decks as $deck){
        $sql = "SELECT * FROM decks WHERE BINARY deck_code = :deck_code";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['deck_code' => $deck]);
        $row = $stmt->fetch();
        $black_cards += $row['black_cards'];
        $white_cards += $row['white_cards'];
    }
    if($black_cards < $max_players * $max_points - $max_players + 1 || $white_cards < $max_players * 12 + 10){
        echo "0";
        exit();
    }
    if(strlen($title)>19){
        echo "0";
        exit();
    }
    $pdo->beginTransaction();
    $counter =0;
	while($flag){
		$counter = $counter + 1;
		$characters = '123456789abcdefghijklmnpqrstuwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < 7; $i++) {
			$index = rand(0, strlen($characters) - 1);
			$randomString .= $characters[$index];
            $hash = password_hash($randomString, PASSWORD_DEFAULT);
		}
		$sql = "SELECT * FROM lobby WHERE lobby_id = '$hash'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		if($stmt->rowCount() == 1) $flag = true;
		
		else $flag = false;
		if($counter == 50000000000){
			break; 
		}
	}
	if(!$flag){
		$user = $_SESSION['user'];
		$sql = "SELECT * FROM players_in_lobby WHERE BINARY nick = '$user'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
        $row = $stmt->fetch();
        $lobby_id = $row['lobby_id'];
		if($stmt->rowCount() > 0){
            $sql = "DELETE FROM players_in_lobby WHERE BINARY nick = '$user'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $sql = "UPDATE lobby SET last_change_players = '$last_change', players_in_lobby = players_in_lobby-1 WHERE lobby_id = '$lobby_id'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();  
		}
        $sql = "INSERT INTO lobby (lobby_id, lobby_password, lobby_points_limit, lobby_title, max_players, owner, game_started, last_change, last_change_players) VALUES('$hash', '$password', $max_points, '$title', $max_players, '$user', false, '$last_change', '$last_change')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $sql = "INSERT INTO players_in_lobby (nick, lobby_id, points, chooser, last_change) VALUES ('$user', '$hash', 0, false, '$last_change')";
        $stmt = $pdo->prepare($sql);
            $stmt->execute();
        $sql = "UPDATE lobby SET last_change_players = '$last_change' WHERE lobby_id = '$hash'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $sql = "UPDATE lobby SET players_in_lobby = players_in_lobby+1 WHERE lobby_id = '$hash'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        foreach($decks as $deck){
            $sql = "SELECT * FROM cards WHERE BINARY deck_code = :deck_code";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['deck_code' => $deck]);
            $row = $stmt->fetchAll();
            foreach($row as $card){
                $color = $card['color'];
                $value = $card['value'];
                $blank_space = $card['blank_space'];
                if($color=="white"){
                    $sql = "INSERT INTO cards_in_lobby (lobby_id, value, color) VALUES('$hash', '$value', '$color')";
                }
                else    $sql = "INSERT INTO cards_in_lobby (lobby_id, value, color, blank_space) VALUES('$hash', '$value', '$color', $blank_space)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }
        }
        echo $hash;
	}
    $pdo->commit();
}
catch(PDOException $e){
    $pdo->rollBack();
    $eror_message = $e->getMessage();
    echo $eror_message;
}