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
<div id = "lang">
    
    <label class = "lang_change"><img src ="img/plflag"><input type = "submit" name = "hl" value ="pl" class = "hl" ></label>

    <label class = "lang_change"> <img src = "img/enflag"> <input type = "submit" name = "hl" value ="en" class = "hl" ></label>
    <div style = "clear:both;"></div>
</div>
	<div id ="container">
		<form action ="">
			<div id = "overall_inf">
				<div id ="lobby_title">
					<label id = "lobby_title_label"><?= $lang['lobby_name']?>
						<input type = "text" required ="true" value= <?=$lang['example_title'] ?> maxlength = "18" id = "lobby_title_input" />
					</label>
				</div>
				<div id ="lobby_password">
				<label id = "lobby_password_label"><?= $lang['lobby_password']?>
						<input type = "password" id = "lobby_password_input" />
					</label>
				</div>
			</div>
			<div id = "sub_inf">
				<div id = "max_players"><?=$lang['max_players'] ?>
					<label id = "max_players_label">
						<input id = "max_players_input" type = "number" value = "3" min="3" max = "10" />
					</label>
				</div>
				<div id ="round_limit">
					<label id = "round_limit_label"><?= $lang['point_limit']?>
						<input id = "round_limit_input" value = "3" type = "number" max = "999" min="3" />
					</label>
				</div>
				<div id = "round_time">
					<label id = "round_time_label"><?= $lang['round_time']?>
						<input id = "round_time_input" type = "number" value = "15" min="15"  max = "60" />
					</label>
				</div>
			</div>
			<div id = "add_decks">
				<h2><?=$lang['add_decks_to_game'] ?> </h2>
				<ol id ="my_decks">
					<?php 
						foreach($decks as $deck){
							$title = $deck['deck_title'];
							$white_cards = $deck['white_cards'];
							$black_cards = $deck['black_cards'];
							$deck_id = $deck['deck_code'];
							echo 
							'<li id = "deck">
								<table>
									<tr>
										<th>ID:</th>
										<th>'.$lang['deck_title'].'</th>
										<th>'.$lang['white_cards'].'</th>
										<th>'.$lang['black_cards'].'</th>
										<td rowspan ="2" style = "border: none;"><div class = "add_my_deck_btn" id = "'.$deck_id.'">'.$lang['add_deck_btn'].'</div></td>
									</tr>
									<tr>
										<td class = "deck_id">'.$deck_id.'</td>
										<td class = "deck_title">'.$title.'</td>
										<td class = "white_cards">'.$white_cards.'</td>
										<td class = "black_cards">'.$black_cards.'</td>
									</tr>
								</table>
							</li>';
						}
					?>
				</ol>
				<label id = "add_decks_label"><?= $lang['add_decks'] ?>
					<input id = "add_decks_input" type = "text" maxlength = "7" /><span id = "add_decks_btn"> +</span>
				</label>
				<div id ="added_decks">
					<h2><?= $lang['added_decks'] ?></h2>
					<div id = "added_decks_list">
						<?php 
							echo 
							'<table id = "added_decks_table">
								<tr>
									<th>ID:</th>
									<th>'.$lang['deck_title'].'</th>
									<th>'.$lang['white_cards'].'</th>
									<th>'.$lang['black_cards'].'</th>
								</tr>
							</table>'
						
						?>
						<h2 id = "child"> <?= $lang['added_decks_list'] ?> </h2>
					</div>
				</div>
				<div id = "min_cards">
					<span id = "white_cards" class ="min_amount">
						<?= $lang['min_white_cards'] ?>
						<span id = "current_white_cards">
							0 
						</span>
							/
						<span id ="min_amount_white_cards">
							46
						</span>
					</span>
					<br><br>
					<span id = "black_cards" class = "min_amount">
						<?= $lang['min_black_cards'] ?>
						<span id = "current_black_cards">
							0 
						</span>
							/
						<span id ="min_amount_black_cards">
							7
						</span>
					</span>
				</div>
			</div>

			<button id = "form" disabled><div id = "create_lobby" ><?=$lang['lobbycreate'] ?></div></button>
		</form>
	</div>
	<script src = "js/create_lobby.js"></script>
</body>