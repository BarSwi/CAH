<?php
	include "languages/config.php";
	if(isset($_SESSION['login']) && $_SESSION['login']!=false) header('Location: index.php');

?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset = "utf-8"/>
	<title>GoCards</title>
	<meta name = "description" content ="Gra karciana, do wspólnej zabawy ze znajomymi." />
	<meta name = "keywords" content = "Karty, gra karciana, multiplayer, zabawne, do gry ze znajomymi, na wolny wieczór" />
	<meta http-equiv="X-UA-Compatible" content = "IE=edge,chrome=1"/> 
	<link href="css/style_register.css" type="text/css" rel="stylesheet" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;900&display=swap" rel="stylesheet">
	<link href = "fontello/icons/css/fontello.css" type ="text/css" rel = "stylesheet">
	<script src="js/jquery-3.6.0.min.js"></script>
	<script src = "js/FormSubmitLang.js"></script> 
	<script src = "js/captcha.js"></script>

	
</head>
<body id = "dark">
	<div id="top">
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
	<div id = "container">
			<div id = "register">
				<table>	
					<tr>
						<td id ="login_label"><?=$lang['register_nickname']?></td> <td> <input type = "text" maxlength ="15" onkeyup="validation()" id = "register_login"> <span class = "help" data-title = <?= '"'.$lang['register_nickname_help'].'"' ?>><i class = "icon-help-circled "></i></span></td>
					</tr>
					<tr>
						<td><?=$lang['register_email']?></td> <td> <input type = "email" onkeyup="validation()"  placeholder = "example@gmail.com" class = "no_inf"  id = "register_email"></td>
					</tr>
					<tr>
						<td><?=$lang['register_password']?></td> <td> <input type = "password" onkeyup="validation()" id = "register_password" > <span class = "help" data-title = <?= '"'.$lang['register_password_help'].'"' ?> ><i class = "icon-help-circled"> </td></span>
					</tr>
					<tr>
						<td><?=$lang['register_password-re']?></td> <td> <input type = "password" onkeyup="validation()"  class = "no_inf" id = "register_password-re"> </td>
					</tr>
					<tr>
						<td id = "equation"></td> <td> <input type = "text" onkeyup="validation()"  id ="equation_result"><span class = "help" data-title = <?= '"'.$lang['register_antybot'].'"' ?> ><i class = "icon-help-circled"> </td></span>
					</tr>
					<tr>
						<td colspan = "2"><span id = "errorl"></span> <span id = "errore"></span></td>
					</tr>
				</table>
			</div>
			<h1><?= $lang['side_rules']?></h1>
			<div id = "rules">
				<ol>
				<?=
					'<li><span class = "rules">'.$lang['side_rule1'].'</span></li><br>
					<li><span class = "rules">'.$lang['side_rule2'].'</span></li><br>
					<li><span class = "rules">'.$lang['side_rule3'].'</span></li><br>
					<li><span class = "rules">'.$lang['side_rule4'].'</span></li><br>
					<li><span class = "rules">'.$lang['side_rule5'].'</span></li><br>
					<li><span class = "rules">'.$lang['side_rule6'].'</span></li><br>
					<li><span class = "rules">'.$lang['side_rule7'].'</span></li><br>
					<li><span class = "rules">'.$lang['side_rule8'].'</span></li>'
				?>
				</ol>
				<input type = 'checkbox' id = "rules_accept"><label for = "rules_accept" id = "rules_accept_label"><?= $lang['side_rules_accept'] ?>
				</label>
			</div>
			<div id = "bottom">
				<input type = "submit" value = <?= '"'.$lang['register_submit'].'"' ?>   id = "register_button" disabled>
			</div>
			<div id = "hl" style = "display: none;"></div>
	</div>
	<script src = "js/captcha.js"></script>
	<script src="js/theme.js"></script>
</body>