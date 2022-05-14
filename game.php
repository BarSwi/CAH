<?php
	include "languages/config.php";	
    if(!isset($_SESSION['login']) || $_SESSION['login']==false){
		header('Location: index.php');
		exit();
	} 
	require_once('phpscripts/connect_users.php');
	$lobby_id = $_GET['id'];
	$last_change = floor(microtime(true) * 1000);
	$user = $_SESSION['user'];
	$dsn = "mysql:host=".$host.";dbname=".$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$sql = "SELECT * FROM lobby WHERE lobby_id = '$lobby_id'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	if($stmt->rowCount() == 0){
		header('Location: index.php');
		exit();
	}
	$lobby = $stmt->fetch();
	$sql = "SELECT * FROM players_in_lobby WHERE nick = '$user'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	if($stmt->rowCount()==0){
		$sql = "INSERT INTO players_in_lobby (nick, lobby_id, points, chooser, last_change) VALUES ('$user', '$lobby_id', 0, false, '$last_change')";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$sql = "UPDATE lobby SET last_change_players = '$last_change' WHERE lobby_id = '$lobby_id'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
	}
	else{
		$sql = "DELETE FROM players_in_lobby WHERE nick = '$user'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$sql = "INSERT INTO players_in_lobby (nick, lobby_id, points, chooser, last_change) VALUES ('$user', '$lobby_id', 0, false, '$last_change')";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$sql = "UPDATE lobby SET last_change_players = '$last_change' WHERE lobby_id = '$lobby_id'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
	}
	$sql = "SELECT * FROM players_in_lobby WHERE lobby_id = '$lobby_id'";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$players = $stmt->fetchAll();
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset = "utf-8"/>
	<title>GoCards</title>
	<meta name = "description" content =<?= $lang['side_description'] ?> />
	<meta name = "keywords" content = "Karty, gra karciana, multiplayer, zabawne, do gry ze znajomymi, na wolny wieczór" />
	<meta http-equiv="X-UA-Compatible" content = "IE=edge,chrome=1"/> 
	<link href="css/game.css" type="text/css" rel="stylesheet" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href = "fontello/fontello-170c85d4/css/fontello.css" type ="text/css" rel = "stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;900&display=swap" rel="stylesheet">
	<script src="js/jquery-3.6.0.min.js"></script>
	<script src = "js/FormSubmitLang.js"></script> 
</head>
<body>
	<script src = "js/game.js"></script>
	<div id = "lang">
				
		<label class = "lang_change"><img src ="img/plflag"><input type = "submit" name = "hl" value ="pl" class = "hl" ></label>
	
		<label class = "lang_change"> <img src = "img/enflag"> <input type = "submit" name = "hl" value ="en" class = "hl" ></label>
		
	</div>
	<?php echo'
	<div id ="top">
		<h1>'.$lobby['lobby_title'].'</h1>
	</div>
	<div id = "middle" class= "middle_not_started">';
	if($user != $lobby['owner']){
		echo $lang['game_not_started'];
	}
	else{
		echo '<div id = "start">START</div><div id = "information">'.$lang['lobby_message'].'</div>';
	}

	echo '</div>
	<div id ="bottom">
	<h2>'.$lang['players'].'</h2>
	<div id = "players" class = "players_not_started">';
		 foreach($players as $player){
			if($lobby['owner']==$player['nick']) $class = 'class = "owner"';
			else $class = 'class ="player_before"';
			echo
			'<div '.$class.'>
				'.$player['nick'].'
			<div class = "kick">test</div></div>';
		}
	'</div>';
	?>
</body>