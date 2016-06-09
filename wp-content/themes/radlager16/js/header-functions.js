
function header() {
	
	jQuery(window).scroll(function() {
		
		scrollPos = jQuery(window).scrollTop();
		
		if (scrollPos >= 1) {
			
			jQuery(".banner").css({"margin-top":"-300px"});
			jQuery(".RL-Logo_oben").css({"display":"block","opacity":"1"});
			
			
		} else {
			
			jQuery(".banner").css({"margin-top":"0px"});
			jQuery(".RL-Logo_oben").css({"opacity":"0"});
			
		}
	});
	
}

