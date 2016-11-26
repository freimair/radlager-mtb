var $j = jQuery.noConflict();

$j(document).on('resize ready', function(){
	$j('.more-link').off('click');
	$j('.more-link').click(function(e){
		e.preventDefault();
		var post_id = jQuery(this).attr("data-post_id");
		var post_top = $j('#post-' + post_id).css ("top" ).replace(/[^-\d\.]/g, '');
		
		$j('#readmoreinline' + post_id).toggle();
		
		//Article auf Seitengröße bringen
           $j('#post-' + post_id).css ({'width' : '100%' ,});
			 
		$j('#post-' + post_id + ' .post-thumbnail').css ({'width' : '100%'});
			$j('#post-' + post_id + ' .more-link').css ({'display' : 'none'});
			
	

		
		
		//Article entkoppeln
		
		var ypos=$j('.header').scrollTop();
		
	              $j('.show-window').css('')
			      $j('#post-' + post_id).clone().appendTo(".show_window");
			    

	
			 
		$j('#masonry-grid').masonry();
	//	alert($j('#post-' + post_id).css("top"));
		
			$j('html, body').animate({
        scrollTop: $j('#post-' + post_id).offset().top
    }, 1000);
			
						$j(document).on('scroll', function(){
	//	alert($j('#post-' + post_id).css("top").replace(/[^-\d\.]/g, ''));
			//alert($j(document).scrollTop());
			
			if($j('#post-' + post_id).css("top").replace(/[^-\d\.]/g, '') > $j(document).scrollTop() +800) {
	 $j('#post-' + post_id).css ({'width' : '33%' ,});
	 $j('#post-' + post_id + ' .more-link').css ({'display' : 'block'});
	 $j('#readmoreinline' + post_id).css ({'display' : 'none'});
	
	 $j('#masonry-grid').masonry();
	   $j(window).off("scroll", scrollHandler);
} 
} );
	
	});
});
