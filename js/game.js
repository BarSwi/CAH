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
selected_flag = false;

//Sometimes bugged because of exit.php

$(window).on('beforeunload', function(){  
    window.unload = true;
    polling = function(){};
    navigator.sendBeacon('../phpscripts/game/exit.php');


});
kickflag = true;
$(document).ready(function(){
    window.selected_cards = [];
    window.winner = [];
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
                if(res[0] == "<"){
                    polling();
                }
                if(res[0]== 1){
                    polling();
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
$(document).on('mouseover', '.player', function(){
    if(kickflag==true && window.owner && window.nick){
        if(window.owner==window.nick){
            if($(this).children('.player_left').length){
                if($(this).children('.player_left').children('.nick').text()!=window.owner){
                    if(window.hl=="pl") var text = "Wyrzuć";
                    if(window.hl=="en") var text = "Kick";
                    $(this).append('<div class = "kick">'+text+'</div>');
        
                    kickflag = false;
                }
            }
            else{
                if($(this).children('.nick').text()!=window.owner){
                    if(window.hl=="pl") var text = "Wyrzuć";
                    if(window.hl=="en") var text = "Kick";
                    $(this).append('<div class = "kick">'+text+'</div>');
        
                    kickflag = false;
                }
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
        kickflag = true;
        if($(this).siblings('.player_left').length){
            var kick = $(this).siblings('.player_left').children('.nick').text();
        }
        else{
            var kick = $(this).siblings('.nick').text();
        }
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
$(document).on('click', '#start', function(){
    $.ajax({
        type: 'post',
        url: '../phpscripts/game/start_game.php',
        success: function(res){
            if(res=="0"){
                return 0;
            }
        }
    });
});
$(document).on('change','.white_check', function(e){
    if(selected_flag == false){
        let white_cards_cont  = $('#white_cards_cont');
        let text = $(this).parent().text();
        if(window.chooser != window.nick){
            if(window.black > window.selected_cards.length){
                let id = $(this).parent().attr('id');
                $(this).parent().css({'background-color': '#8FE738', 'opacity': '1'});
                window.selected_cards.push(id);
                white_cards_cont.append('<div  class = "white_card_picked shown '+id+'">'+text+'</div>');
    
                
            }
            else{
                if($(this).prop("checked")==true){
                    let id = window.selected_cards[window.selected_cards.length - 1];
                    $('#' + id).removeAttr('style');
                    $('#' + id).children().prop('checked', false);
                    window.selected_cards = window.selected_cards.slice(0, -1);
                    white_cards_cont.children('.'+id).remove();
                    $(this).parent().css({'background-color': '#8FE738', 'opacity': '1'});
                    id = $(this).parent().attr('id');
                    window.selected_cards.push(id);
                    white_cards_cont.append('<div  class = "white_card_picked shown '+id+'">'+text+'</div>');
                }
            }
            if($(this).prop("checked")==false){
                $(this).parent().removeAttr('style');
                let id = $(this).parent().attr('id');
                window.selected_cards = window.selected_cards.filter(e => e !== id);
                white_cards_cont.children('.'+id).remove();
            }
            if(window.black == window.selected_cards.length){
                $('#btn').css({'pointer-events': 'auto', 'opacity': '1'});
            }
            else $('#btn').removeAttr('style'); 
        }
        else{
            e.preventDefault();
        }
    }
    else{
        e.preventDefault();
        return 0;
    }
});
$(document).on('change', '.select_check', function(){
    if(window.nick == window.chooser){
        let card_id = $(this).attr("class").split(/\s+/)[1];
        let card_handler = $('.'+card_id);
        if($(this).prop('checked')==true){
            if(window.winner.length == 0){
                card_handler.css({'background-color': '#8FE738', 'opacity': 1});
                window.winner.push(card_id);
            }
            else{
                let remove_card = window.winner;
                $('.'+remove_card).removeAttr('style');
                $('.'+remove_card).prop('checked', false);
                window.winner = [];
                card_handler.css({'background-color': '#8FE738', 'opacity': 1});
                window.winner.push(card_id);
            }
        }
        else{
            window.winner = window.winner.filter(e => e !== card_id);
            card_handler.removeAttr('style');
        }
        if(window.winner.length == 1){
            $('#btn').css({'pointer-events': 'auto', 'opacity': '1'});
        }
        else{
            $('#btn').removeAttr('style');
        }
        
    }
    else{
        //  $(this).css('pointer-events', 'none');
        //  return 0;
    }
});
$('#btn').click(function(){
    if(selected_flag==false){
            if(window.nick != window.chooser){
                if(window.selected_cards.length == 0 ){
                    $(this).removeAttr('style');
                    return;
                }
                if(window.selected_cards.length < window.black){
                    $(this).removeAttr('style');
                    return;
                }
                if(window.selected_cards.length == window.black ){
                    let array = JSON.stringify(window.selected_cards);
                    for(let i = 0; i<window.selected_cards.length; i++){
                        $('#'+window.selected_cards[i]).remove();
                    }
                    window.selected_cards = [];
                    $.ajax({
                        type: 'post',
                        url: '../phpscripts/game/selected_cards.php',
                        async: false,
                        data: {array:array, id:id},
                        success: function(res){
                            if(res=="0"){
                                alert('error');
                                window.location.reload();
                            }
                            else{
                                selected_flag = true;
                                $('#btn').css('display', 'none');
                                let array = JSON.parse(res);
                                for(let i = 0; i<array.length; i++){
                                    $('#my_cards').append("<label id = "+array[i][0]+" class = 'white_card'>"+array[i][1]+"<input type = 'checkbox' id = 'check"+array[i][0]+"' class = 'white_check''></label>");
                                    
                                }
                                $('.white_card').css('pointer-events','none');
                            }
                        }
                    })
                }
        }
        else{
            if(window.winner.length != 1){
                $(this).removeAttr('style');
                return;
            }
            else{
                let winner = JSON.stringify(window.winner);
                $.ajax({
                    type: 'post',
                    url: '../phpscripts/game/winner.php',
                    data: {winner:winner, id:id},
                    success: function(res){
                        if(res=='0'){
                            alert('error');
                        }
                        else{
                            $('#btn').css('display', 'none');
                            selected_flag = true;
                        }
                    }
                });
            }
        }
    }
});


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
                    if(players_in_lobby.html() >= 3){
                        var className = "class = 'active'";
                    }
                    else var className = "class = 'inactive'";
                    if(window.hl=="pl") var text = "W celu wystartowania rozgrywki potrzeba conajmniej 3 graczy. Każda poczekalnia zostaje usunięta po godzinie nieaktywności.";
                    if(window.hl=="en") var text = "In order to start the game you need at least 3 players. Each lobby is deleted after one hour of inactivity.";
                    $('#middle').html('<div id = "start" '+className+'>START</div><div id = "information">'+text+'</div>');
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
            if(window.owner != owner){
                if(owner==window.nick){
                    window.owner = window.nick;
                }
            }
            for(var i = 0; i< param.length-3; i++){
                if(param[i][2]==1){
                    window.chooser = param[i][0];
                    if(window.hl = "pl") var select = "Wybiera";
                    if(window.hl="en")   var select = "Selecting";
                }
                else var select = "";
                if(param[i][0]==owner){
                    players.append('<div class = "player_after player" id = "'+param[i][0]+'"><div class = "player_left"><span class = "nick owner">'+param[i][0]+'<i class = "icon-crown"></i></span><div class = "points">'+points+'<span class = "value">'+param[i][1]+'</span></div></div><div class = "player_right">'+select+'</div><div style = "clear:both;"></div></div>');
                }
                else{
                    players.append('<div class = "player_after player" id = "'+param[i][0]+'"><div class = "player_left"><span class = "nick">'+param[i][0]+'</span><div class = "points">'+points+'<span class = "value">'+param[i][1]+'</span>    </div></div><div class = "player_right">'+select+'</div><div style = "clear:both;"></div></div>');
                }
            }
            if(window.chooser == window.nick){
                $('#my_cards').css('display','none');
                if(hl=="pl") var text = "W tej rundzie wybierasz wygrywającą kartę.";
                if(hl=="en") var text = "You are selecting a winning card during this round.";
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
    if(param[param.length-1]=="round"){
        time = param[0];
        let i = param[1];
        let white_cards_cont = $('#white_cards_cont');
        white_cards_cont.children().remove();
        for(let k=0; k<i;k++){
            white_cards_cont.append('<div class = "white_card_picked"></div>');
        }
        polling(time);
    }
    if(param[param.length-1]=="round_end"){
        time = param[param.length-2];
        if(window.chooser != window.nick){
            var style = 'style = "pointer-events: none;"';
        }
        else var style = '';
        let white_cards_cont = $('#white_cards_cont');
        white_cards_cont.children().remove();   
        let i = param.length-2;
        paramShuffled = param.slice(0, param.length-2).sort((a, b) => 0.5 - Math.random());
        for(let j = 0; j<i; j++){
           let k = param[j].length-1;
           for(let m = 0; m<k;m++){
                white_cards_cont.append('<label  class = "selected white_card_picked '+paramShuffled[j][0]+'"'+style+'>'+paramShuffled[j][m+1]+'<input type = "checkbox" class = "select_check '+paramShuffled[j][0]+'"></label>');
           }
        }
        polling(time);
    }
    if(param[param.length-1] == 'winner_selected'){
        time = param[0];
        let nick = param[1];
        let card = param[2];
        nick_handler = $('#'+nick);
        nick_handler.css('background-color', 'green');
        let new_value = parseInt(nick_handler.children('.player_left').children('.points').children('.value').text()) +1;
        nick_handler.children('.player_left').children('.points').children('.value').text(new_value);
        $('.'+card).css('background-color', 'green');
        polling(time);
    }
    if(param[param.length-1]=='reset'){
        time = param[0];
        window.chooser = param[1];
        alert(param[1]);
        if(window.nick != window.chooser){
            let information = $('#select_info');
            $('.player').removeAttr('style');
            $('#btn').removeAttr('style');
            $('#white_cards_cont').children().remove();
            $('.white_card').removeAttr('style');
            if(information.length){
                $('#my_cards').removeAttr('style');
                information.remove();
            }
        }
        else{
            $('#my_cards').css('display','none');
            $('.player').removeAttr('style');
            $('#btn').removeAttr('style');
            $('#white_cards_cont').children().remove();
            $('.white_card').removeAttr('style');
            if(hl=="pl") var text = "W tej rundzie wybierasz wygrywającą kartę.";
            if(hl=="en") var text = "You are selecting a winning card during this round.";
            $('#UI').append('<div id = "select_info">'+text+'</div>');

        }
        selected_flag = false;
        polling(time);
    }

}