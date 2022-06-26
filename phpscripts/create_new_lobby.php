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
$afk_time = $_POST['afk_time'];
if(empty($title) || !is_numeric($max_players) || !is_numeric($max_points) || !is_numeric($afk_time) || $max_players < 3 || $max_players > 9 || $max_points < 3 || $afk_time > 999 || $afk_time < 5){
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
    // 1 query instead of loop
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
    $sql = "SELECT lobby_id FROM lobby";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $lobbies = $stmt->fetchAll();
    $counter =0;
	while($flag){
		$counter = $counter + 1;
		$characters = '123456789abcdefghijklmnpqrstuwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < 7; $i++) {
			$index = rand(0, strlen($characters) - 1);
			$randomString .= $characters[$index];
		}
        if(empty($lobbies)){
            $flag = false;
        }
        foreach($lobbies as $lobby){
            if($randomString === $lobby) $flag = true;
            else $flag = false;
        }
		if($counter == 500000000){
			break; 
		}
	}
	if(!$flag){
		$user = $_SESSION['user'];
        $sql = "INSERT INTO lobby (lobby_id, lobby_password, lobby_points_limit, lobby_afk_time, lobby_title, max_players, owner, game_started, last_change, last_change_players) VALUES('$randomString', '$password', $max_points, $afk_time, '$title', $max_players, '$user', false, '$last_change', '$last_change')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $sql = "INSERT INTO players_in_lobby (nick, lobby_id, points, chooser, last_change) VALUES ('$user', '$randomString', 0, false, '$last_change')";
        $stmt = $pdo->prepare($sql);
            $stmt->execute();
        $sql = "UPDATE lobby SET last_change_players = '$last_change', players_in_lobby = players_in_lobby+1 WHERE lobby_id = '$randomString'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $in = join(',', array_fill(0, count($decks), '?'));
        $sql = "INSERT INTO cards_in_lobby (lobby_id, value, color, blank_space) SELECT '$randomString', value, color, blank_space FROM cards WHERE BINARY deck_code IN ($in) ORDER BY RAND()";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($decks);
        echo $randomString;
        $_SESSION['lobby_password_ignore'] = $randomString;
	}   
    $pdo->commit();
}
catch(PDOException $e){
    $pdo->rollBack();
    $eror_message = $e->getMessage();
    echo $eror_message;
}