<?php
	include "languages/config.php";	
	//$_SESSION['login']=true;
	//$_SESSION['login']=false;
	//unset($_SESSION['login']);
	//$_SESSION['register']=1;
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset = "utf-8"/>
	<title>Przykładowy tytuł</title>
	<meta name = "description" content ="Gra karciana, do wspólnej zabawy ze znajomymi." />
	<meta name = "keywords" content = "Karty, gra karciana, multiplayer, zabawne, do gry ze znajomymi, na wolny wieczór" />
	<meta http-equiv="X-UA-Compatible" content = "IE=edge,chrome=1"/> 
	<link href="css/style.css" type="text/css" rel="stylesheet" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href = "fontello/fontello-170c85d4/css/fontello.css" type ="text/css" rel = "stylesheet">
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
				<a href = "my_cards.php"><div id = "my_cards" tabindex="0">'.$lang['my_cards_button'].'</div></a>
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
		<div id ="middle_container"><br><br><br>
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

			