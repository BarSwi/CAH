<?php
	usleep(10000);
	include "languages/config.php";	
    if(!isset($_SESSION['login']) || $_SESSION['login']==false){
		header('Location: index.php');
		exit();
	} 
	require_once('phpscripts/connect_users.php');
	$lobby_id = $_GET['id'];
	
	$last_change = floor(microtime(true) * 1000);
	$delete = $last_change - 3600000;
	$user = $_SESSION['user'];
	$dsn = "mysql:host=".$host.";dbname=".$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	try{
		$pdo->beginTransaction();
		$sql = "SELECT * FROM lobby WHERE lobby_id = '$lobby_id'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$lobby_before = $stmt->fetch();
		if($lobby_before['players_in_lobby'] + 1 > $lobby_before['max_players']){
			header('Location: index.php');
			exit();
		}
		if($stmt->rowCount() == 0){
			header('Location: index.php');
			exit();
		}
		if($lobby_before['last_change'] < $delete){
			$sql = "DELETE FROM lobby WHERE lobby_id = '$lobby_id'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$sql = "DELETE FROM players_in_lobby WHERE lobby_id = '$lobby_id'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$sql = "DELETE FROM cards_in_lobby WHERE lobby_id = '$lobby_id'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
		}
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
			$sql = "UPDATE lobby SET players_in_lobby = players_in_lobby+1 WHERE lobby_id = '$lobby_id'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
		}
		else{
			$sql = "SELECT * FROM players_in_lobby WHERE nick = '$user'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$lobbies = $stmt->fetchAll();
			$sql = "DELETE FROM players_in_lobby WHERE nick = '$user' AND lobby_id = '$lobby_id'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			foreach($lobbies as $lobby_del){
				$lobby_del_var = $lobby_del['lobby_id'];
				$sql = "UPDATE lobby SET players_in_lobby = players_in_lobby-1 WHERE lobby_id = '$lobby_del_var'";
				$stmt = $pdo->prepare($sql);
				$stmt->execute();
				$sql = "UPDATE lobby SET last_change_players = '$last_change' WHERE lobby_id = '$lobby_del_var'";
				$stmt = $pdo->prepare($sql);
				$stmt->execute();
			}
			$sql = "INSERT INTO players_in_lobby (nick, lobby_id, points, chooser, last_change) VALUES ('$user', '$lobby_id', 0, false, '$last_change')";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$sql = "UPDATE lobby SET last_change_players = '$last_change' WHERE lobby_id = '$lobby_id'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$sql = "UPDATE lobby SET players_in_lobby = players_in_lobby+1 WHERE lobby_id = '$lobby_id'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
		}
		$_SESSION['game']= true;
		$sql = "SELECT * FROM players_in_lobby WHERE lobby_id = '$lobby_id'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$players = $stmt->fetchAll();
		$sql = "SELECT * FROM lobby WHERE lobby_id = '$lobby_id'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$lobby = $stmt->fetch();
		if($stmt->rowCount() == 0){
			header('Location: index.php');
			exit();
		}
		$pdo->commit();
	}
	catch(PDOException $e){
		$pdo->rollBack();
		$error_message = $e->getMessage();


	}
	
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset = "utf-8"/>
	<title>GoCards</title>
	<meta name = "description" content =<?= $lang['side_description'] ?> />
	<meta http-equiv="X-UA-Compatible" content = "IE=edge,chrome=1"/> 
	<link href="css/game.css" type="text/css" rel="stylesheet" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href = "fontello/icons/css/fontello.css" type ="text/css" rel = "stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;900&display=swap" rel="stylesheet">
	<script src="js/jquery-3.6.0.min.js"></script>
	<script src = "js/FormSubmitLang.js"></script> 
</head>
<body>
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
		if($lobby['players_in_lobby']>=3) $class = 'class = "active"';
		else $class = 'class = "inactive"';
		echo '<div id = "start"'.$class.'>START</div><div id = "information">'.$lang['lobby_message'].'</div>';
	}

	echo '</div>
	<div id ="bottom">
	<div id = "players_vis">
		<span id = "players_in_lobby">
			'.$lobby['players_in_lobby'].'
		</span>
		/
		<span id = "max_players">
			'.$lobby['max_players'].'
		</span>
		<i class = "icon-adult"></i>
	</div>
	<h2><span id = "players_in_lobby"></span>'.$lang['players'].'</h2>
	<div id = "players" class = "players_not_started">';
		 foreach($players as $player){
			if($lobby_before['owner']==$player['nick']) {
				$class = 'class = "player_before owner player"';
				$icon = '<i class ="icon-crown"></i>';
			}
			else
		 	{
				$class = 'class ="player_before player"';
				$icon = '';
			} 
			echo
			'<div '.$class.'><span class = "nick">'.$player['nick'].$icon.'</span></div>';
		}
	'</div>';
	?>
	<script src = "js/game.js"></script>
</body>