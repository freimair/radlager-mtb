
function header() {
	
	jQuery(window).scroll(function() {

						  
		if ( jQuery(document).height() >= 1500) {
           
        
						  
						 
		
		scrollPos = jQuery(window).scrollTop();
		
		if (scrollPos >= 1) {
			
			jQuery(".banner").css({"margin-top":"-337px"});
			jQuery(".RL-Logo_oben").css({"display":"block","opacity":"1"});
			
			
		} else {
			
			jQuery(".banner").css({"margin-top":"134px"});
			jQuery(".RL-Logo_oben").css({"opacity":"0"});
			
		}
	
	}
	});
	
	
	

}

