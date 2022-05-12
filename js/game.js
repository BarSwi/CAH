
$(window).on('beforeunload', function(){    
        navigator.sendBeacon('../phpscripts/game/exit.php');
});
$(document).ready(function(){

    let time = Date.now();
    if($('.player_before').length >= 3){
        polling(time);
    }
});
function polling(time){
    $.ajax({
        type: 'post',
        data: {time:time},
        url: 'phpscripts/game/game_polling.php',
        success: function(res){

            polling(time);
        },
        error: function(){
            polling(time);
        }
    });
}