<?php
	include "languages/config.php";	
	try{
		require_once("phpscripts/connect_users.php");
		if(!isset($_SESSION['login']) || $_SESSION['login']==false) header('Location: index.php');
		if(empty($_GET['id'])) header('Location: index.php');
		$dsn = 'mysql:host='.$host.';dbname='.$db_name;
		$pdo = new PDO($dsn, $db_user, $db_password);
		$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$deck_code = $_GET['id'];
		$nick = $_SESSION['user'];
		$sql = "SELECT * FROM decks WHERE BINARY deck_code = '$deck_code' AND author = '$nick'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$row = $stmt->fetch();
		if($stmt->rowCount()==0){
			header('Location: index.php');	
			exit();
		}
		$stmt->execute();
		$sql = "SELECT * FROM decks WHERE BINARY deck_code = '$deck_code'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$title = $row['deck_title'];
		$sql ="SELECT * from cards WHERE BINARY deck_code =	'$deck_code'";
		$stmt =  $pdo->prepare($sql);
		$stmt->execute();
		$cards = $stmt->fetchAll();
	}
	catch(PDOException $e){
		$error_message = $e->getMessage();
		echo $error_message;
		
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
	<link href="css/style_card_creator.css" type="text/css" rel="stylesheet" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href = "fontello/css/fontello.css" type ="text/css" rel = "stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;900&display=swap" rel="stylesheet">
	<script src="js/jquery-3.6.0.min.js"></script>	
	<script src = "js/FormSubmitLang.js"></script> 

	
</head>
<body>
	<div id = "lang">
		
			<label class = "lang_change"><img src ="img/plflag"><input type = "submit" name = "hl" value ="pl" class = "hl" ></label>
			
			<label class = "lang_change"> <img src = "img/enflag"> <input type = "submit" name = "hl" value ="en" class = "hl" ></label>
	</div>
	<div id = "container">
		<div id = "tutorial">
		<h1><?= $lang['tutorial_header'] ?></h1>
		<p><?= $lang['tutorial_message']?></p>
		</div>
		<div id = "adding_cards">
			<div id = "black_side">
				<textarea id = "black_input" maxlength = "200"></textarea>
				<input type = "checkbox" id = "black_input_antydouble" class = "cards_antydouble_checkbox"><label for = "black_input_antydouble" class = "cards_antydouble"><?=$lang['add_cards_antydouble']?></label>
				<label id = "add_card_black" class = "add_card" tabindex = "0"><?= $lang['add_card_button']?></label>
			</div>
			<div id = "white_side">
				<textarea id = "white_input" maxlength = "170"></textarea>
				<input type = "checkbox" id = "white_input_antydouble" class = "cards_antydouble_checkbox"><label for = "white_input_antydouble" class = "cards_antydouble"><?=$lang['add_cards_antydouble']?></label>
				<label id = "add_card_white" class = "add_card" tabindex = "0"><?= $lang['add_card_button']?></label>
			</div>
			<div style = "clear:both;"></div>
			<span id ="adding_result"></span>
		</div>
		<div id = "cards_added">
			<h1 class = "cards_added_h1"><?= $lang['black_cards'].'(<span id = "black_cards_h1">'.$row['black_cards'].'</span>):'?></h1>
			<h1 class = "cards_added_h1"><?= $lang['white_cards'].'(<span id = "white_cards_h1">'.$row['white_cards'].'</span>):'?></h1>
			<div id ="black_cards_added" class = "cards">
			<?php foreach($cards as $value){
				if($value['color']=='black'){
					echo "<label id = '".$value['ID']."' class = 'black_card added_card' tabindex ='0'>".$value['value']."<input class = 'added_card_check' type = 'checkbox'></label>";
				}
			}?>
			</div>
			<div id ="white_cards_added" class = "cards">
			<?php foreach($cards as $value){
				if($value['color']=='white'){
					echo "<label id = '".$value['ID']."' class = 'white_card added_card' tabindex ='0'>".$value['value']."<input class = 'added_card_check' type = 'checkbox'></label>";
				}
			}?>
			</div>
			<div style = "clear:both;"></div>
		</div>
		<div id = "remove_cards">
		<?= $lang['remove_cards'] ?>
		</div>
	</div>
	<div id = "deck_info">
		<?=" ID: ".$deck_code."<br>".$lang['deck_title'].$title ?>
	</div>
	<script src = "js/card_creator.js"></script>
</body>
</html>