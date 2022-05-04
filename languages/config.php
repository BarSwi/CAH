<?php
	session_start();

	if(!isset($_SESSION['lang'])){
		$_SESSION['lang'] = "pl";
	}
	if(!empty($_GET['lang'])){
		$_SESSION['lang'] = $_GET['lang'];
	}	
	require_once $_SESSION['lang'].'.php';
?>