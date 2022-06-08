<?php
	include "languages/config.php";	
	require_once('phpscripts/connect_users.php');
	if(!isset($_SESSION['login']) || $_SESSION['login']==false) header('Location: index.php');
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
	<meta name = "description" content ="Gra karciana, do wspólnej zabawy ze znajomymi." />
	<meta name = "keywords" content = "Karty, gra karciana, multiplayer, zabawne, do gry ze znajomymi, na wolny wieczór" />
	<meta http-equiv="X-UA-Compatible" content = "IE=edge,chrome=1"/> 
	<link href="css/my_cards.css" type="text/css" rel="stylesheet" />
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
		<?php 
		echo '<div id = "container">';
			$counter = 0;
			if($stmt->rowCount()==0) echo "<h2>".$lang['no_decks']."</h1>";
			else{
				foreach($decks as $deck){
					$counter = $counter + 1;
					$white_cards = $deck['white_cards'];
					$black_cards = $deck['black_cards'];
					$deck_title = $deck['deck_title'];
					$deck_code = $deck['deck_code'];
					echo '<div class = "deck" id = "deck'.$counter.'"><span id = "deck_content"><br><br>ID: <span id ="deck_id">'.$deck_code.'</span><br><br>'.$lang['deck_title'].'<span id = "deck_title">'.$deck_title.'</span><span id = "deck_content_cards">'.$lang['white_cards'].': '.$white_cards.'<br>'.$lang['black_cards'].': '.$black_cards.'
					</span></span></div>';
				}
				if($stmt->rowCount()>1){ echo
				'<i class = "icon-right-circled2"></i>
				<i class = "icon-left-circled2"></i></div>';
				echo '<div id = "bottom"><div id = "edit_btn" class = "btn">'.$lang['edit_deck'].'</div><div id = "delete_btn" class = "btn">'.$lang['delete_deck'].'</div></div>';
				}
			}
		if($stmt->rowCount()<3) echo '<div id = "create_btn" class = "btn"">'.$lang['create_new_deck_button'].'</div>';
		?>
	</div>
	<script src = "js/my_cards.js"></script>
</body>