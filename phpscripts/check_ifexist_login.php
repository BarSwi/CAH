<?php
ini_set('display_errors','0');

try{
	$login = $_POST['login'];
	require_once("connect_users.php");
	$dsn = 'mysql:host='.$host.';dbname='.$db_name;
	$pdo = new PDO($dsn, $db_user, $db_password);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "SELECT * FROM users WHERE BINARY login = :login";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['login' => $login]);
	if($stmt->rowCount()>0) echo "0";
}
catch(PDOException $e){
	$error_message = $e->getMessage();
}