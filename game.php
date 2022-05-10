<?php
	include "languages/config.php";	
    if(!isset($_SESSION['login']) || $_SESSION['login']==false) header('Location: index.php');
	require_once('phpscripts/connect_users.php');
	$nick = $_SESSION['user'];
	$dsn = "mysql:host=".$host.";dbname=".$db_name;
	$pdo = new PDO($dsn,$db_user,$db_password);
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	try{
		$sql = "SELECT * FROM decks WHERE BINARY author = '$nick'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$decks = $stmt->fetchAll();
	}
	catch(PDOException $e){
		$message = $e->getMessage();
		echo $message;
	}
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset = "utf-8"/>
	<title>GoCards</title>
	<meta name = "description" content =<?= $lang['side_description'] ?> />
	<meta name = "keywords" content = "Karty, gra karciana, multiplayer, zabawne, do gry ze znajomymi, na wolny wieczór" />
	<meta http-equiv="X-UA-Compatible" content = "IE=edge,chrome=1"/> 
	<link href="css/create_lobby.css" type="text/css" rel="stylesheet" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href = "fontello/fontello-170c85d4/css/fontello.css" type ="text/css" rel = "stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;900&display=swap" rel="stylesheet">
	<script src="js/jquery-3.6.0.min.js"></script>
	<script src = "js/FormSubmitLang.js"></script> 
</head>
<body>
<button id ="test">Test</button>
</body>