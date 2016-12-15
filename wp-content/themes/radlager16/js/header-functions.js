jQuery(window).scroll(function() {
         
		 var scrollPos = jQuery(window).scrollTop();
		
		if ((scrollPos >= 200) || (jQuery(".slidewindow").find(".artcont").length != 0 )){
			jQuery(".RL-Logo_oben").css({"display":"block","opacity":"1"});	
    }
	else
	{
		jQuery(".RL-Logo_oben").css({"display":"block","opacity":"0"});

    };
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
    
         function userlogin(){
                  
         jQuery('.slidewindow').append('ffffffffffffffffffff');	      
           
	};
    

    
function openslidewindow(fromtop) {
         
    
         jQuery('#content').css({
						   'position' : 'fixed',
						   'width' : '100%',
                           'top' : (fromtop*(-1)), 
                           });
    
         jQuery('.slidewindow').css({
						   'position' : 'relative',
						   'width' : '100%',
						   'z-index' : '2',
						   'background-color' : 'rgba(0, 0, 0, 0.60)',
						   'opacity' : '1',
						   'min-height' : $j('html').height()	
						   });
    
         jQuery('#closearticlebutton').css({
						   'display' : 'block',
						   'position' : 'relative',
						   'z-index' : '200',
						   });
    
         jQuery(document).scrollTop(0);
         

}

function closeslidewindow(fromtop) {
         
         
         jQuery('#closearticlebutton').add('#closewin').on ('click', function(e) {
								
                  jQuery('#closearticlebutton').css({'display' : 'none'});
                  jQuery('.slidewindow').css({'opacity' : '0', 'min-height' : '0px'});
						
                  setTimeout(function(){ 
                           jQuery('.slidewindow').empty();
                           jQuery('#content').css({
                                    'position' : 'relative',
                                    'top' : '0px',
						   });
                  
                           jQuery(document).scrollTop(fromtop);
						
				  },200);					
         
         });
}


function pushlogin() {
         
         var fromtop = $j(document).scrollTop();
         
         openslidewindow(fromtop);
         jQuery('.login').clone().appendTo('.slidewindow');
         jQuery('.slidewindow .login').append('</br><input id="closearticlebutton" style="float: right" type = "button" value="Close"></br>');
         closeslidewindow(fromtop);
}
