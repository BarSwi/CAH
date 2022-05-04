
$(function() {  
    $(".hl").click(function() {  
        var lang =  $(this).val();
		event.preventDefault();
			 
		$.ajax({  
		    type: 'GET',  
		    url: '../languages/config.php',
			data: {lang:lang},  
		    success: function() {
				window.location.reload();
				
			    } 
				
		});
    });  
}); 