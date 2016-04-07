var $j = jQuery.noConflict();

$j('document').ready(function(){
	$j('.readmoreinline').hide();
	$j('.more-link').click(function(e){
		e.preventDefault();
		$j('.readmoreinline').toggle();
	});
});
