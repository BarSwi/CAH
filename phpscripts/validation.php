<?php
	ini_set('display_errors','0');
	session_start();
	$login = $_POST['login'];
	$haslo = $_POST['haslo'];
	$haslo2 = $_POST['haslo2'];
	$email = $_POST['email'];
	$bot = $_POST['bot'];
	

	
	if(strlen($login)>15 || strlen($login)<3 || ctype_alnum($login)==false){
		echo "0";
		exit();
	}
	if(ctype_alnum($login)==false){
		echo "0";
		exit();
	}
	if(filter_var($email, FILTER_VALIDATE_EMAIL)==false){
		echo "0";
		exit();
	}
	if(strlen($haslo)<8){
		echo "0";
		exit();
	}
	if($haslo != $haslo2){
		echo "0";
		exit();
	}
	if(!$bot){
		echo "0";
		exit();
	}
	
	try{
		require_once("connect_users.php");
		$dsn = 'mysql:host='.$host.';dbname='.$db_name;
		$pdo = new PDO($dsn, $db_user, $db_password);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$haslo_hash = password_hash($haslo, PASSWORD_DEFAULT);
		$sql = "SELECT * FROM users WHERE BINARY login = :login";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['login' => $login]);
		if($stmt->rowCount()>0){
			echo "0";
			exit();
		}
		$sql = "SELECT * FROM users WHERE BINARY email = :email";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['email' => $email]);
		if($stmt->rowCount()>0){ 
		echo "0";
		exit();
		}
		$sql = 'INSERT INTO users(id, login, password, email, ingame) VALUES(NULL, :login, :password, :email, false)';
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['login'=> $login, 'password' => $haslo_hash, 'email'=> $email]);
		$_SESSION['login'] = true;
		$_SESSION['register']= true;
		$_SESSION['user'] = $login;
		echo "1";
	}
	catch(PDOException $e){
		$error_message = $e->getMessage();
		echo $error_message;
	}
	
?>