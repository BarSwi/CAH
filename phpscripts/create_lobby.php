<?php
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
}
require_once "connect_users.php";
$deck_id = $_POST['code'];
$dsn = "mysql:host=".$host.';dbname='.$db_name;
$pdo = new PDO($dsn, $db_user, $db_password);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
try{
    $sql = "SELECT * FROM decks WHERE BINARY deck_code = :deck_code";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['deck_code' => $deck_id]);
    $row = $stmt->fetch();
    if ($_SESSION['user'] == $row['author']) $author = 1;
    else $author = 0;
    if($stmt->rowCount()!=0){
        $array=[
            "deck_id" => $row['deck_code'],
            "title" => $row['deck_title'],
            "white_cards"=>$row['white_cards'],
            "black_cards" => $row['black_cards'],
            "author" => $author
        ];
        echo json_encode($array);
        exit();
    }
    else echo "2";

}
catch(PDOException $e){
    $error_message = $e->getMessage();
    echo $error_message;

}