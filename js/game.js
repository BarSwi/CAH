function isMobile() {
    if(screen.width<600 && screen.height<1000) return true;
    return false;
} 
//exmerimental mobile treatment
document.addEventListener("visibilitychange", function() {

    if(isMobile()==true){
        window.unload = true;
        polling = function(){};
        navigator.sendBeacon('../phpscripts/game/exit.php');

    }
});
id = window.location.href.substring(window.location.href.indexOf('=')+1);

//Sometimes bugged because of exit.php

$(window).on('beforeunload', function(){  
    window.unload = true;
    polling = function(){};
    navigator.sendBeacon('../phpscripts/game/exit.php');


});
kickflag = true;
$(document).ready(function(){
    window.selected_cards = [];
    $.ajax({
        type: 'post',
        url: 'phpscripts/game/check_id.php',
        data: {id:id},
        async: false,
        success: function(res){
            var result = JSON.parse(res);
            window.personal_id = result[0];
            window.nick = result[1];
            window.owner = result[2];
            lang = result[3];
            window.black = result[4];
            window.chooser = result[5];
            if(lang=="pl") window.hl = "pl";
            if(lang=="en") window.hl = "en";
            
        }
    });
        polling();
});
function polling(time){
        $.ajax({
            type: 'post',
            cache: false,
            data: {time:time, id:id, personal_id:window.personal_id},
            url: 'phpscripts/game/game_polling.php',
            success: function(res){
                if(window.unload == true) return 0;
                if(res=="0"){
                    window.location = "index.php";
                    return 0;
                }
                if(res == "1"){
                    polling();
                }
                if(res[0] == "<"){
                    polling();
                }
                if(res[0]== 1){
                    polling(res);
                }
                if(res=="kick"){
                    if(window.hl=="pl") alert("Zostałeś wyrzucony");
                    if(window.hl=="en") alert("You have been kicked");
                    window.location = "index.php";
                    return 0;
                }
                else{
                    let array = JSON.parse(res);
                    polling_res(array);
                }
    
                
            },
            error: function(){
                polling();
            }
        }); 
}
function polling_res(param){
    if(param[param.length-1]=="players"){
        var owner = param[param.length-2];
        if($('#players_in_lobby').length){
            var players_in_lobby = $('#players_in_lobby');
            var start = $('#start');
            $('.player_before').remove();
            players_in_lobby.html(param.length-3);
            if(players_in_lobby.html() >= 3){
                start.prop('class', 'active');
            }
            else start.prop('class', 'inactive');
            time = param[param.length-3];
            for(var i = 0; i< param.length-3; i++){
                if(param[i][0]==owner){
                    $('#players').append('<div class = "player_before owner player"><span class = "nick">'+param[i][0]+'<i class = "icon-crown"></i></span></div>');
                }
                else{
                    $('#players').append('<div class = "player_before player"><span class = "nick">'+param[i][0]+'</span></div>');
                }
            }
            if(window.owner != owner){
                if(owner==window.nick){
                    window.owner = window.nick;
                    if(window.hl=="pl") var text = "W celu wystartowania rozgrywki potrzeba conajmniej 3 graczy. Każda poczekalnia zostaje usunięta po godzinie nieaktywności.";
                    if(window.hl=="en") var text = "In order to start the game you need at least 3 players. Each lobby is deleted after one hour of inactivity.";
                    $('#middle').html('<div id = "start" class = "inactive">START</div><div id = "information">'+text+'</div>');
                }
            }
            polling(time);
        }
        else{
            var players = $('#players');
            $('.player_after').remove();
            time = param[param.length-3];
            if(window.hl = "pl"){
                var points = "Punkty: ";
            }
            if(window.hl = "en"){
                var points = "Points: ";
            }
            for(var i = 0; i< param.length-3; i++){
                if(param[i][2]==1){
                    if(window.hl = "pl") var select = "Wybiera";
                    if(window.hl="en")   var select = "Selecting";
                }
                else var select = "";
                if(param[i][0]==owner){
                    players.append('<div class = "player_after player"><div class = "player_left"><span class = "nick owner">'+param[i][0]+'<i class = "icon-crown"></i></span><div class = "points">'+points+param[i][1]+'</div></div><div class = "player_right">'+select+'</div><div style = "clear:both;"></div></div>');
                }
                else{
                    players.append('<div class = "player_after player"><div class = "player_left"><span class = "nick">'+param[i][0]+'<i class = "icon-crown"></i></span><div class = "points">'+points+param[i][1]+'</div></div><div class = "player_right">'+select+'</div><div style = "clear:both;"></div></div>');
                }
                // <div class = "player_after player"><div class = "player_left"><span '.$class.'>'.$player['nick'].$icon.'</span>
				// <div class = "point">'.$lang['points'].$player['points'].'</div>
				// </div>
				// <div class = "player_right">';
				// if($player['chooser']==1) echo $lang['Selecting'];
				// echo '</div>
				// <div style = "clear:both;"></div>
				// </div>';
            }
            polling(time);
        }
    }
    if(param[param.length-1]=="game"){
        time = param[param.length-2];
        polling = function(){};
        setTimeout(function(){
            window.location.reload();
        }, 500);

    }

}
$(document).on('mouseover', '.player', function(){
    if(kickflag==true && window.owner && window.nick){
        if(window.owner==window.nick){
            if($(this).children('.nick').text()!=window.owner){
                if(window.hl=="pl") var text = "Wyrzuć";
                if(window.hl=="en") var text = "Kick";
                if($(this).children().length < 2) {
                    $(this).append('<div class = "kick">'+text+'</div>');
                }
    
                kickflag = false;
            }
        }
    }
})
$(document).on('mouseleave', '.player', function(){
    if(kickflag==false){
        if(window.owner==window.nick){
        $(this).children('.kick').remove() ;
        kickflag = true;
        }
    }

})
$(document).on('click', '.kick', function(){
    if(window.owner==window.nick){
        var kick = $(this).siblings('.nick').text();
        $(this).parent().remove();
        $.ajax({
            type: 'post',
            data: {id:id, kick:kick},
            url: '../phpscripts/game/kick.php',
            success: function(res){
                if(res=="0"){
                    window.location = "index.php";
                    return 0;
                }
            }
        });
    }
});
$('#start').click(function(){
    $.ajax({
        type: 'post',
        url: '../phpscripts/game/start_game.php',
        success: function(res){
            alert(res);
            if(res=="0"){
                alert('error');
                return 0;
            }
        }
    });
});
$(document).on('change','.white_check', function(e){

    if(window.chooser == window.nick){
        if(window.black > window.selected_cards.length){
            $(this).parent().css({'background-color': '#8FE738', 'opacity': '1'});
            window.selected_cards.push($(this).parent().attr('id'));
            
        }
        else{
            if($(this).prop("checked")==true){
                let id = window.selected_cards[window.selected_cards.length - 1];
                $('#' + id).removeAttr('style');
                $('#' + id).children().prop('checked', false);
                window.selected_cards = window.selected_cards.slice(0, -1);
                $(this).parent().css({'background-color': '#8FE738', 'opacity': '1'});
                window.selected_cards.push($(this).parent().attr('id'));
            }
        }
        if($(this).prop("checked")==false){
            $(this).parent().removeAttr('style');
            window.selected_cards = window.selected_cards.filter(e => e !== $(this).parent().attr('id'));
        }
        if(window.black == window.selected_cards.length){
            $('#btn').css({'pointer-events': '', 'opacity': '1'});
        }
        else $('#btn').removeAttr('style'); 
    }
    else{
        e.preventDefault();
    }
});