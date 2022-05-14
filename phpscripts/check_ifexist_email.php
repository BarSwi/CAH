<?php
ini_set('display_errors','0');
$email = $_POST['email'];

try{
	require_once("connect_users.php");
	$dsn = 'mysql:host='.$host.';dbname='.$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$sql = "SELECT * FROM users WHERE BINARY email = :email";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['email' => $email]);
	if($stmt->rowCount()>0) echo "0";
}
catch(PDOException $e){
	$error_message = $e->getMessage();
	echo $error_message;
}