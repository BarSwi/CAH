<?php
	include "languages/config.php";	
    if(!isset($_SESSION['login']) || $_SESSION['login']==false) header('Location: index.php');
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
					<label id = "round_limit_label"><?= $lang['round_limit']?>
						<input id = "round_limit_input" value = "3" type = "number" max = "999" min="3" />
					</label>
				</div>
				<div id = "round_time">
					<label id = "round_time_label"><?= $lang['round_time']?>
						<input id = "round_time_input" type = "number" value = "15" min="15"  max = "60" />
					</label>
				</div>
			</div>
			<input type = "submit" id = "form"/>
		</form>
	</div>

	<script src = "js/create_lobby.js"></script>
</body>