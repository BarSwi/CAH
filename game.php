<?php
	usleep(100000);
	ini_set('display_errors','0');
	include "languages/config.php";	
    if(!isset($_SESSION['login']) || $_SESSION['login']==false){
		header('Location: index.php');
		exit();
	} 
	require_once('phpscripts/connect_users.php');
	$lobby_id = $_GET['id'];
	$dsn = "mysql:host=".$host.";dbname=".$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$last_change = floor(microtime(true) * 1000);
	$delete = $last_change - 3600000;
	$delete2 = $last_change - 5000;
	$sql = "SELECT * FROM lobby WHERE lobby_id = '$lobby_id'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
	$last_change_lobby = $row['last_change'];
	$last_change_players = $last_change;
	$abs = abs($last_change_players - $last_change_lobby);
	$user = $_SESSION['user'];
	try{
		$pdo->beginTransaction();
		$sql = "SELECT * FROM lobby WHERE lobby_id = '$lobby_id'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$lobby_before = $stmt->fetch();
		if($stmt->rowCount() == 0){
			header('Location: index.php');
			exit();
		}
		if(($lobby_before['last_change'] < $delete && $lobby_before['last_change_round'] < $delete) || ($delete2 > $lobby_before['last_change_players'] && $lobby_before['players_in_lobby']<=0)){
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
		if($abs>2000){
			$sql = "SELECT * FROM players_in_lobby WHERE nick = '$user'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			if($stmt->rowCount()==0){
				$sql = "INSERT INTO players_in_lobby (nick, lobby_id, points, chooser, last_change) VALUES ('$user', '$lobby_id', 0, false, '$last_change')";
				$stmt = $pdo->prepare($sql);
					$stmt->execute();
				$sql = "UPDATE lobby SET last_change_players = '$last_change', players_in_lobby = players_in_lobby+1 WHERE lobby_id = '$lobby_id'";
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
			if($lobby_before['players_in_lobby'] + 1 > $lobby_before['max_players']){
				header('Location: index.php');
				exit();
			}
		}
		if($lobby_before['game_started']==1){
			$sql = "SELECT * FROM cards_in_lobby WHERE lobby_id = :id AND color = 'white'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute(['id'=>$lobby_id]);
			$sql = "SELECT * FROM cards_in_lobby WHERE lobby_id = :id AND color = 'black' AND choosen = 1";
			$stmt = $pdo->prepare($sql);
			$stmt->execute(['id'=>$lobby_id]);
			$black_card = $stmt->fetch();
			$sql = "SELECT * FROM cards_in_lobby WHERE lobby_id = :id AND color = 'white' AND owner = '$user'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute(['id'=>$lobby_id]);
			if($stmt->rowCount()<10){
				$sql = "UPDATE cards_in_lobby SET owner = '$user' WHERE lobby_id = :id AND color = 'white' AND owner IS NULL ORDER BY RAND() LIMIT 10";
				$stmt=$pdo->prepare($sql);
				$stmt->execute(['id'=>$lobby_id]);
			}
			$sql = "SELECT * FROM cards_in_lobby WHERE owner = '$user'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			if($stmt->rowCount()==0){
				Header('Location: '.$_SERVER['PHP_SELF']);
				Exit();
			}
			$my_cards = $stmt->fetchAll();
		}
		$_SESSION['game']= true;
		$sql = "SELECT * FROM players_in_lobby WHERE nick = '$user'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$row = $stmt->fetch();
		$chooser = $row['chooser'];
		usleep(50000);
		$sql = "SELECT * FROM players_in_lobby WHERE lobby_id = '$lobby_id' ORDER BY 'ID' ASC";
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
		echo $error_message;


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
	<?php
	if($lobby['game_started']==0){
		
		echo'
		<div id = "lang_before">
				
		<label class = "lang_change"><img src ="img/plflag"><input type = "submit" name = "hl" value ="pl" class = "hl" ></label>
	
		<label class = "lang_change"> <img src = "img/enflag"> <input type = "submit" name = "hl" value ="en" class = "hl" ></label>
		
		</div>
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
		<h2>'.$lang['players'].'	
		<span id = "players_in_lobby">
			'.$lobby['players_in_lobby'].'
		</span>
		/
		<span id = "max_players">
			'.$lobby['max_players'].'
		</span>
		<i class = "icon-adult"></i>
		</h2>
		<div id = "players" class = "players_not_started">';
			 foreach($players as $player){
				if($lobby['owner']===$player['nick']) {
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
	}	
	if($lobby['game_started']==1){
		echo'
		<div id = "lang_after">
				
		<label class = "lang_change"><img src ="img/plflag"><input type = "submit" name = "hl" value ="pl" class = "hl" ></label>
	
		<label class = "lang_change"> <img src = "img/enflag"> <input type = "submit" name = "hl" value ="en" class = "hl" ></label>
		
		</div>
		<div id = "main">
			<div id = "left">
			<div id = "black_card_cont">
				<div id = "black_card">'.$black_card["value"].'</div>
			</div>
				<div id = "players" class = "players_started">';
				foreach($players as $player){
					if($lobby['owner']===$player['nick']) {
						$class = 'class = "nick owner"';
						$icon = '<i class ="icon-crown"></i>';
					}
					else
					{
						$class = 'class ="nick"';
						$icon = '';
					} 
					echo
					'
					<div class = "player_after player" id = "'.$player['nick'].'"><div class = "player_left"><span '.$class.'>'.$player['nick'].$icon.'</span>
					<div class = "points">'.$lang['points'].'<span class = "value">'.$player['points'].'</span></div>
					</div>
					<div class = "player_right">';
					if($player['chooser']==1) echo $lang['Selecting'];
					echo '</div>
					<div style = "clear:both;"></div>
					</div>';
				}
			
			echo' </div></div>
			<div id = "right">
			<div id = "white_cards_cont">
 



			</div>
			<div style = "clear: both;"></div>
			<div id = "UI">';
			if($chooser == 1) $style = 'style = "display: none;"';
			else $style = '';
			echo '<div id = "my_cards"'.$style.'>';
				foreach($my_cards as $card){
					echo '<label id = '.$card['ID'].' class = "white_card">'.$card['value'].'<input type = "checkbox" id = check'.$card['ID'].' class = "white_check"/></label>';
				}
			echo '</div>
			<div id = "menu">
				<div id = "btn">'.$lang['Select'].'</div>
			</div>';
			if($chooser == 1){
				echo '<div id = "select_info">'.$lang['Selecting_information'].'</div>';
			}
		echo '</div>
		</div></div>';
	}
	?>
	<script src = "js/game.js"></script>
</body>