$(document).ready(function(){
	$('#deck1').addClass('middle');
	$('#deck2').addClass('left');
	$('#deck3').addClass('right');
	if(!$('#deck3').length) $('.icon-left-circled2').css('display', 'none')
})
var keyup = false;
$(document).keydown(function turn(e){
	if(!keyup){
		var flagleft = $('.icon-left-circled2').is(":visible");
		var flagright = $('.icon-right-circled2').is(":visible");
		switch(e.which) {
			case 37:
			if(flagleft) turnleft();
			break;
			case 39: 
			if(flagright) turnright();
			break;
			default: return; 
		}
		keyup = true;
		setTimeout(function(){
			keyup = false;
		},400);
	}
}); 
$('.icon-right-circled2').on('click', turnright);
$('.icon-left-circled2').on('click', turnleft);

$('#delete_btn').click(function(){
	var hl = $(this).text().substring(0,1);
	var title = $('.middle  #deck_title').text();
	$(this).css('pointer-events', 'none');
	$('#edit_btn').css('pointer-events', 'none');
	$('.icon-right-circled2').css('pointer-events', 'none');
	$('.icon-left-circled2').css('pointer-events', 'none');
	$('#container').css('opacity', '50%');
	if(hl == "D"){
		$('body').append('<div id = "remove_deck_confirm"><div>Are you sure you want to delete deck: <br>'+title+' ?</div><div id = "remove_deck_select"><div class = "remove_deck_option" id ="remove_deck_yes">Yes</div><div class = "remove_deck_option" id ="remove_deck_no">No</div></div></div>');
		}	
	if(hl == "U"){
		$('body').append('<div id = "remove_deck_confirm"><div>Czy na pewno chcesz usunąć talię:<br> '+title+' ?</div><div id = "remove_deck_select"><div class = "remove_deck_option" id ="remove_deck_yes">Tak</div><div class = "remove_deck_option" id ="remove_deck_no">Nie</div></div></div>');
	}	
});


$(document).on('click', '.remove_deck_option',function(){
	$('#remove_deck_confirm').remove();
	$('#container').css('opacity', '100%');
	$('#delete_btn').css('pointer-events', '');
	$('#edit_btn').css('pointer-events','');
	$('.icon-right-circled2').css('pointer-events','');
	$('.icon-left-circled2').css('pointer-events','');
});
$(document).on('click', '#remove_deck_yes', function(){
	var deck_code = $('.middle #deck_id').text();
	$.ajax({
		type: 'POST',
		url: '../phpscripts/remove_deck.php',
		data: {deck_code:deck_code},
		success: function(res){
			if(res=="0"){
				alert("Something went wrong");
				window.location.reload();
			}
			else window.location.reload();
		}
	});
});
$('#edit_btn').click(function(){
	location.href= "card_creator.php?id=" + $('.middle #deck_id').text();
});

$('#create_btn').click(function(){
	$('#top').css('opacity','40%');
	$('#bottom').css('display','none');
	$('#container').css('display','none');
	if($(this).html().charAt(0) == "C"){
	$('body').append('<div id = "create_new_deck_menu"><label id = "close_deck_creator"><input type = "button" id = "close_deck_creator_btn"><i class = "icon-cancel"></i></label><span id = "create_new_deck_input_title_span">Title:</span><textarea id = "create_new_deck_title_input" maxlength = "30" placeholder = " Maximum 30 characters."></textarea><label id = "create_new_deck_send"><input type = "button">Create deck</label></div>');
	}
	if($(this).html().charAt(0) == "S"){
	$('body').append('<div id = "create_new_deck_menu"><label id = "close_deck_creator"><input type = "button" id = "close_deck_creator_btn"><i class = "icon-cancel"></i></label><span id = "create_new_deck_input_title_span">Tytuł:</span><textarea id = "create_new_deck_title_input" maxlength = "30" placeholder = "Maksymalnie 30 znaków."></textarea><label id = "create_new_deck_send"><input type = "button">Utwórz talię</label></div>');
	}
	setTimeout(function(){
		$('#create_new_deck_menu').css('transform', 'scaleX(1)');
});	


});
$(document).on('click', '#close_deck_creator_btn', function() {
	$('#create_new_deck_menu').remove();
	$('#top').css('opacity','100%');
	$('#bottom').css('display','');
	$('#container').css('display','');
	});
$(document).on('click', '#create_new_deck_send', function(){
	var hl = $(this).text().charAt(0);
	$(this).prop('disabled', true).css('pointer-events', 'none');
	var create_new_deck_send = $(this);
	var deck_title_input = $('#create_new_deck_title_input');
	var title = deck_title_input.val();
	if(title.length > 30){
		if(hl == 'U') 	deck_title_input.val("Zbyt długi tytuł.");
		if(hl == 'C') 	deck_title_input.val("Title too long.");
		$(this).prop('disabled', false).css('pointer-events', 'auto');
	}
	if(title.length < 1){
		if(hl == 'U') 	deck_title_input.attr('placeholder', "Tytuł jest wymagany.");
		if(hl == 'C') 	deck_title_input.attr('placeholder', "Title is required.");
		$(this).prop('disabled', false).css('pointer-events', 'auto');
	}
	else{
		$.ajax({
			type: 'POST',
			url: 'phpscripts/create_deck_id.php',
			data: {title:title},
			success: function(res){
				if(res!="0")	location.href= "card_creator.php?id=" + res;
				else if(res=="0"){
					if(litera == 'U') 	alert("Maksymalna ilość możliwych do stworzenia talii na konto została osiągnięta.");
					if(litera == 'C') 	alert("The maximum number of decks that can be created per account has been reached.");					
				}
			}
		});

	}
	
});


function turnleft(){
	if(!$('#remove_deck_confirm').length){
		$(this).css('pointer-events', 'none');
		$('.btn').css({"pointer-events": "none", "opacity": "70%"});
		var left = $('.left');
		var middle = $('.middle');
		var right = $('.right');
		var deck1 = $('#deck1');
		var deck2 = $('#deck2');
		var deck3 = $('#deck3');
		if(deck3.length){
			if(deck1.attr('class').substring(5) =='left'){
				deck1.css({'transform': 'translateX(105%) scale(1) ', 'z-index': -1});
			}
			else if(deck1.attr('class').substring(5) =='middle'){
				deck1.css({'transform': 'translateX(-100%) scale(1)', 'z-index': -1});
			}
			else if(deck1.attr('class').substring(5) =='right'){
				deck1.css({'z-index': 1, 'transform': 'scale(1.75)'});
			}
			if(deck2.attr('class').substring(5) =='left'){
				deck2.css({'transform': 'scale(1) translateX(210%)', 'z-index': -1});
			}
			else if(deck2.attr('class').substring(5) =='middle'){
				deck2.css({'transform': 'scale(1', 'z-index': -1});
			}
			else if(deck2.attr('class').substring(5) =='right'){
				deck2.css({'z-index': 1, 'transform': 'translateX(105%) scale(1.75) '});
			}
			if(deck3.attr('class').substring(5) =='left'){
				deck3.css({'transform': 'scale(1) ', 'z-index': -1});
			}
			else if(deck3.attr('class').substring(5) =='middle'){
				deck3.css({'transform': 'scale(1) translateX(-210%)', 'z-index': -1});
			}
			else if(deck3.attr('class').substring(5) =='right'){
				deck3.css({'z-index': 1, 'transform': 'translateX(-105%) scale(1.75) '});
			}
			left.attr('class', 'deck right');
			middle.attr('class', 'deck left');
			right.attr('class', 'deck middle');
		}
		else if(deck2.length){
			$('.icon-left-circled2').css('display','none');
			$('.icon-right-circled2').css('display', '');
			if(deck1.attr('class').substring(5) =='left'){
				deck1.css({'transform': 'translateX(105%) scale(1) ', 'z-index': -1});
			}
			else if(deck1.attr('class').substring(5) =='middle'){
				deck1.css({'transform': 'translateX(-100%) scale(1)', 'z-index': -1});
			}
			else if(deck1.attr('class').substring(5) =='right'){
				deck1.css({'z-index': 1, 'transform': 'scale(1.75)'});
			}
			if(deck2.attr('class').substring(5) =='left'){
				deck2.css({'transform': 'scale(1) translateX(210%)', 'z-index': -1});
			}
			else if(deck2.attr('class').substring(5) =='middle'){
				deck2.css({'transform': 'scale(1', 'z-index': -1});
			}
			else if(deck2.attr('class').substring(5) =='right'){
				deck2.css({'z-index': 1, 'transform': 'translateX(105%) scale(1.75) '});
			}
			middle.attr('class', 'deck left');
			right.attr('class', 'deck middle');
		}
		setTimeout(function(){
			$('.icon-left-circled2').css('pointer-events', '');
			$('.btn').css({"pointer-events": "", "opacity": ""});
		},400);
	}
}


function turnright(){
	if(!$('#remove_deck_confirm').length){
		$(this).css('pointer-events', 'none');
		$('.btn').css({"pointer-events": "none", "opacity": "70%"});
		var left = $('.left');
		var middle = $('.middle');
		var right = $('.right');
		var deck1 = $('#deck1');
		var deck2 = $('#deck2');
		var deck3 = $('#deck3');
		if(deck3.length){
			if(deck1.attr('class').substring(5) =='left'){
				deck1.css({'transform': 'scale(1.75) ', 'z-index': 1});
			}
			else if(deck1.attr('class').substring(5) =='middle'){
				deck1.css({'transform': 'scale(1) translateX(105%)', 'z-index': -1});
			}
			else if(deck1.attr('class').substring(5) =='right'){
				deck1.css({'z-index': -1, 'transform': 'scale(1) translateX(-100%) '});
			}
			if(deck2.attr('class').substring(5) =='left'){
				deck2.css({'transform': 'translateX(105%) scale(1.75)', 'z-index': 1});
			}
			else if(deck2.attr('class').substring(5) =='middle'){
				deck2.css({'transform': 'scale(1) translateX(210%)', 'z-index': -1});
			}
			else if(deck2.attr('class').substring(5) =='right'){
				deck2.css({'z-index': -1, 'transform': ' scale(1) '});
			}
			if(deck3.attr('class').substring(5) =='left'){
				deck3.css({'transform': 'translateX(-105%) scale(1.75) ', 'z-index': 1});
			}
			else if(deck3.attr('class').substring(5) =='middle'){
				deck3.css({'transform': 'scale(1)', 'z-index': -1});
			}
			else if(deck3.attr('class').substring(5) =='right'){
				deck3.css({'z-index': -1, 'transform': 'translateX(-210%) scale(1) '});
			}
			left.attr('class', 'deck middle');
			middle.attr('class', 'deck right');
			right.attr('class', 'deck left');
		}
		else if(deck2.length){
			$('.icon-right-circled2').css('display','none');
			$('.icon-left-circled2').css('display', '');
			if(deck1.attr('class').substring(5) =='left'){
				deck1.css({'transform': 'scale(1.75) ', 'z-index': 1});
			}
			else if(deck1.attr('class').substring(5) =='middle'){
				deck1.css({'transform': 'scale(1) translateX(105%)', 'z-index': -1});
			}
			else if(deck1.attr('class').substring(5) =='right'){
				deck1.css({'z-index': -1, 'transform': 'scale(1) translateX(-105%) '});
			}
			if(deck2.attr('class').substring(5) =='left'){
				deck2.css({'transform': 'translateX(105%) scale(1.75)', 'z-index': 1});
			}
			else if(deck2.attr('class').substring(5) =='middle'){
				deck2.css({'transform': 'scale(1) translateX(210%)', 'z-index': -1});
			}
			else if(deck2.attr('class').substring(5) =='right'){
				deck2.css({'z-index': -1, 'transform': ' scale(1) '});
			}
			left.attr('class', 'deck middle');
			middle.attr('class', 'deck right');
		} 
		setTimeout(function(){
			$('.icon-right-circled2').css('pointer-events', '');
			$('.btn').css({"pointer-events": "", "opacity": ""});
		},400);
	}
}