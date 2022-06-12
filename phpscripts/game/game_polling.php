<?php
ini_set('max_execution_time', 4000);
session_start();
if(!isset($_SESSION['login']) || $_SESSION['login']==false){
    echo "0";
    exit();
}
$nick = $_SESSION['user'];

session_write_close();

if(empty($_POST['time'])){
    $time = floor(microtime(true) * 1000);
}
else $time = $_POST['time'];
$id = $_POST['id'];
$player = $_POST['personal_id'];
$array_exit = [];

try{


    require_once('../connect_users.php');
    $dsn = "mysql:host=".$host.";dbname=".$db_name;
    $pdo = new PDO($dsn, $db_user, $db_password);
    $pdo->SetAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $sql = "SELECT * FROM users WHERE id = :personal_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['personal_id' => $player]);
    if($stmt->rowCount()==0){
        echo "0";
        exit();
    }
    $row = $stmt->fetch();
    $nick = $row['login'];
    $sql = "SELECT * FROM players_in_lobby WHERE nick = '$nick' AND lobby_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id'=>$id]);
    if($stmt->rowCount()==0){
        $sql = "INSERT INTO players_in_lobby (nick, lobby_id, points, chooser, last_change_players) VALUES ('$nick', :id, 0, false, :time_change)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=> $id, 'time_change' => $time]);
        $sql = "UPDATE lobby SET last_change_players = '$time', players_in_lobby = players_in_lobby+1 WHERE lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
    }
    $counter = 0;
    if(empty($_POST['round'])){
        $sql = "SELECT * FROM lobby WHERE lobby_id =  :id";
        $stmt =  $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $row = $stmt->fetch();
        $round_started = $row['round_started'];
        $max_points = $row['lobby_points_limit'];
        if($stmt->rowCount() == 0){
            echo "0";
            exit();
        }
    }
    else{
        $round_started = $_POST['round'];
    } 
    while(true){
        if(!isset($time_res)){
            $time_res = floor(microtime(true)*1000);
        }
        $sql = "SELECT * FROM players_in_lobby WHERE nick = '$nick' AND lobby_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        if($stmt->rowCount()==0){
            echo "0";
            exit();
        }
        $counter += 1;
        $sql = "SELECT * FROM lobby WHERE lobby_id =  :id";
        $stmt =  $pdo->prepare($sql);
        $stmt->execute(['id'=>$id]);
        $reset = $stmt->fetch();
        $reset = $reset['reset'];
        if($stmt->rowCount() == 0){
            echo "0";
            exit();
        }
        $sql = "SELECT * FROM lobby WHERE (last_change > :time_change OR last_change_players > :time_change2 OR last_change_round > :time_change3) AND lobby_id = :id";
        $stmt =  $pdo->prepare($sql);
        $stmt->execute(['time_change'=>$time,'time_change2'=>$time,'time_change3'=>$time, 'id'=>$id]);
        if($stmt->rowCount()>0){
            $time_change = $stmt->fetch();
            if($time_change['last_change']>$time){
                array_push($array_exit, $time_res, 'game');
                echo json_encode($array_exit);
                exit();
            }
            if($time_change['last_change_round']>$time){
                if($time_change['last_change_players'] <= $time || $time_change['last_change_round']< $time_change['last_change_players'])
                {
                    $time_res = $time_change['last_change_round'];
                    if($round_started==1){
                        $players = $time_change['players_in_lobby'];
                        $sql = "SELECT * FROM cards_in_lobby WHERE color = 'white' AND choosen = 1 AND lobby_id = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute(['id'=>$id]);
                        if($stmt->rowCount()!=$players-1 || $players<3){
                            array_push($array_exit, $time_res, $stmt->rowCount(), 1, 'round');
                            echo json_encode($array_exit);
                            exit();
                        }
                        else{
                            $sql = "SELECT * FROM cardsShuffled WHERE lobby_id = :id ORDER BY `cardsShuffled`.`owner` DESC, `cardsShuffled`.`choosen` ASC";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute(['id'=>$id]);
                            if($stmt->rowCount()==0){
                                usleep(100000);
                                $sql = "SELECT * FROM cardsShuffled WHERE lobby_id = :id ORDER BY `cardsShuffled`.`owner` DESC, `cardsShuffled`.`choosen` ASC";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute(['id'=>$id]);
                            }
                            $cards = $stmt->fetchAll();
                            foreach($cards as $card){
                                $owner = $card['owner'];
                                $card_id = $card['ID'];
                                $choosen = $card['choosen'];
                                if(isset($last_owner) && $last_owner == $owner && isset($last_choosen) && $last_choosen == $choosen)
                                {
                                    continue;
                                }
                                if(!isset($last_choosen)) $last_choosen = $choosen;
                                if(!isset($last_owner)) $last_owner = $owner;
                                if(!isset($last_card)) $last_card = $card_id;
                                if($last_owner == $owner){
                                    $card_id = $last_card;
                                }
                                else{
                                    $last_owner = $owner;
                                    $last_card = $card_id;
                                }
                                $last_choosen = $choosen;
                                $array_inside = [];
                                array_push($array_inside, $card_id, $card['value']);
                                array_push($array_exit, $array_inside);
                            }
                            array_push($array_exit, $time_res, 'round_end');
                            echo json_encode($array_exit);
                            exit();
                        }
                    }
                    else if($round_started == 0){
                        if($reset==0){
                            $sql = "SELECT * FROM cardsShuffled WHERE winner = 1 AND lobby_id = :id";
                            $stmt= $pdo->prepare($sql);
                            $stmt->execute(['id'=>$id]);
                            if($stmt->rowCount()==0){
                                echo $time_res;
                                exit();
                            }
                            $winner = $stmt->fetch();
                            $winner_nick = $winner['owner'];
                            $winner_card = $winner['ID'];
                            $sql = "SELECT * FROM players_in_lobby WHERE lobby_id = :id AND points = $max_points";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute(['id'=>$id]);
                            if($stmt->rowCount()==1){
                                $game_end = 1;
                            }
                            else $game_end = 0;
                            array_push($array_exit, $time_res, $winner_nick, $winner_card, $game_end, 'winner_selected');
                            echo json_encode($array_exit);
                            exit();
                        }
                        else if ($reset==1){
                            $sql = "SELECT * FROM cards_in_lobby WHERE lobby_id = :id AND color = 'black' AND choosen = 1 LIMIT 1";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute(['id'=>$id]);
                            $row = $stmt->fetch();
                            $black_card_val = $row['value'];
                            $black_card_blank = $row['blank_space'];
                            $sql = "SELECT * FROM players_in_lobby WHERE lobby_id = :id ORDER BY `players_in_lobby`.`ID` ASC";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute(['id'=>$id]);
                            $array = $stmt->fetchAll();
                            $sql = "SELECT * FROM lobby WHERE lobby_id = :id";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute(['id'=>$id]);
                            $row = $stmt->fetch();
                            $owner = $row['owner'];
                            foreach($array as $player){
                                array_push($array_exit, [$player['nick'], $player['points'], $player['chooser']]);
                            }
                            array_push($array_exit,$black_card_val, $black_card_blank, $time_res, $owner, "reset");
                            echo json_encode($array_exit);
                            exit();
                        }
                    }
                }
                
            }
            if($time_change['last_change_players']>$time){
                if($time_change['last_change_round']<=$time || $time_change['last_change_players'] < $time_change['last_change_round']){
                    $time_res = $time_change['last_change_players'];
                    $sql = "SELECT * FROM players_in_lobby WHERE lobby_id = :id ORDER BY `players_in_lobby`.`ID` ASC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['id'=>$id]);
                    $array = $stmt->fetchAll();
                    $sql = "SELECT * FROM lobby WHERE lobby_id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['id'=>$id]);
                    $row = $stmt->fetch();
                    $owner = $row['owner'];
                    foreach($array as $player){
                        array_push($array_exit, [$player['nick'], $player['points'], $player['chooser']]);
                    }
                    array_push($array_exit, $time_res, $owner, $round_started, "players");
                    echo json_encode($array_exit);
                    exit();
                }

            }
            else{
                echo "0";
                exit();
            }
        }
        if($counter == 3600){
            echo $time;
            exit();
        }
        $time_res = floor(microtime(true)*1000);
        usleep(600000);
    }

}
catch(PDOException $e){
    $error_message = $e->getMessage();
    echo $error_message;
    
}