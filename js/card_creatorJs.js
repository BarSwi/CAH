var deck_code = window.location.href.substr(window.location.href.indexOf("=")+1);

$('textarea').keyup(function(){
    $(this).scrollTop(0);
});
$('#add_card_black').click(function(){
	$(this).blur();
	var add_card_black = $(this);
	var black_input = $('#black_input');
	var black_value = black_input.val();
	var flag = true;
	var adding_result = $('#adding_result');
	var hl = $('#tutorial h1').text().charAt(0);
	var black_cards_h1 = $('#black_cards_h1');
	var count = (black_value.match(/\b___\b/g) || []).length;
	if(count==0){
		if(hl == "P"){
			adding_result.html("Brak pustego miejsca na czarnej karcie.");
		}	
		if(hl == "T"){
			adding_result.html("There is no blank space on the black card.");
		}	
	}
	if(count>3){
		if(hl == "P"){
			adding_result.html("Za dużo pustych miejsc na czarnej karcie.");
		}	
		if(hl == "T"){
			adding_result.html("Too many blank spaces on the black card.");
		}	
	}
	if(count> 0 && count <4){
		adding_result.html('');
		if(!$('#black_input_antydouble').prop('checked')){
			$('.black_card').each(function(){
				var black_card = $(this).text().toUpperCase();
				if(black_card == black_value.toUpperCase()){
					flag = false;
				}	
			});
		}
		$(this).css('pointer-events', 'none');
		if(flag){
			$.ajax({
				type: 'GET',
				url: "../phpscripts/add_card_db.php",
				async: false,
				data: {color:"black", value:black_value, deck_code:deck_code},
				success: function(res){
					if(res=="0"){
						alert("Critical error, please try again");
						window.location.reload();
					}
					else{
						black_input.addClass('cards_animation');	
						setTimeout(function(){
							add_card_black.css('pointer-events', '');
							black_input.removeClass('cards_animation');
							$('#black_cards_added').prepend("<label id = '"+res+"' class = 'black_card added_card' tabindex ='0'>"+ black_value +"<input class = 'added_card_check' type = 'checkbox'></label>");
							black_input.val('');
							black_cards_h1.text(parseInt(black_cards_h1.text())+1);
						}, 300);
					}
				}
			});
		}
		if(!flag){
			add_card_black.css('pointer-events', '');
			if(hl == "P"){
				adding_result.html("Czarna karta się powtarza.");
			}	
			if(hl == "T"){
				adding_result.html("Black card is repeating.");
			}	
		}
	}
});
$('#add_card_white').click(function(){
	$(this).blur();
	var white_input = $('#white_input');
	$(this).css('pointer-events', 'none');
	var white_value = white_input.val();
	var add_card_white = $(this);
	var white_cards_h1 = $('#white_cards_h1');
	var adding_result = $('#adding_result');
	var flag = true;
	var hl = $('#tutorial h1').text().charAt(0);
	if(!$('#white_input_antydouble').prop('checked')){
		$('.white_card').each(function(){
			var white_card = $(this).text().toUpperCase();
			if(white_card == white_value.toUpperCase()){
				flag = false;
			}	
		});
	}
	if(!white_value.length){
		if(hl == "P"){
			adding_result.html("Biała karta nie może być pusta.");
		}	
		if(hl == "T"){
			adding_result.html("White card cannot be empty.");
		}
		add_card_white.css('pointer-events', '');
	}
	else if(flag){
			$.ajax({
				type: 'GET',
				url: "../phpscripts/add_card_db.php",
				async: false,
				data: {color:"white", value:white_value, deck_code:deck_code},
				success(res){
					if(res=="0"){
						alert("Critical error, please try again");
						window.location.reload();
					}
					else{
						white_input.addClass('cards_animation');	
						setTimeout(function(){
							add_card_white.css('pointer-events', '');
							white_input.removeClass('cards_animation');
							$('#white_cards_added').prepend("<label id = '"+res+"' class = 'white_card added_card' tabindex ='0'>"+ white_value +"<input class = 'added_card_check' type = 'checkbox'></label>");
							white_input.val('');
							white_cards_h1.text(parseInt(white_cards_h1.text())+1);
						}, 300);
					}
				}
			});
		}
	else if(!flag){
			add_card_white.css('pointer-events', '');
			if(hl == "P"){
				adding_result.html("Biała karta się powtarza.");
			}	
			if(hl == "T"){
				adding_result.html("White card is repeating.");
			}	
		}
});
$(document).on('click', '.added_card', function(){
	$(this).blur();
});
$(document).on('click', '.added_card_check', function(){
	if($(this).is(':checked')){
		$(this).parent().css({'background-color': 'green', 'border': '2px solid #99cc33'});
	}
	else if(!$(this).is(':checked')){
		$(this).parent().css({'background-color': $(this).parent().attr('class').substring(0,5), 'border': '2px solid var(--container-background)'});

	}
});
$('#remove_cards').click(function(){
	var hl = $('#tutorial h1').text().charAt(0);
	var adding_result = $('#adding_result');
	var counter = 0;
	$('.added_card_check').each(function(){
		if($(this).is(':checked')){
			counter = counter + 1;
		}

	});
	if(counter==0){
			if(hl == "P"){
				adding_result.html("Nie wybrano żadnej karty do usunięcia.");
			}	
			if(hl == "T"){
				adding_result.html("There is no card selected to be removed.");
			}	
	}
	else{
		$(this).css('pointer-events', 'none');
		$('#container').css('opacity', '50%');
		if(hl == "T"){
			if(counter==1) var karta = "card";
			if(counter>1) var karta = "cards";
			$('body').append('<div id = "remove_cards_confirm"><div>Are you sure you want to delete: '+counter+ ' ' +karta+'?</div><div id = "remove_cards_select"><div class = "remove_cards_option" id ="remove_cards_yes">Yes</div><div class = "remove_cards_option" id ="remove_cards_no">No</div></div></div>');
			adding_result.html("");
			}	
		if(hl == "P"){
			if(counter==1) var karta = "kartę";
			if(counter>1) var karta = "karty";
			if(counter>4) var karta = "kart";
			$('body').append('<div id = "remove_cards_confirm"><div>Czy na pewno chcesz usunąć: '+counter+ ' ' + karta +'?</div><div id = "remove_cards_select"><div class = "remove_cards_option" id ="remove_cards_yes">Tak</div><div class = "remove_cards_option" id ="remove_cards_no">Nie</div></div></div>');
			adding_result.html("");
		}	

	}
});
$(document).on('click', '.remove_cards_option',function(){
	$('#remove_cards_confirm').remove();
	$('#container').css('opacity', '100%');
	$('#remove_cards').css('pointer-events', '');
});
$(document).on('click', '#remove_cards_yes', function(){
	const cards = [];
	$('.added_card_check').each(function(){
		if($(this).is(':checked')){
			var color = $(this).parent().attr('class').substring(0,5);
			var card_color = $("#"+color+"_cards_h1");
			var id = $(this).parent().attr('id');
			cards.push(id);
			$(this).parent().remove();
			card_color.text(parseInt(card_color.text())-1);
		}
	});
	var json = JSON.stringify(cards);
	$.ajax({
		type: "post",
		url: '../phpscripts/remove_card_db.php',
		data: {cards:json, deck_code:deck_code},
		success: function(res){
			if(res == '0'){
				alert('Critical Error');
				window.location.replace="/Home";
			}
		}
	});
});