<?php
ini_set('display_errors','0');
session_start();
require_once('connect_users.php');
$deck_code = $_POST['deck_code'];
$nick = $_SESSION['user'];
try{
	$dsn = "mysql:host=".$host.";dbname=".$db_name;
	$pdo = new PDO($dsn,$db_user, $db_password);
	$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "SELECT * FROM decks WHERE BINARY deck_code = :deck_code AND BINARY author = '$nick'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['deck_code'=>$deck_code]);
	if($stmt->rowCount()==0)
	{		
		echo "0";
		exit();
	}
	$pdo->beginTransaction();
	$sql = "DELETE FROM decks WHERE BINARY deck_code = :deck_code AND BINARY author = '$nick'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['deck_code'=>$deck_code]);
	$sql = "DELETE FROM cards WHERE BINARY deck_code = :deck_code";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['deck_code'=>$deck_code]);
	$pdo->commit();
}
catch(PDOException $e){
	$pdo->rollBack();
	$message = $e->getMessage();
	echo $message;	
}