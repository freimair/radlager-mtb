var $j = jQuery.noConflict();

$j(document).on('resize ready', function(){
	$j('.more-link').off('click');
	$j('.more-link').click(function(e){
		e.preventDefault();
		var post_id = jQuery(this).attr("data-post_id");
		$j('#readmoreinline' + post_id).toggle();
		$j('#masonry-grid').masonry();
	});
});
