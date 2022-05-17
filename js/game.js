id = window.location.href.substring(window.location.href.indexOf('=')+1);
time = Date.now()-17000;
$(window).on('beforeunload', function(){    
    polling = function(){};
    navigator.sendBeacon('../phpscripts/game/exit.php');
});
$(document).ready(function(){
    setTimeout(function(){
        polling(time);
    },1000);

});
function polling(time){
    $.ajax({
        type: 'post',
        data: {time:time, id:id},
        url: 'phpscripts/game/game_polling.php',
        success: function(res){
            if(res=="0"){
                window.location = "index.php";
                return 0;
            }
            if(res == "1"){
                polling(time);
            }
            if(res[0] == "<"){
                polling(time);
            }
            else{
                let array = JSON.parse(res);
                polling_res(array);
            }

            
        },
        error: function(){
            polling(time);
        }
    });
}
function polling_res(param){
    if(param[param.length-1]=="players"){
        time = param[param.length-2];
        $('.player_before').remove();
        for(var i = 0; i< param.length-3; i++){
            $('#players').append('<div class = "player_before">'+param[i]+'<div class = "kick">test</div>');
        }
    polling(time);
    }
}