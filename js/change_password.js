$(document).ready(function(){
	$.ajax({
		url: '../phpscripts/languagecheck.php',
		success: function(res){
			$('#hl').html(res);
		}
	});
});
function change_password(){
	validation = true;
	var haslo = $('#register_password').val();
	var haslo1 = $('#register_password-re').val();
	if(!haslo){
		validation = false;
		$('#register_password').css("border", "2px solid var(--input-border)")
	}
	else if(haslo.length < 8 ){
		$('#register_password').css("border", "2px solid red");
		validation = false;
	}
	else if(haslo.length > 7) {
		$('#register_password').css("border", "2px solid green");
	}
	if(!haslo1){
		validation = false;
		$('#register_password-re').css("border", "2px solid var(--input-border)")
	}
	else if ((haslo1 != haslo) && haslo.length>7){
		$('#register_password-re').css("border", "2px solid red");
		validation = false;
	}
	else if((haslo1 == haslo) && haslo.length>7) $('#register_password-re').css("border", "2px solid green");
	if(validation==true){
		$('#register_button').prop("disabled", false);
	}
	else $('#register_button').prop("disabled", true);
}
$('#register_password').keyup(change_password);
$('#register_password-re').keyup(change_password);
$("#register_button").click(function(){
	var haslo = $('#register_password').val();
	var haslo1 = $('#register_password-re').val();
	var selector = window.location.href.substring(45,61);
	$.ajax({
		type: 'POST',
		data: {haslo:haslo, haslo1:haslo1, selector:selector},
		url: "../phpscripts/change_password_db.php",
		async: false,
		success: function(res){
			if(res=="0"){
				alert("Unauthorized action");
				window.location.reload();
			}
			else if(res=="1"){
				if($('#hl').html()=="en")	$('#register').html('Password changed successfuly! Soon you will be redirected to the home page.').css('color', 'green');
				if($('#hl').html()=="pl")	$('#register').html('Hasło zostało zmienione pomyślnie! Nastąpi przekierowanie na stronę główną.').css('color', 'green');
				setTimeout(function(){
					window.location.replace('index.php');
				}, 5000);
			}
		}
	});
});