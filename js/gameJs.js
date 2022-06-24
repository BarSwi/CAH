function isMobile() {
    if(screen.width<600 && screen.height<1000) return true;
    return false;
} 
//exmerimental mobile treatment
// document.addEventListener("visibilitychange", function() {
//     if($('#password_check').length==0){
//         if(isMobile()==true){
//             window.unload = true;
//             polling = function(){};
//             navigator.sendBeacon('../phpscripts/game/exit.php');
    
//         }
//     }
// });
id = window.location.href.substring(window.location.href.indexOf('=')+1);
selected_flag = false;

//Sometimes bugged because of exit.php
$(window).on('beforeunload', function(){  
    if($('#password_check').length==0){
        window.unload = true;
        polling = function(){};
        navigator.sendBeacon('../phpscripts/game/exit.php');
    }
});
kickflag = true;
$(document).ready(function(){
    if($('#password_check').length==0){
        window.scrollTo(0, 0);
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
                window.round = result[6];
                window.afk_time = result[7];
                window.afk = 0;
                if(lang=="pl") window.hl = "pl";
                if(lang=="en") window.hl = "en";
                
            }
        });
            polling(0, window.round);
    }
    var timer = $('#timer');
    var end_time = Date.now() + window.afk_time * 1000;
    if(timer.length){
        timerCount(window.afk_time, timer, end_time);
    }
});
$('#password_submit').click(function(){
    $(this).blur();
    var password = $('#password_input');
    if(password.val().length != 0 ){
        $(this).css('pointer-events', 'none');
        $.ajax({
            type: 'post',
            url: '../phpscripts/game/check_password.php',
            data: {password:password.val(), id:id},
            success: function(res){
                setTimeout(function(){
                    ($('#password_submit').removeAttr('style'));
                },2000);
                if(res=="1"){
                    window.location.reload();
                }
                if(res=="0"){
                    password.css('border', '2px solid red');
                }
                if(res=="2"){
                    window.location.replace('/Home');
                }
            }
        });
    }
    else{
        password.css('border', '2px solid red');
    }
});
function polling(time, round){
        $.ajax({
            type: 'post',
            cache: false,
            data: {time:time, id:id, personal_id:window.personal_id, round:round},
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
                if(res.length == 13){
                    polling(res);
                }
                else{
                    let array = JSON.parse(res);
                    polling_res(array);
                }
    
                
            },
            error: function(){
                alert('Connection error please try again');
                window.location.reload();
            }
        }); 
}
$(document).on('mouseover', '.player', function(){
    if(kickflag==true && window.owner && window.nick){
        if(window.owner==window.nick){
            if($(this).children('.player_left').length){
                if($(this).children('.player_left').children('.nick').text()!=window.owner){
                    $(this).append('<div class = "player_menu"><div class = "kick"><i class = "icon-user-times"></i></div><div class = "crown_btn"><i class = "icon-crown"></i></div>');
        
                    kickflag = false;
                }
            }
            else{
                if($(this).children('.nick').text()!=window.owner){
                    $(this).append('<div class = "player_menu"><div class = "kick"><i class = "icon-user-times"></i></div><div class = "crown_btn"><i class = "icon-crown"></i></div>');
        
                    kickflag = false;
                }
            }
        }
    }
})
$(document).on('mouseleave', '.player', function(){
    if(kickflag==false){
        if(window.owner==window.nick){
        $(this).children('.player_menu').remove() ;
        kickflag = true;
        }
    }

})
$(document).on('click', '.kick', function(){
    if(window.owner==window.nick){
        kickflag = true;
        if($(this).parent().siblings('.player_left').length){
            var kick = $(this).parent().siblings('.player_left').children('.nick').text();
        }
        else{
            var kick = $(this).parent().siblings('.nick').text();
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
$(document).on('click', '.crown_btn', function(){
    if(window.owner==window.nick){
        if($(this).parent().siblings('.player_left').length){
            var new_owner = $(this).parent().siblings('.player_left').children('.nick').text();
        }
        else{
            var new_owner = $(this).parent().siblings('.nick').text();
        }
        $.ajax({
            type: 'post',
            data: {id:id, new_owner:new_owner},
            url: '../phpscripts/game/change_owner.php',
            success: function(res){
                window.owner = new_owner;
                if(res=='0'){
                    window.location.reload();
                    return 0;
                }
            }
        });
    }
});
$(document).on('click', '#start', function(){
    $(this).css('pointer-events', 'none');
    $.ajax({
        type: 'post',
        url: '../phpscripts/game/start_game.php',
        success: function(res){
            if(res=="0"){
                alert('error');
                window.location.reload();
            }
        }
    });
});
$(document).on('change','.white_check', function(e){
    if(selected_flag == false){
        let white_cards_shown  = $('#white_cards_shown');
        let text = $(this).parent().text();
        if(window.chooser != window.nick){
            if(window.black > window.selected_cards.length){
                let id = $(this).parent().attr('id');
                $(this).parent().css({'background-color': '#164135', 'opacity': '1', 'color': 'white'});
                window.selected_cards.push(id);
                if($('.shown').length){
                    $('.shown:last-of-type').after('<div  class = "white_card_picked shown '+id+'">'+text+'</div>');
                }
                else{
                    white_cards_shown.prepend('<div  class = "white_card_picked shown '+id+'">'+text+'</div>');
                }
    
                
            }
            else{
                if($(this).prop("checked")==true){
                    let id = window.selected_cards[window.selected_cards.length - 1];
                    $('#' + id).removeAttr('style');
                    $('#' + id).children().prop('checked', false);
                    window.selected_cards = window.selected_cards.slice(0, -1);
                    white_cards_shown.children('.'+id).remove();
                    $(this).parent().css({'background-color': '#164135', 'opacity': '1', 'color': 'white'});
                    id = $(this).parent().attr('id');
                    window.selected_cards.push(id);
                    if($('.shown').length){
                        $('.shown:last-of-type').after('<div  class = "white_card_picked shown '+id+'">'+text+'</div>');
                    }
                    else{
                        white_cards_shown.prepend('<div  class = "white_card_picked shown '+id+'">'+text+'</div>');
                    }
                }
            }
            if($(this).prop("checked")==false){
                $(this).parent().removeAttr('style');
                let id = $(this).parent().attr('id');
                window.selected_cards = window.selected_cards.filter(e => e !== id);
                white_cards_shown.children('.'+id).remove();
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
    if(selected_flag == false){
        if(window.nick == window.chooser){
            let card_id = $(this).attr("class").split(/\s+/)[1];
            let card_handler = $('.'+card_id);
            if($(this).prop('checked')==true){
                if(window.winner.length == 0){
                    card_handler.css({'background-color': '#164135', 'opacity': '1', 'color': 'white'});
                    window.winner.push(card_id);
                }
                else{
                    let remove_card = window.winner;
                    $('.'+remove_card).css({'background-color': '', 'opacity': '', 'color': ''});
                    $('.'+remove_card).prop('checked', false);
                    window.winner = [];
                    card_handler.css({'background-color': '#164135', 'opacity': '1', 'color': 'white'});
                    window.winner.push(card_id);
                }
            }
            else{
                window.winner = window.winner.filter(e => e !== card_id);
                card_handler.css({'background-color': '', 'opacity': '', 'color': ''});
            }
            if(window.winner.length == 1){
                $('#btn').css({'pointer-events': 'auto', 'opacity': '1'});
            }
            else{
                $('#btn').removeAttr('style');
            }
            
        }
        else{
            $(this).css('pointer-events', 'none');
            return 0;
        }
    }
});
$('#reroll').click(function(){
    var btn = $('#btn');
    $('.shown').remove();
    btn.css('pointer-events', 'none');
    $(this).css('pointer-events','none');
    $.ajax({
        url: '../phpscripts/game/reroll_cards.php',
        async: false,
        success: function(res){
            let result = JSON.parse(res);
            if(res=='0'){
                alert('Error');
                $('#reroll').remove(); 
            }
            else{
                window.selected_cards = [];
                my_cards = $('#my_cards');
                $('#reroll').remove();
                btn.css('pointer-events', '');
                my_cards.children().remove();
                for(let i =0;i<result.length;i++){
                    my_cards.append('<label id = "'+result[i][0]+'" class = "white_card">'+result[i][1]+'<input type = "checkbox" id = "check'+result[i][0]+'" class = "white_check"></label>');
                }
            }
        }
    });
});
$('#btn').click(function(){
    var reroll = $('#reroll');
    reroll.css('pointer-events','none');
    setTimeout(function(){
        reroll.css('pointer-events','');
    },1500);
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
                    AjaxSelectedCards(array, window.afk, 0);
                    window.afk = 0;
                }
        }
        else{
            if(window.winner.length != 1){
                $(this).removeAttr('style');
                return;
            }
            else{
                $('.white_card_picked').css('pointer-events','none');
                let winner = JSON.stringify(window.winner);
                $('#btn').css('display', 'none');
                selected_flag = true;
                $.ajax({
                    type: 'post',
                    url: '../phpscripts/game/winner.php',
                    data: {winner:winner, id:id},
                    success: function(res){
                        if(res=='0'){
                            alert('Unexpected Error');
                        
                           
                        }
                        if(res=='1'){
                            $('.'+window.winner[0]).remove();
                            $('#btn').css('display', '');
                            $('.white_card_picked').removeAttr('style');
                            selected_flag = false;
                            window.winner = [];
                            return 0;
                        }
                        if(res=="2"){
                            alert('Unexpected Error');
                            window.location.replace="/Home";
                        }
                    }
                });
            }
        }
    }
});


function polling_res(param){
    if(param[param.length-1]=="players"){
        var owner = param[param.length-3];
        var round = param[param.length-2];
        // players_in_lobby exists only when game_started = 0
        if($('#players_in_lobby').length){
            var players_in_lobby = $('#players_in_lobby');
            var start = $('#start');
            $('.player_before').remove();
            players_in_lobby.html(param.length-4);
            if(players_in_lobby.html() >= 3){
                start.prop('class', 'active');
            }
            else start.prop('class', 'inactive');
            time = param[param.length-4];
            for(var i = 0; i< param.length-4; i++){
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
            // var players exists only when game_started = 1
            var players = $('#players');
            var player_after = $('.player_after');
            const styles = [];
            player_after.each(function(){
                if($(this).css('background-color') == "rgba(35, 255, 71, 0.1)"){
                    styles.push($(this).attr('id'));
                }
            });
            player_after.remove();
            time = param[param.length-4];
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
            for(var i = 0; i< param.length-4; i++){
                if(param[i][2]==1){
                    window.chooser = param[i][0];
                    if(window.hl == "pl") var select = "Wybiera";
                    if(window.hl=="en")   var select = "Selecting";
                    var inline_style = "";
                }
                else{
                    var select = "";
                    if(styles.includes(param[i][0])){
                        var inline_style = 'style = "background-color: rgba(35, 255, 71, 0.1)"';
                    }
                    else var inline_style = '';
                } 
                if(param[i][0]==owner){
                    players.append(`<div class = "player_after player" id = "${param[i][0]}" ${inline_style} ><div class = "player_left"><span class = "nick owner">${param[i][0]}<i class = "icon-crown"></i></span><div class = "points">${points}<span class = "value">${param[i][1]}</span></div></div><div class = "player_right">${select}</div><div style = "clear:both;"></div></div>`);
                }
                else{
                    players.append(`<div class = "player_after player" id = "${param[i][0]}" ${inline_style}><div class = "player_left"><span class = "nick">${param[i][0]}</span><div class = "points">${points}<span class = "value">${param[i][1]}</span></div></div><div class = "player_right">${select}</div><div style = "clear:both;"></div></div>`);
                }
            }
            if(window.chooser == window.nick){
                $('.shown').remove();
                $('#timer').css('display', 'none');
                selected_flag = false;
                $('.white_card_picked').css({'background-color': '', 'opacity': '', 'color': '', 'pointer-events': ''});
                $('#my_cards').css('display','none');
                $('#reroll').css('display', 'none');
                $('#btn').removeAttr('style');
                if(hl=="pl") var text = "W tej rundzie wybierasz wygrywającą kartę.";
                if(hl=="en") var text = "You are selecting a winning card during this round.";
                if(!$('#select_info').length){
                    $('#UI').append('<div id = "select_info">'+text+'</div>');
                }
            }
            polling(time, round);
        }
    }
    if(param[param.length-1]=="game"){
        time = param[param.length-2];
        polling = function(){};
        setTimeout(function(){
            window.location.reload();
        },200)
        

    }
    if(param[param.length-1]=="round"){
        time = param[0];
        var players_selected = param[1];
        var round = param[2];
        let white_cards_cont = $('#white_cards_cont');
        white_cards_cont.children().not('#white_cards_shown').remove();
        for(let k=0; k<players_selected.length;k++){
            let player = $('#'+players_selected[k]);
            let attr = player.attr('style');
            if(typeof attr == 'undefined' || attr == false){
                player.css('background-color', 'rgb(35, 255, 71,.1)');
            }
            white_cards_cont.append('<div class = "white_card_picked"></div>');
        }
        polling(time, round);
    }
    if(param[param.length-1]=="round_end"){
        $('#my_cards').css('display','none');
        $('#reroll').css('display', 'none');
        $('.player').removeAttr('style');
        time = param[param.length-2];
        let white_cards_cont = $('#white_cards_cont');
        white_cards_cont.children().not('#white_cards_shown').remove();   
        let i = param.length-2;
        const colors = ['#FF8C00', '#DC143C', '#C0C0C0', '#9400D3', '#4682B4', '#00FF00', '#DEB887', '#660000', '#FF1493']
        for(let j = 0; j<i; j++){
           let k = param[j].length-1;
           if(window.black>1 && window.black%2==0 && j%2==0){
                if(j!=0){
                    var color = colors[j/2];
                }
                else{
                    var color = colors[j];
                }
           }
           else if(window.black>1 && window.black%3==0 && j%3==0){
                if(j!=0){
                    var color = colors[j/3];
                }
                else{
                    var color = colors[j];
                }
           }
           else if(window.black==1){
                var color = colors[j];
           }
           if(window.chooser != window.nick){
               var style_addon = "pointer-events: none;";
            }
            else var style_addon = '';
            let inline_shadow = `0px 6px 6px -4px ${color}`
            var style = `style = "-webkit-box-shadow: ${inline_shadow}; -moz-box-shadow: ${inline_shadow}; box-shadow: ${inline_shadow}; ${style_addon}"`;
            for(let m = 0; m<k;m++){
                white_cards_cont.append('<label  class = "selected white_card_picked '+param[j][0]+'"'+style+'>'+param[j][m+1]+'<input type = "checkbox" class = "select_check '+param[j][0]+'"></label>');
            }
        }
        polling(time, 0);
    }
    if(param[param.length-1] == 'winner_selected'){
        time = param[0];
        let nick = param[1];
        let card = param[2];
        window.game_status = param[3];
        nick_handler = $('#'+nick);
        nick_handler.addClass('blink');
        let new_value = parseInt(nick_handler.children('.player_left').children('.points').children('.value').text()) +1;
        nick_handler.children('.player_left').children('.points').children('.value').text(new_value);
        $('.'+card).css({'background-color': '#164135', 'color':'white'});
        if(window.game_status == 1){
            if(window.hl = "pl"){
                var text = "Zwycięzca";
            }
            if(window.hl = "en"){
                var text = "Winner";
            }
            nick_handler.children('.player_right').text(text);
          //  window.game_won = true;
        }
        polling(time);
    }
    if(param[param.length-1]=='reset'){
        var owner = param[param.length-2];
        window.black = param[param.length-4];
        var black_value = param[param.length-5];
        setTimeout(function(){
            $('#black_card').text(black_value);
            var players = $('#players');
            $('.player_after').remove();
            time = param[param.length-3];
            if(window.hl = "pl"){
                var points = "Punkty: ";
                var reroll_text = "Przelosuj swoje karty 🎲 (Raz na grę)";
            }
            if(window.hl = "en"){
                var points = "Points: ";
                var reroll_text = "Reroll your cards 🎲 (Once per game)";
            }
            if(window.owner != owner){
                if(owner==window.nick){
                    window.owner = window.nick;
                }
            }
            for(var i = 0; i< param.length-5; i++){
                if(param[i][2]==1){
                    window.chooser = param[i][0];
                    if(window.hl == "pl") var select = "Wybiera";
                    if(window.hl=="en")   var select = "Selecting";
                }
                else var select = "";
                if(param[i][0]==owner){
                    players.append(`<div class = "player_after player" id = "${param[i][0]}"><div class = "player_left"><span class = "nick owner">${param[i][0]}<i class = "icon-crown"></i></span><div class = "points">${points}<span class = "value">${param[i][1]}</span></div></div><div class = "player_right">${select}</div><div style = "clear:both;"></div></div>`);
                }
                else{
                    players.append(`<div class = "player_after player" id = "${param[i][0]}"><div class = "player_left"><span class = "nick">${param[i][0]}</span><div class = "points">${points}<span class = "value">${param[i][1]}</span></div></div><div class = "player_right">${select}</div><div style = "clear:both;"></div></div>`);
                }
            }
            if(window.nick != window.chooser){
                var information = $('#select_info');
                $('.player').removeClass('blink');
                $('#btn').removeAttr('style');
                $('#reroll').removeAttr('style');
                $('#white_cards_cont').children().not('#white_cards_shown').remove();
                $('#my_cards').removeAttr('style');
                $('.white_card').removeAttr('style');
                if(information.length){
                    information.remove();
                }
                if(window.game_status==1){
                    if($('#reroll').length==0){
                        $('#UI').append('<div id = "reroll">'+reroll_text+'</div>');       
                    }
                }
                var timer = $('#timer');
                var end_time = Date.now() + window.afk_time * 1000;
                timerCount(window.afk_time, timer, end_time);
                timer.css('display' ,'');
            }
            else{
                var information = $('#select_info');
                $('#my_cards').css('display','none');
                $('.player').removeClass('blink');
                $('#btn').removeAttr('style');
                $('#reroll').css('display', 'none');
                $('#white_cards_cont').children().not('#white_cards_shown').remove();
                $('.white_card').removeAttr('style');
                if(hl=="pl") var text = "W tej rundzie wybierasz wygrywającą kartę.";
                if(hl=="en") var text = "You are selecting a winning card during this round.";
                if(information.length == 0){
                    $('#UI').append('<div id = "select_info">'+text+'</div>');
                }
                if(window.game_status==1){
                    if($('#reroll').length==0){
                        $('#UI').append('<div id = "reroll" style = "display: none;">'+reroll_text+'</div>');       
                    }
                }
    
            }
            let round = 1;
            polling(time, round);
        },700)
        selected_flag = false;
        window.winner = [];
        window.selected_cards = [];
    }

}

function timerCount(i, timer, end_time){
    var k = i;
    if(selected_flag==false && window.chooser != window.nick){
        function countDown(){
            if(!document.hasFocus()){
                let current_time = Date.now();
                k = Math.ceil((end_time - current_time) / 1000);
            }
            k--;
            timer.text(k);
            if (k > 0) {      
                timerCount(k, timer, end_time);
            }  
            else{
                timer.css('display', 'none');
                var reroll = $('#reroll');
                reroll.css('pointer-events','none');
                setTimeout(function(){
                    reroll.css('pointer-events','');
                },1500);
                window.selected_cards = [];
                $('#btn').css('display', 'none');
                let cards = [];
                for(let m = 0; m<window.black; m++){
                    let card = $('.white_card').eq(m).attr('id');
                    cards.push(card);
                }
                var array = JSON.stringify(cards);
                AjaxSelectedCards(array, window.afk, 1);
                window.afk = 1;
                
            }
        }
        setTimeout(countDown, 1000);
    }
}

function AjaxSelectedCards(array, current_afk_status, new_afk_status){
    $.ajax({
        type: 'post',
        url: '../phpscripts/game/selected_cards.php',
        async: false,
        data: {array:array, id:id, current_afk:current_afk_status, new_afk:new_afk_status},
        success: function(res){
            if(res=="2"){
                alert('Unexpected Error');
                window.chooser = window.nick;
                $('.white_card').css('pointer-events','none');
                $('#my_cards').css('display', 'none');
            }
            if(res=="1"){
                alert('Unexpected Error');
                $('.white_card').css('pointer-events','none');
            }
            if(res=="0"){
                alert('Unexpected Error');
                window.location.reload();
            }
            else{
                let timer = $('#timer');
                timer.text(window.afk_time);
                timer.css('display', 'none');
                let my_cards = $('#my_cards');
                $('.shown').remove();
                $('#white_cards_cont').append('<div class = "white_card_picked"></div>');
                selected_flag = true;
                $('#btn').css('display', 'none');
                let array = JSON.parse(res);
                my_cards.empty();
                for(let i = 0; i<array.length; i++){
                    my_cards.append("<label id = "+array[i][0]+" class = 'white_card'>"+array[i][1]+"<input type = 'checkbox' id = 'check"+array[i][0]+"' class = 'white_check''></label>");
                    
                }
                $('.white_card').css('pointer-events','none');
            }
        }
    });
}