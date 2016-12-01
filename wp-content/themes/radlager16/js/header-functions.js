jQuery(window).scroll(function() {
   
		 var scrollPos = jQuery(window).scrollTop();
		
		if (scrollPos >= 1) {	
			jQuery(".banner").css({"margin-top":"-337px"});
			jQuery(".RL-Logo_oben").css({"display":"block","opacity":"1"});	
	}
	else
	{
		jQuery(".banner").css({"margin-top":"75px"});
		jQuery(".RL-Logo_oben").css({"display":"block","opacity":"0"});
	}
	});