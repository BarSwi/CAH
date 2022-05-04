<?php
session_start();
require_once ('connect_users.php');
$nick = $_SESSION['user'];
$color = $_GET['color'];
$text = $_GET['value'];
$deck_code = $_GET['deck_code'];

if($color=="white" && empty($text)){
	echo "0";
	exit();
}
if($color == "black"){
	$count = 	substr_count($text, ' ___');
	if($count == 0 || $count > 3) {
	echo "0";
	exit();
	}
	$count = 	substr_count($text, ' ____');
	if($count != 0) {
	echo "0";
	exit();
	}
}
try{
	$dsn = "mysql:host=".$host.";dbname=".$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "SELECT * FROM decks WHERE BINARY deck_code = '$deck_code' AND BINARY author = '$nick'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$row = $stmt->fetch();
	if($stmt->rowCount()==0){
		header('Location: index.php');	
		exit();
	}
	$pdo->beginTransaction();
	$sql = "INSERT INTO cards (deck_code, value, color) VALUES ('$deck_code', :text, '$color')";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['text' => $text]);
	$sql = "UPDATE decks SET $color"."_cards = $color"."_cards + 1 WHERE BINARY deck_code = '$deck_code'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$pdo->commit();
	
}
catch(PDOException $e){
	$pdo->rollBack();
	$error_message = $e->getMessage();
	echo $error_message;
}
