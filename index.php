<?php
	require_once('phpscripts/connect_users.php');
	include "languages/config.php";	
	$dsn = "mysql:host=".$host.";dbname=".$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$sql = "SELECT * FROM lobby";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$lobbies = $stmt->fetchAll();
	if(isset($_SESSION['login']) && $_SESSION['login']==true){

		$nick = $_SESSION['user'];
		$_SESSION['game']=false;
		$last_change = floor(microtime(true) * 1000);
		$delete = $last_change - 3600000;
		try{
			$pdo->beginTransaction();
			$sql = "SELECT * FROM players_in_lobby WHERE nick = '$nick'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			if($stmt->rowCount()>0){
	
				$row = $stmt->fetch();
				$lobby_id = $row['lobby_id'];
				$sql = "DELETE FROM players_in_lobby WHERE nick = '$nick'";
				$stmt = $pdo->prepare($sql);
				$stmt->execute();
				$sql = "UPDATE lobby SET players_in_lobby = players_in_lobby-1 WHERE lobby_id = '$lobby_id'";
				$stmt = $pdo->prepare($sql);
				$stmt->execute();
				$sql = "UPDATE lobby SET last_change_players = '$last_change' WHERE lobby_id = '$lobby_id'";
				$stmt = $pdo->prepare($sql);
				$stmt->execute();
			}
			if($_SESSION['login']==true){
				if($_SESSION['game']==true){
					$sql = "DELETE FROM players_in_lobby WHERE nick = '$nick'";
					$stmt = $pdo->prepare($sql);
					$stmt->execute();
				}
			}
			$sql = "SELECT * FROM lobby WHERE last_change < $delete AND last_change_players < $delete OR players_in_lobby < 1";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetchAll();
			$sql = "DELETE FROM lobby WHERE last_change < $delete AND last_change_players < $delete OR players_in_lobby < 1";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			foreach($row as $lobby){
				$id = $lobby['lobby_id'];
				$sql = "DELETE FROM players_in_lobby WHERE lobby_id = '$id'";
				$stmt = $pdo->prepare($sql);
				$stmt->execute();
				$sql = "DELETE FROM cards_in_lobby WHERE lobby_id = '$id'";
				$stmt = $pdo->prepare($sql);
				$stmt->execute();
			}
			$pdo->commit();
			
		}
		catch(PDOException $e){
			$pdo->rollBack();
	
		}
	}
  
	
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset = "utf-8"/>
	<title>GoCards</title>
	<meta name = "description" content ="Gra karciana, do wspólnej zabawy ze znajomymi." />
	<meta name = "keywords" content = "Karty, gra karciana, multiplayer, zabawne, znajomi, wieczór, evening friends, card game, cards" />
	<meta http-equiv="X-UA-Compatible" content = "IE=edge,chrome=1"/> 
	<link href="css/style.css" type="text/css" rel="stylesheet" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href = "fontello/icons/css/fontello.css" type ="text/css" rel = "stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;900&display=swap" rel="stylesheet">
	<script src="js/jquery-3.6.0.min.js"></script>
	<script src = "js/FormSubmitLang.js"></script> 

	
	
</head>
 <body id = "dark">
	<div id = "top">
		<div id = "lefttop">
			<div id = "lang">
				
				<label class = "lang_change"><img src ="img/plflag"><input type = "submit" name = "hl" value ="pl" class = "hl" ></label>
			
				<label class = "lang_change"> <img src = "img/enflag"> <input type = "submit" name = "hl" value ="en" class = "hl" ></label>
				
			</div>
			<div id = "theme">
				<div class = "t_icon">
				🌙
				</div>
				<div id = "themetoggler">
					<label>
					<input type = "checkbox" id = "topcheck" name = "topcheck">
					<span class = "check"></span>
					</label>
				</div>
				<div class = "t_icon">
				🌞
				</div>
			</div>
		</div>
		<div id = "middletop">
			<?php
			if(!isset($_SESSION['login']) || $_SESSION['login']==false)	echo '<a title = "'.$lang['lobbycreate_title'].'"  id = "inactive" >'.$lang['lobbycreate'].'</a>'; 
			else echo  '<a  href="create_lobby.php"  id = "active" >'.$lang['lobbycreate'].'</a>' ;
			?>
		</div>
		<div id = "righttop">
			<?php if(!isset($_SESSION['login']) || $_SESSION['login']==false) echo
			'<div id = "login">
				<form>
				<input type = "text" id = "input_login" placeholder = "Login" required oninvalid = "this.setCustomValidity(\''.$lang['login_invalid_mess'].'\')" oninput = "this.setCustomValidity(\'\')" maxlength = "15" title = "" /></br>
				<input type ="password" id = "input_password" title = "" placeholder = "'.$lang['login_password'].'" required oninvalid = \'this.setCustomValidity("'.$lang['password_invalid_mess'].'")\' oninput = "this.setCustomValidity(\'\')"></br>
				<button id = "login_button">'.$lang["login_button"].'</button>	
				</form>
			</div>
			<div id = "register">'.$lang['acc_noexist_text'].'<br>
				<a href = "register.php">'.$lang["acc_register"].'</a>
			</div>';
			else echo
				'<div id = "nickname">'.$_SESSION['user'].'</div><br>
				<div id = "my_cards" tabindex="0"><a href = "my_cards.php" tabindex ="-1">'.$lang['my_cards_button'].'</a></div>
				<div id = "create_new_deck" tabindex="0">'.$lang['create_new_deck_button'].'</div>
				<div id = "logout_button" tabindex="0">'.$lang['Logout_button'].'</div>
				'
			?>

		</div>
	</div>
	<div style = "clear:both;"></div>
	<div id = "container">
		<div id = "left_container">
		
			<div id = "left_nav">
				<input type = "checkbox" id = "game_rules_check" class = "rules" checked><label class = "rules" id ="game_rules" for = "game_rules_check"><?= $lang['game_rules'] ?></label>
				<input type = "checkbox" id = "side_rules_check" class = "rules"><label class = "rules" id = "side_rules" for ="side_rules_check"><?= $lang['side_rules'] ?></label>		
				<div id = "game_rules_text">
					<ul><?=
						'<li><strong>'.$lang['game_rule1'].'</strong></li><br><br>
						<li>'.$lang['game_rule2'].'</li><br><br>
						<li>'.$lang['game_rule3'].'</li><br><br>
						<li>'.$lang['game_rule4'].'</li><br><br>
						<li>'.$lang['game_rule5'].'</li><br><br>
						<li>'.$lang['game_rule6'].'</li><br><br>
						<li>'.$lang['game_rule7'].'</li><br><br>
						<li>'.$lang['game_rule8'].'</li><br><br>
						<li>'.$lang['game_rule9'].'</li><br><br>
						<li>'.$lang['game_rule10'].'</li><br><br>
						<li>'.$lang['game_rule11'].'</li>'
						?>
					</ul>
				</div>
				<div id = "side_rules_text">
					<ol><?=
						'<br><li>'.$lang["side_rule1"].'</li><br><br>
						<li>'.$lang['side_rule2'].'</li><br><br>
						<li>'.$lang['side_rule3'].'</li><br><br>
						<li>'.$lang['side_rule4'].'</li><br><br>
						<li>'.$lang['side_rule5'].'</li><br><br>
						<li>'.$lang['side_rule6'].'</li><br><br>
						<li>'.$lang['side_rule7'].'</li><br><br>
						<li>'.$lang['side_rule8'].'</li>'
						?>
					</ol>
				</div>
			</div>
		
		</div>
		<div id ="middle_container">
			<div id = "navbar">
				<button id = "refresh"><?=$lang['Refresh']?></button>
				<?= $lang['active_lobbies']?>
				<input type = "search" placeholder = <?= $lang['Search'] ?>></input>
			</div>
			<div id = "lobbies">
				<?php
					foreach($lobbies as $lobby){
						$id = $lobby['ID'];
						$owner = $lobby['owner'];
						$title = $lobby['lobby_title'];
						$password = $lobby['lobby_password'];
						$max_players = $lobby['max_players'];
						$players = $lobby['players_in_lobby'];
						if($lobby['game_started']==true){
							$status = $lang['game_started'];
							$class = 'class = "started"';						} 
						else
						{
							$status = $lang['game_waiting'];
							$class = 'class = "not_started"';
						} 
						echo 
						'<div class = "lobby" id ='.$id.'>';	
							if(!empty($password)) echo '<i class = "icon-lock"></i>';
							echo
							'<div id = "lobby_owner">'.
								$owner
							.'<i class = "icon-crown" ></i></div>
							<div id = "lobby_title">„'.
								$title	
							.'”</div>
							<div id = "status"'.$class.'><br>'.
								$status
							.'</div><br>
							<div id = "players_in_lobby">'.
								$players
							.'/'.$max_players.'<i class = "icon-adult"></i></div><br>
							<div id = "join">'.$lang['join'].'</div>
						</div>';

					}
				?>
			</div>
		</div>
		
		<div id = "right_container">
		
			<?php if(isset($_SESSION['login_error'])){ 
			echo '<span id = "error_login">'.$lang['Invalid_login_data'].'</span><br><br><span id = "forgot_password">'.$lang["forgot_password"].'</span>'; 
			unset($_SESSION['login_error']);
			}
			?>
		</div>
	</div>
	<?php if(isset($_SESSION['register']))
	{		echo
		'<div id = "register_success">
			<h1>'.$lang['register_success'].'</h1>
			<label id = "close_register"><input type ="button" id = "close_register_btn"><i class = "icon-cancel"></i></label>
		</div>';
		unset($_SESSION['register']);
	}
	if(isset($_SESSION['password_recovery'])){
		if($_SESSION['password_recovery'] == 0){
			echo
			'<div id = "password_recovery">
				<h1>'.$lang['recovery_failed_expired'].'</h1>
				<label id = "close_register"><input type ="button" id = "close_recovery_btn"><i class = "icon-cancel"></i></label>
			</div>';
			unset($_SESSION['password_recovery']);
		}
		}
	?>
	<script src="js/theme.js"></script>
	<script src = "js/login.js"></script>
	
</body>
</html>

			