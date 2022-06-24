

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset = "utf-8"/>
	<title>GoCards</title>
	<meta name = "description" content =<?= $lang['side_description'] ?> />
	<meta http-equiv="X-UA-Compatible" content = "IE=edge,chrome=1"/> 
	<link href="css/gameStyle.css" type="text/css" rel="stylesheet" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href = "fontello/css/fontello.css" type ="text/css" rel = "stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;900&display=swap" rel="stylesheet">
	<script src="js/jquery-3.6.0.min.js"></script>
	<script src = "js/FormSubmitLang.js"></script> 
</head>
<?php
		usleep(100000);
	//ini_set('display_errors','0');
	include "languages/config.php";	
    if(!isset($_SESSION['login']) || $_SESSION['login']==false){
		header('Location: Home');
		exit();
	} 
	require_once('phpscripts/connect_users.php');
	$lobby_id = $_GET['id'];
	if(!ctype_alnum($lobby_id)){
		header('Location: Home');
		exit();
	}
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
	if($stmt->rowCount() == 0){
		header('Location: Home');
		exit();
	}
	$lobby_before = $stmt->fetch();
	$lobby_password = $lobby_before['lobby_password'];
	$last_change_lobby = $lobby_before['last_change'];
	$afk_time = $lobby_before['lobby_afk_time'];
	$last_change_players = $last_change;
	$abs = abs($last_change_players - $last_change_lobby);
	$user = $_SESSION['user'];
	if(isset($_SESSION['lobby_password_ignore']) && $_SESSION['lobby_password_ignore'] == $lobby_id || empty($lobby_password)){
		try{
			$pdo->beginTransaction();
			if($lobby_before['reset']==1 && $lobby_before['game_started']==0){
				sleep(1);
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
				$row = $stmt->fetch();
				$chooser = $row['chooser'];
				$player_id = $row['ID'];
				$remove_id = $row['lobby_id'];
				if($stmt->rowCount()==0){
					$sql = "INSERT INTO players_in_lobby (nick, lobby_id, points, chooser, last_change) VALUES ('$user', '$lobby_id', 0, false, '$last_change')";
					$stmt = $pdo->prepare($sql);
					$stmt->execute();
					$sql = "UPDATE lobby SET last_change_players = '$last_change', players_in_lobby = players_in_lobby+1 WHERE lobby_id = '$lobby_id'";
					$stmt = $pdo->prepare($sql);
					$stmt->execute();
				}
				else{
					$sql = "SELECT * FROM lobby WHERE owner = '$user'";
					$stmt = $pdo->prepare($sql);
					$stmt->execute();	
					if($stmt->rowCount()!=0){
						$owner = 1;
					}
					if($chooser == 1){
						$sql = "UPDATE players_in_lobby SET chooser = 1 WHERE lobby_id = '$remove_id' AND ID > $player_id AND afk = 0 LIMIT 1";
						$stmt = $pdo->prepare($sql);
						$stmt->execute();
						if($stmt->rowCount()==0){
							$sql = "UPDATE players_in_lobby SET chooser = 1 WHERE lobby_id = '$remove_id' AND afk = 0 LIMIT 1";
							$stmt = $pdo->prepare($sql);
							$stmt->execute();
						}
					}
					if($owner == 1){
						$sql = "UPDATE lobby SET owner = (SELECT nick FROM players_in_lobby WHERE lobby_id = '$remove_id' LIMIT 1) WHERE lobby_id = '$remove_id'";
						$stmt = $pdo->prepare($sql);
						$stmt->execute();
					}
					$sql = "UPDATE lobby SET players_in_lobby = players_in_lobby-1, last_change_players = '$last_change' WHERE lobby_id IN (SELECT lobby_id FROM players_in_lobby WHERE nick = '$user')";
					$stmt = $pdo->prepare($sql);
					$stmt->execute();
					$sql = "DELETE FROM players_in_lobby WHERE nick = '$user'";
					$stmt = $pdo->prepare($sql);
					$stmt->execute();
					$sql = "INSERT INTO players_in_lobby (nick, lobby_id, points, chooser, last_change) VALUES ('$user', '$lobby_id', 0, false, '$last_change')";
					$stmt = $pdo->prepare($sql);
					$stmt->execute();	
					$sql = "UPDATE lobby SET last_change_players = '$last_change', players_in_lobby = players_in_lobby+1 WHERE lobby_id = '$lobby_id'";
					$stmt = $pdo->prepare($sql);
					$stmt->execute();
				}
				if($lobby_before['players_in_lobby'] + 1 > $lobby_before['max_players']){
					header('Location: Home');
					exit();
				}
			}
			if($lobby_before['game_started']==1){
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
					$sql = "SELECT * FROM cards_in_lobby WHERE lobby_id = :id AND color = 'white' AND owner = '$user'";
					$stmt = $pdo->prepare($sql);
					$stmt->execute(['id'=>$lobby_id]);
				}
				$my_cards = $stmt->fetchAll();
			}
			$_SESSION['game']= true;
			$sql = "SELECT * FROM players_in_lobby WHERE nick = '$user'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch();
			$chooser = $row['chooser'];
			$pdo->commit();
			usleep(200000);
			$sql = "SELECT * FROM players_in_lobby WHERE lobby_id = '$lobby_id' ORDER BY 'ID' ASC";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$players = $stmt->fetchAll();
			$sql = "SELECT * FROM lobby WHERE lobby_id = '$lobby_id'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$lobby = $stmt->fetch();
			if($stmt->rowCount() == 0){
				header('Location: Home');
				exit();
			}
		}
		catch(PDOException $e){
			$pdo->rollBack();
			$error_message = $e->getMessage();
			echo $error_message;
	
	
		}
	}
	
	
?>
<body>
	<?php
	if(isset($_SESSION['lobby_password_ignore']) && $_SESSION['lobby_password_ignore'] == $lobby_id || empty($lobby_password)){
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
			
			</div>';
			if($lobby['reset'] == 1 || $lobby['round_started']==1){
				if($chooser==0){
					$style = '';
				}
				else{
					$style = 'style = "display: none;"';
				}
				echo '<div id = "timer" '.$style.'>'.$afk_time.'</div>';
			}

			echo '<div id = "main">
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
				<div id = "white_cards_cont"><div id = "white_cards_shown"></div>';
				if($lobby['round_started']==1){
					$sql = "SELECT * FROM cards_in_lobby WHERE color = 'white' AND choosen = 1 AND lobby_id = :id";
					$stmt=$pdo->prepare($sql);
					$stmt->execute(['id'=>$lobby_id]);
					$count = $stmt->rowCount();
					for($i = 0; $i<$count; $i++){
						echo '<div class = "white_card_picked"></div>';
					}
				}
				if($lobby['round_started']==0 && $lobby['reset']==0){
					$sql = "SELECT * FROM cardsShuffled WHERE lobby_id = :id ORDER BY `cardsShuffled`.`owner` DESC, `cardsShuffled`.`choosen` ASC";
					$stmt = $pdo->prepare($sql);
					$stmt->execute(['id'=>$lobby_id]);
					$cards = $stmt->fetchAll();
					foreach($cards as $card){
						$owner = $card['owner'];
						$card_id = $card['ID'];
						if(!isset($last_owner)) $last_owner = $owner;
						if(!isset($last_card)) $last_card = $card_id;
						if($last_owner == $owner){
							$card_id = $last_card;
						}
						else{
							$last_owner = $owner;
							$last_card = $card_id;
						}
						if($chooser == 1) $style = "";
						else $style = 'style = "pointer-events: none;"';
						echo '<label class = "white_card_picked '.$card_id.'"'.$style.'>'.$card['value'].'<input type = "checkbox" class = "select_check '.$card_id.'"></label>';
					}
				}
				echo '</div>
				<div style = "clear: both;"></div>
				<div id = "UI">';
				if($chooser == 1) $style = 'style = "display: none;"';
				else $style = '';
				if($lobby['round_started']==0 && $lobby['reset']==0){
					$style_card = 'style ="pointer-events: none;"';
					$style = 'style = "display: none;"';
					$style_btn = 'style = "display: none;"';
				}
				else $style_card = "";
				echo '<div id = "my_cards"'.$style.'>';
					foreach($my_cards as $card){
						echo '<label id = '.$card['ID'].' class = "white_card" '.$style_card.'>'.$card['value'].'<input type = "checkbox" id = check'.$card['ID'].' class = "white_check"></label>';
					}
				echo '</div>
				<div id = "menu">
					<div id = "btn" '.$style_btn.'>'.$lang['Select'].'</div>
				</div>
				<div id = "reroll" '.$style.'>'.$lang['Reroll_cards'].'</div>';
				if($chooser == 1){
					echo '<div id = "select_info">'.$lang['Selecting_information'].'</div>';
				}
			echo '</div>
			</div></div>';
		}
	}
	else{
		echo '<div id = "password_check"><span id = "insert_password_span">'.$lang['insert_password_lobby'].'</span><input type = "text" id = "password_input"/><div id = "password_submit" tabindex = "0">'.$lang['join'].'</div></div>';
	}
	?>
	<script src = "js/gameJs.js"></script>
</body>