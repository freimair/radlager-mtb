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


// show and hide for search and filter menu

  function togglefilter () {
        if (jQuery(".filter").is(".selected")) {jQuery("#filterbutton").css ({"color" : "#47974C"})} else {jQuery("#filterbutton").css ({"color" : "#ffffff"})};
        jQuery("#filterbutton").toggle(0);
        jQuery(".filtermenu").toggle(0);
	};
    
   function togglesearch () {
        if (jQuery("#searchbox").val() != "") {jQuery("#searchbutton").css ({"color" : "#47974C"})} else {jQuery("#searchbutton").css ({"color" : "#ffffff"})};
        jQuery("#searchbutton").toggle(0);
        jQuery(".searchmenu").toggle(0);
	};