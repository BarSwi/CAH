<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
}
session_write_close();

$time = $_POST['time'];
set_time_limit(30);
ignore_user_abort(false);
$array = [];
try{
    require_once('../connect_users.php');

}
catch(PDOException $e){
    $error_message = $e->getMessage();
    echo $error_message;
}


