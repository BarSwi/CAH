<?php
//ini_set('display_errors','0');
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
}
$cards = json_decode($_POST['cards']);
require_once ('connect_users.php');
$deck_code = $_POST['deck_code'];
$nick = $_SESSION['user'];
try{
	$dsn = "mysql:host=".$host.";dbname=".$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$sql = "SELECT * FROM decks WHERE BINARY deck_code = :deck_code AND BINARY author = '$nick'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['deck_code'=>$deck_code]);
	if($stmt->rowCount()==0){
		echo '0';
		exit();
	}
	$in = join(',', array_fill(0, count($cards), '?'));
	$sql = "SELECT * FROM cards WHERE ID IN ($in)";
	$stmt = $pdo->prepare($sql);
	$stmt->execute($cards);
	$colors = $stmt->fetchAll();
	$white = 0;
	$black = 0;
	foreach($colors as $color){
		if($color['color'] == 'white') $white += 1;
		else $black += 1;
	}
	$sql = "DELETE FROM cards WHERE ID IN ($in)";
	$stmt = $pdo->prepare($sql);
	$stmt->execute($cards);
	$sql = "UPDATE decks SET white_cards = white_cards - $white, black_cards = black_cards - $black WHERE BINARY deck_code = :deck_code";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['deck_code'=>$deck_code]);
}
catch(PDOException $e){
	$message = $e->getMessage();
	echo $message;
}
	