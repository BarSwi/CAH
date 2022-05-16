
<?php
	include "languages/config.php";
	if(isset($_SESSION['login']) && $_SESSION['login']!=false) header('Location: index.php');
	if(!isset($_GET['selector']) || !isset($_GET['validator']) || empty($_GET['selector']) || empty($_GET['validator'])){
		header('Location: index.php');
	}
	else{
		$selector = $_GET['selector'];
		$validator = $_GET['validator'];
		if(ctype_xdigit($selector)!= false && ctype_xdigit($validator)!=false){
			try{
				require_once "phpscripts/connect_users.php";
				$dsn = "mysql:host=".$host.";dbname=".$db_name;
				$pdo = new PDO($dsn, $db_user, $db_password);
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$currentDate = date("U");
				$sql = "SELECT * FROM password_reset WHERE password_reset_selector = '$selector' AND password_reset_expires >= $currentDate";
				$stmt = $pdo->prepare($sql);
				$stmt->execute();
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if($stmt->rowCount()==0){
					$_SESSION['password_recovery']=0;
					header ('Location: index.php');
					exit();
				}
				$tokenBin = hex2bin($validator);
				if(!$tokenCheck = password_verify($tokenBin, $row['password_reset_token'])){
							$_SESSION['password_recovery']=0;
							header ('Location: index.php');
							exit();
				}
				$email = $row['reset_email'];
			}
			catch(PDOException $e){
				$error_message = $e->getMessage();
				echo $error_message;
				
			}
		}
		else{
			header ('Location: index.php');
			$_SESSION['password_recovery']=0;
			exit();
		}
	}


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
	<div id = "container" style = "margin-top: 30vh;">
			<div id = "register">
				<table style = "margin-top: 25px;">	
					<tr>
						<td><?=$lang['password_change']?></td> <td> <input type = "password" id = "register_password" > <span class = "help" data-title = <?= '"'.$lang['register_password_help'].'"' ?> ><i class = "icon-help-circled"> </td></span>
					</tr>
					<tr>
						<td><?=$lang['register_password-re']?></td> <td> <input type = "password" class = "no_inf" id = "register_password-re"> </td>
					</tr>
				</table>
			<div id = "bottom">
				<input type = "submit" value = <?= '"'.$lang['change_password_button'].'"' ?>   id = "register_button" disabled>
			</div>
			</div>
			<div id = "hl" style = "display: none;"></div>
	</div>
	<script src="js/theme.js"></script>
	<script src = "js/change_password.js"></script>
</body>