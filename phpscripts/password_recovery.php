<?php
include "../languages/config.php";
$email = $_POST['email'];
try{
	require_once("connect_users.php");
	$dsn = 'mysql:host='.$host.';dbname='.$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "SELECT * FROM users WHERE BINARY email = :email";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['email' => $email]);
	if($stmt->rowCount()!=1){
		echo "0";
		exit();
	}
}
catch(PDOException $e){
	$error_message = $e->getMessage();
	echo $error_message;
}
require_once("connect_users.php");
try{
$currentDate = date("U");
$sql = "SELECT * FROM password_reset WHERE BINARY reset_email = :email AND password_reset_expires >= $currentDate";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email'=>$email]);
if($stmt->rowCount()!=0){
	echo "2";
	exit();
}
$selector = bin2hex(random_bytes(8));
$dsn = 'mysql:host='.$host.';dbname='.$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$token = random_bytes(32);
$expires = date("U") + 1800;
echo "1";
$sql = "DELETE FROM password_reset WHERE BINARY reset_email = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$sql = "INSERT INTO	password_reset (reset_email, password_reset_selector, password_reset_token, password_reset_expires) VALUES (:email, :selector, :token, :expires)";
$stmt = $pdo->prepare($sql);

$hashedToken = password_hash($token, PASSWORD_DEFAULT);
$stmt->execute(['email' => $email, 'selector' => $selector, 'token' => $hashedToken, 'expires' => $expires]);
$sql = "SELECT * FROM users WHERE BINARY email = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$nick = $row['login'];
$to = $email;
$url = "localhost/password_reset.php?selector=".$selector."&validator=".bin2hex($token);
$subject = $lang['reset_password_subject'];
$message = $lang['reset_password_mail_message_nick']." ".$nick;
$message .= $lang['reset_password_mail_message'].'<a href = "'.$url.'">'.$url.'</a></p>';
$headers = "From: Localhost \r\n Reply-To: localhost \r\n";
$headers .= "Content-type: text/html\r\n";
mail($to, $subject, $message, $headers);
}
catch(PDOException $e){
	$error_message = $e->getMessage();
	echo $error_message;
}

