<?php
session_start();
$cards = json_decode($_POST['cards']);
require_once ('connect_users.php');
$deck_code = $_POST['deck_code'];
$nick = $_SESSION['user'];
try{
	$dsn = "mysql:host=".$host.";dbname=".$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "SELECT * FROM decks WHERE BINARY deck_code = '$deck_code' AND BINARY author = '$nick'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$row = $stmt->fetch();
	if($stmt->rowCount()==0){
		header('Location: ../index.php');	
		exit();
	}
	for($x=0;$x<count($cards);$x++){
		$cards[$x]=get_object_vars($cards[$x]);
	}
	for($x=0;$x<count($cards);$x++){
		$color = $cards[$x]['color'];
		$value = $cards[$x]['value'];
		$pdo->beginTransaction();
		$sql = "DELETE FROM cards WHERE value = '$value' AND color = '$color' LIMIT 1";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$sql = "UPDATE decks SET ".$color."_cards = ".$color."_cards - 1 WHERE BINARY deck_code = '$deck_code'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$pdo->commit();
	}
}
catch(PDOException $e){
	$pdo->rollBack();
	$message = $e->getMessage();
	echo $message;
}
	