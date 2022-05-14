<?php
	ini_set('display_errors','0');
	$haslo = $_POST['haslo'];
	$haslo1 = $_POST['haslo1'];
	if(strlen($haslo)<8) echo 0;
	if($haslo1 != $haslo) echo 0;
	try{
		require_once('connect_users.php');
		$dsn = 'mysql:host='.$host.';dbname='.$db_name;
		$pdo = new PDO($dsn, $db_user, $db_password);
		$selector = $_POST['selector'];
		$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "SELECT * FROM password_reset WHERE password_reset_selector = '$selector'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$email = $row['reset_email'];
		$hashedPassword = password_hash($haslo, PASSWORD_DEFAULT);
		$sql = "UPDATE users SET password = '$hashedPassword' WHERE BINARY email = :email";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['email' => $email]);
		$sql = "DELETE FROM password_reset WHERE password_reset_selector = '$selector'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		echo "1";
	}catch(PDOException $e)
	{
			$error_message = $e->getMessage();
			echo $error_message;
	}