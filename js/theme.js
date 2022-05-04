var check = document.getElementById("topcheck");

function changeTheme()
{
	if (check.checked==false)
		{
			var body = document.getElementById("bright");
			body.id = "dark";
		}
	else if (check.checked==true)
		{
			var body = document.getElementById("dark");
			body.id = "bright";
		}
}
check.addEventListener("click", changeTheme);

$(document).ready(function(){
	$('#game_rules').css('border-bottom', "2px solid var(--rules-background)");
	$('#game_rules_text').css('display', "block");
	var check_game = $('#game_rules_check');
	var check_side = $('#side_rules_check');
	check_side.change(function(){
			if(check_side.prop("checked")){
				check_game.prop('checked', false);
				$('#side_rules').css("border-bottom", "2px solid var(--rules-background)");
				$('#game_rules').css('border', "2px solid var(--rules-border)");
				$('#side_rules_text').css('display', 'block');
				$('#game_rules_text').css('display', 'none');
			}
			else if(!check_side.prop("checked")){
				check_side.prop('checked', false);
				check_game.prop('checked', true);
				$('#game_rules').css("border-bottom", "2px solid var(--rules-background)");
				$('#side_rules').css('border', "2px solid var(--rules-border)");
				$('#game_rules_text').css('display', 'block');
				$('#side_rules_text').css('display', 'none');
			}
	});
	check_game.change(function(){
			if(check_game.prop("checked")){
				check_side.prop('checked', false);
				$('#game_rules').css("border-bottom", "2px solid var(--rules-background)");
				$('#side_rules').css('border', "2px solid var(--rules-border)");
				$('#game_rules_text').css('display', 'block');
				$('#side_rules_text').css('display', 'none');
			}
			else if(!check_game.prop("checked")){
				check_game.prop('checked', false);
				check_side.prop('checked', true);
				$('#side_rules').css("border-bottom", "2px solid var(--rules-background)");
				$('#game_rules').css('border', "2px solid var(--rules-border)");
				$('#side_rules_text').css('display', 'block');
				$('#game_rules_text').css('display', 'none');
			}
	});
});
$(document).ready(function(){
	if($('#register_success').length){
		$('#top').css('opacity','40%');
		$('#container').css('opacity','40%');
	}
	$('#close_register_btn').click(function(){
		$('#top').css('opacity','100%');
		$('#container').css('opacity','100%');
		$('#register_success').remove();
	});
});
