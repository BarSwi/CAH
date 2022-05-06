<?php
	include "languages/config.php";	
    if(!isset($_SESSION['login']) || $_SESSION['login']==false) header('Location: index.php');
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
<body>
<div id = "lang">
    
    <label class = "lang_change"><img src ="img/plflag"><input type = "submit" name = "hl" value ="pl" class = "hl" ></label>

    <label class = "lang_change"> <img src = "img/enflag"> <input type = "submit" name = "hl" value ="en" class = "hl" ></label>
    
</div>

</body>