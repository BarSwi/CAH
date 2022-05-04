$("#login_button").click(function(){
	var login = $("#input_login").val();
	var haslo = $("#input_password").val();
	if(login!="" && haslo !=""){
		event.preventDefault();
		$.ajax({
		type: "POST",
		url: '../phpscripts/login.php',
		data: {login:login, haslo:haslo},
		success: function(res){
			window.location.reload();

				
		}
	});
	}
		
});


$('#logout_button').click(function(){
	$.ajax({
		url: '../phpscripts/logout.php',
		success: function(){
			window.location.reload();
		}
	});
});



 $(document).on('click', '#forgot_password_email_input_send', function(){
	var forgot_password_email_input_send = $('#forgot_password_email_input_send');
	var forgot_password_email_input = $('#forgot_password_email_input');
	var loader = $('#password_recovery_loader');
	$(this).prop('disabled', true).css('pointer-events', 'none');
	var email = forgot_password_email_input.val();
	 if (/^([A-Za-z0-9.\-]*\w)+@+([A-Za-z0-9\-]*\w)+(\.[A-Za-z]*\w)+$/.test(email) == false){
		forgot_password_email_input.css("border", "2px solid red");
		forgot_password_email_input_send.prop('disabled', false).css('pointer-events','');
		}
	else{
		forgot_password_email_input.css("border", "1px solid var(--forgot-password-email-input-border)");
		loader.html("<div class = 'loader'></div>");
		setTimeout(function(){
			$.ajax({
				type: 'POST',
				url: '../phpscripts/password_recovery.php',
				async: false,
				data: {email:email},
				success: function(res){
					var forgot_password_result = $('#forgot_password_result');
					var forgot_password = $('#forgot_password');
					if(res=="0"){
						if(forgot_password.html().charAt(0) == "H"){
							forgot_password_result.html("Email does not exist").css("color","red");
						}
						if(forgot_password.html().charAt(0) == "Z"){
							forgot_password_result.html("Email nie istnieje").css("color","red");
						}
					forgot_password_email_input_send.prop('disabled', false).css('pointer-events','');
							loader.html('');
					}
					else if(res=="2"){
						if(forgot_password.html().charAt(0) == "H"){
							forgot_password_result.html("Reset link already sent check your email").css("color","red");
						}
						if(forgot_password.html().charAt(0) == "Z"){
							forgot_password_result.html("Link został już wysłany na podany adres, sprawdź swoją pocztę").css("color","red");
						}
					forgot_password_email_input_send.prop('disabled', false).css('pointer-events','');
					loader.html('');
					}
					
					else if(res=="1"){
						if(forgot_password.html().charAt(0) == "H"){
							forgot_password_result.html("Check your email").css("color","green");
						}
						if(forgot_password.html().charAt(0) == "Z"){
							forgot_password_result.html("Sprawdź swoją pocztę email").css("color","green");
						}		
						loader.html('');			
					}

				}
			});
		}, 50);

	}
});
 $(document).on('click', '#close_password_recovery_btn', function() {
	$('#forgot_password_email').remove();
	$('#forgot_password').css("pointer-events", "auto");
	$('#top').css('opacity','100%');
	$('#container').css('opacity','100%');
	});
	
$('#forgot_password').click(function(){
	$(this).css("pointer-events", "none");
	$('#top').css('opacity','40%');
	$('#container').css('opacity','40%');
	if($(this).html().charAt(0) == "H"){
	$('body').append('<div id = "forgot_password_email"> <h4>Insert email for the account you want to recover:</h4><label id = "close_register"><input type = "button" id = "close_password_recovery_btn"><i class = "icon-cancel"></i></label><input type = "text" placeholder = "example@mail.com" id = "forgot_password_email_input"><label id = "forgot_password_email_input_send"><input type = "button">Send</label><span id = "forgot_password_result"></span><span id = "password_recovery_loader"></span></div>');
	}
	if($(this).html().charAt(0) == "Z"){
	$('body').append('<div id = "forgot_password_email"> <h4>Podaj email do konta, do którego hasło chcesz odzyskać:</h4><label id = "close_register"><input type = "button" id = "close_password_recovery_btn"><i class = "icon-cancel"></i></label><input type = "text" placeholder = "example@mail.com" id = "forgot_password_email_input"><label id = "forgot_password_email_input_send"><input type = "button">Wyślij</label><span id = "forgot_password_result"></span><span id = "password_recovery_loader"></span></div>');
	}
	setTimeout(function(){
		$('#forgot_password_email').css('transform', 'translateY(10%)');
	});
});
$('#create_new_deck').click(function(){
	$(this).css('pointer-events', 'none');
	$('#top').css('opacity','40%');
	$('#container').css('opacity','40%');
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
	$('#create_new_deck').css('pointer-events','auto');
	$('#create_new_deck_menu').remove();
	$('#top').css('opacity','100%');
	$('#container').css('opacity','100%');
	});
$(document).on('click', '#create_new_deck_send', function(){
	var litera = $(this).text().charAt(0);
	$(this).prop('disabled', true).css('pointer-events', 'none');
	var create_new_deck_send = $(this);
	var deck_title_input = $('#create_new_deck_title_input');
	var title = deck_title_input.val();
	if(title.length > 30){
		if(litera == 'U') 	deck_title_input.val("Zbyt długi tytuł.");
		if(litera == 'C') 	deck_title_input.val("Title too long.");
		$(this).prop('disabled', false).css('pointer-events', 'auto');
	}
	if(title.length < 1){
		if(litera == 'U') 	deck_title_input.attr('placeholder', "Tytuł jest wymagany.");
		if(litera == 'C') 	deck_title_input.attr('placeholder', "Title is required.");
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
