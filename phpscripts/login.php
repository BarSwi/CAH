<?php
ini_set('display_errors','0');
session_start();
try{
	$login = $_POST['login'];
	$haslo = $_POST['haslo'];
	require_once('connect_users.php');
	$dsn = "mysql:host=".$host.";dbname=".$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "SELECT * FROM users WHERE BINARY login = :login";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['login' => $login]);
	if($stmt->rowCount()==1){
		$log = $stmt->fetch(PDO::FETCH_ASSOC);
		if(password_verify($haslo, $log['password'])){
			$_SESSION['user'] = $log['login'];
			$_SESSION['login'] = true;
			$sql = "UPDATE users SET Last_login = now() WHERE BINARY login = '$login'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			unset($_SESSION['login_error']);
			unset($_SESSION['error_count']);
		}
		else{
			$_SESSION['login_error'] = true;
		}
		
	}
	else{
		$_SESSION['login_error'] = true;	}
}catch(PDOException $e){
	$error_message = $e->getMessage();
	echo $error_message;
}
