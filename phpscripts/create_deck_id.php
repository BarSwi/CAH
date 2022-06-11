<?php
ini_set('display_errors','0');
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false) exit();
$flag = true;
$title = $_POST['title'];
$counter = 0;
require_once "connect_users.php";
try{
	$dsn = "mysql:host=".$host.";dbname=".$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$user = $_SESSION['user'];
	$sql = "SELECT * FROM decks WHERE BINARY author = '$user'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	if($stmt->rowCount() == 3){
		echo "0";
		exit();
	}
	$sql = "SELECT deck_code FROM decks";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$active_decks = $stmt->fetchAll();
	while($flag){
		$counter = $counter + 1;
		$characters = '123456789abcdefghijklmnpqrstuwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < 7; $i++) {
			$index = rand(0, strlen($characters) - 1);
			$randomString .= $characters[$index];
		}
		foreach($active_decks as $deck){
			if($randomString == $deck) $flag = true;
			else $flag = false;
		}
		if($counter == 50000000000){
			break; 
		}
	}
	if(!$flag){
		$sql = "INSERT INTO decks(deck_code, deck_title, white_cards, black_cards, create_date, author) VALUES('$randomString', '$title', 0, 0, now(), '$user')";
		$stmt = $pdo->prepare($sql);
		echo $randomString;
		$stmt->execute();
	}
}
catch(PDOException $e){
	$error = $e->getMessage();
	echo $error;
	
}