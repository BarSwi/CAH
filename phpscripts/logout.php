<?php
ini_set('display_errors','0');
session_start();
    require_once('connect_users.php');
   
    if($_SESSION['game']=true){
        try{
            $dsn = "mysql:host=".$host.";dbname=".$db_name;
            $pdo = new PDO($dsn, $db_user, $db_password);
            $last_change = floor(microtime(true) * 1000);
            $nick = $_SESSION['user'];
            $pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->beginTransaction();
            $sql = "DELETE FROM players_in_lobby WHERE nick = '$nick'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $sql = "UPDATE lobby SET players_in_lobby = players_in_lobby-1 WHERE lobby_id = '$lobby_id'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $sql = "UPDATE lobby SET last_change_players = '$last_change' WHERE lobby_id = '$lobby_id'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $pdo->commit();
            $_SESSION['game']= false;
        }
        catch(PDOException $e){
            $pdo->rollBack();
            $error_message = $e->getMessage();
        
        }
}

session_destroy();