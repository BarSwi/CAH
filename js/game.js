function isMobile() {
    if(screen.width<600 && screen.height<1000) return true;
    return false;
} 
//exmerimental mobile treatment
document.addEventListener("visibilitychange", function() {

    if(isMobile()==true){
        polling = function(){};
        navigator.sendBeacon('../phpscripts/game/exit.php');

    }
});
if($('#bottom  h2').text().charAt(0)== "G") hl = "pl";
if($('#bottom  h2').text().charAt(0)== "P") hl = "en";
id = window.location.href.substring(window.location.href.indexOf('=')+1);
$(window).on('beforeunload', function(){    
    polling = function(){};
    navigator.sendBeacon('../phpscripts/game/exit.php');
});
kickflag = true;
$(document).ready(function(){
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
                if(hl=="pl") alert("Zostałeś wyrzucony");
                if(hl=="en") alert("You have been kicked");
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
        var players_in_lobby = $('#players_in_lobby');
        var start = $('#start');
        time = param[param.length-3];
        $('.player_before').remove();
        players_in_lobby.html(param.length-3);
        if(players_in_lobby.html() >= 3){
            start.prop('class', 'active');
        }
        else start.prop('class', 'inactive');
        for(var i = 0; i< param.length-3; i++){
            if(param[i]==owner){
                $('#players').append('<div class = "player_before owner player"><span class = "nick">'+param[i]+'<i class = "icon-crown"></i></span></div>');
            }
            else{
                $('#players').append('<div class = "player_before player"><span class = "nick">'+param[i]+'</span></div>');
            }

        }
        if(window.owner != owner){
            if(owner==window.nick){
                window.owner = window.nick;
                if(hl=="pl") var text = "W celu wystartowania rozgrywki potrzeba conajmniej 3 graczy. Każda poczekalnia zostaje usunięta po godzinie nieaktywności.";
                if(hl=="en") var text = "In order to start the game you need at least 3 players. Each lobby is deleted after one hour of inactivity.";
                $('#middle').html('<div id = "start">START</div><div id = "information">'+text+'</div>');
            }
        }
    polling(time);
    }
}
$(document).on('mouseover', '.player', function(){
    if(kickflag==true && window.owner && window.nick){
        if(window.owner==window.nick){
            if($(this).children('.nick').text()!=window.owner){
                if(hl=="pl") var text = "Wyrzuć";
                if(hl=="en") var text = "Kick";
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