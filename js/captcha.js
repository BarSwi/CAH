
window.onload = equation;
function equation(){
	var a = Math.floor(Math.random()*9+1);
	var b = Math.floor(Math.random()*60+1);
	$('#equation').html(a + "  +  " + b+ " = ");
	$.ajax({
		url: '../phpscripts/languagecheck.php',
		success: function(res){
			$('#hl').html(res);
		}
	});
}
$('#rules_accept').click(validation);
function validation(){
	var register_login = $('#register_login');
	var register_password = $('#register_password');
	var register_email = $('#register_email');
	var register_password_re = $('#register_password-re');
	var equation_result = $('#equation_result');
	var login = register_login.val();
	var haslo = register_password.val();
	var email = register_email.val();
	var lang = $('#hl').html();
	var validation = true;
	var haslo2 = register_password_re.val();
	var bot = equation_result.val();
	var a= parseInt($('#equation').html().substring(0, 2));
	var b =parseInt($('#equation').html().substring(5, 8));
	var login = register_login.val();
	if(!login){
		validation = false;
		register_login.css("border", "2px solid var(--input-border)")
		$('#errorl').html('');
	}
	else if(login.length > 15 || login.length < 3 || /^[a-zA-Z0-9]*$/.test(login) == false || /\s/.test(login)){
		register_login.css("border", "2px solid red");
		validation = false;
		$('#errorl').html('');	
	}
	else if(login.length > 2 && login.length < 16) {
		register_login.css("border", "2px solid green");
	}
	var email = register_email.val();
	if(!email)	{
		validation = false;
		register_email.css("border", "2px solid var(--input-border)")
		$('#errore').html('');
	}
	else if (/^([A-Za-z0-9.\-]*\w)+@+([A-Za-z0-9\-]*\w)+(\.[A-Za-z]*\w)+$/.test(email) == false){
		validation = false;
		register_email.css("border", "2px solid red");
		$('#errore').html('');
	}
	else{
		register_email.css("border", "2px solid green");
	}
	var haslo = register_password.val();
	if(!haslo){
		validation = false;
		register_password.css("border", "2px solid var(--input-border)")
	}
	else if(haslo.length < 8 ){
		register_password.css("border", "2px solid red");
		validation = false;
	}
	else if(haslo.length > 7) {
		register_password.css("border", "2px solid green");
	}
	var haslo2 = register_password_re.val();
	if(!haslo2){
		validation = false;
		register_password_re.css("border", "2px solid var(--input-border)")
	}
	else if ((haslo2 != haslo) && haslo.length>7){
		register_password_re.css("border", "2px solid red");
		validation = false;
	}
	else if((haslo2 == haslo) && haslo.length>7) register_password_re.css("border", "2px solid green");
	var bot = equation_result.val();
	if(!bot){
		validation = false;
		equation_result.css("border", "2px solid var(--input-border)");
	}
	else if (parseInt(bot) != (a+b)){
		validation = false;
		equation_result.css("border", "2px solid red");
	}
	else if (parseInt(bot) == (a+b)) equation_result.css("border", "2px solid green");
	if(!$('#rules_accept').prop('checked')){
		validation = false;
	}
	if(validation==true){
		$.ajax({
			type: "POST",
			url: '../phpscripts/check_ifexist_login.php',
			data: {login:login},
			async: false,
			success: function(res){
				if(res=="0"){
					validation = false;
					if(lang=="en") $('#errorl').html('<br>Nickname is already taken.');
					if(lang=="pl") $('#errorl').html("<br>Nazwa jest zajęta.");
					register_login.css("border", "2px solid red");
				} 
				else $('#errorl').html('');
			}
		});
		$.ajax({
		
			type: "POST",
			url: '../phpscripts/check_ifexist_email.php',
			data: {email:email},
			async: false,
			success: function(res){
				if(res=="0"){
					if(lang=="en") $('#errore').html('<br>Email is already taken.');
					if(lang=="pl") $('#errore').html("<br>Email jest zajęty.");
					register_email.css("border", "2px solid red");
					validation = false;
				} 
				else $('#errore').html('');
			}
		});
		if(validation==true)	$('#register_button').prop("disabled", false);
	}
	if(validation==false) $('#regsiter_button').prop('disabled', true);

}

$("#register_button").click(function(){
	var register_login = $('#register_login');
	var register_password = $('#register_password');
	var register_email = $('#register_email');
	var register_password_re = $('#register_password-re');
	var equation_result = $('#equation_result');
	var login = register_login.val();
	var haslo = register_password.val();
	var email = register_email.val();
	var haslo2 = register_password_re.val();
	var bot = equation_result.val();
	$.ajax({
		type: "POST",
		url: '../phpscripts/validation.php',
		data: {login:login, haslo:haslo, email:email, haslo2:haslo2, bot:bot},
		success: function(res){
			if(res=="0"){
				window.location.reload();
				alert("Error: Unauthorized actions, please try again.");
			}
			else{
				location.href="index.php";
			}
				
			
		}
	});
});
