var $j = jQuery.noConflict();

$j(document).on('resize ready', function()
				
{
				$j('.more-link').off('click');
				$j('.more-link').click(function(e)
				{
						
					e.preventDefault();
					var post_id = jQuery(this).attr("data-post_id");
					loadmore = 'off';
						
					var fromtop = $j(document).scrollTop();		
				
					openslidewindow(fromtop);

					$j('#post-' + post_id).clone().appendTo('.slidewindow');
					$j('.slidewindow #post-' + post_id + ' .artcont .readmoreinline').append('</br><input id="closearticlebutton" style="float: right" type = "button" value="Close"></br>');
					$j('.slidewindow #post-' + post_id + ' .artcont').append('<input id="closewin" type = "button" value="&#xf00d;">');			
					$j('.slidewindow #readmoreinline' + post_id).toggle();
					$j('.slidewindow #post-' + post_id).css({
						'top' :'0px',
						'position' : 'relative',
						'left' : '0px',
						'margin' : '80px auto',
						'float' : 'none'				
					});
				
					$j('#custom-bg').css ({'height' : '300px', 'object-fit' : 'cover' });
					$j('.slidewindow .more-link').css ({'display' : 'none'});
						
					closeslidewindow(fromtop);
						
						
					loadmore = 'on';
				
				});		
});



