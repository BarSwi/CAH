$('#form').click(function(event){
    event.preventDefault();
});
added_decks = [];
min_amount_white_cards = $('#min_amount_white_cards');
white_cards = $('#white_cards');
min_amount_black_cards = $('#min_amount_black_cards');
black_cards = $('#black_cards');
current_white_cards = $('#current_white_cards');
current_black_cards = $('#current_black_cards');

$('.add_my_deck_btn').click(function() {
    var added_decks_list = $('#added_decks_list');
    var deck_id =  $(this).parents().eq(1).siblings().children(".deck_id").text();
    var title =  $(this).parents().eq(1).siblings().children(".deck_title").text();
    var white_cards = $(this).parents().eq(1).siblings().children(".white_cards").text();
    var black_cards = $(this).parents().eq(1).siblings().children(".black_cards").text();
    for(var i =0; i<added_decks.length; i++){   
        if (deck_id == added_decks[i]) return 0;
    }
    added_decks.push(deck_id);
    $(this).css({'pointer-events': 'none', 'opacity': '60%'});   
    added_decks_list.children('#child').css('display', 'none');
    $('#added_decks_table').append('<tr><td class = "deck_id">'+deck_id+'</td><td class = "deck_title">'+title+'</td><td class = "white_cards">'+white_cards+'</td><td class = "black_cards">'+black_cards+'</td><td><div id ="delete_added_deck_btn">x</div></td></tr>');
    current_black_cards_int = parseInt(current_black_cards.text());
    current_black_cards_res = parseInt(black_cards) + current_black_cards_int;
    current_black_cards.html(current_black_cards_res);
    current_white_cards_int = parseInt(current_white_cards.text());
    current_white_cards_res = parseInt(white_cards) + current_white_cards_int;
    current_white_cards.html(current_white_cards_res);
    if(parseInt(min_amount_white_cards.text()) < current_white_cards_res) {
        white_cards.css('color','green');
    }  
    if(parseInt(min_amount_black_cards.text()) < current_black_cards_res) {
        black_cards.css('color','green');
    } 
});
$(document).on('click', '#delete_added_deck_btn', function(){
    var added_decks_list = $('#added_decks_list');
    var deck_id = $(this).parent().siblings('.deck_id').text();
    var white_cards = $(this).parent().siblings('.white_cards').text();
    var black_cards = $(this).parent().siblings('.black_cards').text();
    if($(this).parents().eq(1).siblings().length == 1){
        added_decks_list.children('#child').css('display', '')
    }
    added_decks = $.grep(added_decks, function(value) {
        return value != deck_id;
    });
    deck_button  = $('#'+deck_id);
    if(deck_button.length){
        deck_button.css({'pointer-events':'', 'opacity': '100%'});
    }
    $(this).parents().eq(1).remove();
    current_black_cards_int = parseInt(current_black_cards.text());
    current_white_cards_int = parseInt(current_white_cards.text());
    current_black_cards_res =current_black_cards_int - parseInt(black_cards);
    current_white_cards_res = current_white_cards_int - parseInt(white_cards);
    current_white_cards.html(current_white_cards_res);
    current_black_cards.html(current_black_cards_res);
    if(parseInt(min_amount_white_cards.text()) > current_white_cards_res) {
        white_cards.css('color','red');
    }  
    if(parseInt(min_amount_black_cards.text()) > current_black_cards_res) {
        black_cards.css('color','red');
    }  
});