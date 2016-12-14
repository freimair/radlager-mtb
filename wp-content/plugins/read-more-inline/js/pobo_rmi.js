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
				
					
						
							$j('#content').css({
								'position' : 'fixed',
								'width' : '100%',
								'top' : (fromtop*(-1)), 
								});
							$j('.slidewindow').css({
								'position' : 'relative',
								'width' : '100%',
								'z-index' : '2',
								'background-color' : 'rgba(0, 0, 0, 0.60)',
								'opacity' : '1',
								'min-height' : $j('html').height()
								
								});
							$j('#closearticlebutton').css({
								'display' : 'block',
								'position' : 'relative',
								'z-index' : '200',
								});
				

                $j('#post-' + post_id).clone().appendTo('.slidewindow');
			    $j('.slidewindow #post-' + post_id + ' .artcont .readmoreinline').append('</br><input id="closearticlebutton" style="float: right" type = "button" value="Close"></br>');
			 	$j('.slidewindow #post-' + post_id + ' .artcont').append('<input id="closewin" type = "button" value="&#xf00d;">');			
				$j('.slidewindow #readmoreinline' + post_id).toggle();
					
			
						$j('.slidewindow #post-' + post_id).css({
							'top' :'0px',
							'position' : 'relative',
							'left' : '0px',
							'margin' : '80px auto',
							'float' : 'none',
							
						});
						
					//	  $j('.slidewindow #post-' + post_id + ' .artcont').css({
					//		'background-color' : 'white',
					//		'padding' : '30px',
						
					//	});

						$j('#custom-bg').css ({'height' : '300px', 'object-fit' : 'cover' });
					 	$j('.slidewindow .more-link').css ({'display' : 'none'});
						$j(document).scrollTop(0);
						$j('#closearticlebutton').add('#closewin').on ('click', function(e) {
							
				
						
						$j('#closearticlebutton').css({'display' : 'none'});
						$j('.slidewindow').css({'opacity' : '0', 'min-height' : '0px'});
						
						setTimeout(function(){
						$j('.slidewindow').empty();
						$j('#content').css({
							'position' : 'relative',
								'top' : '0px',
							});
					    $j(document).scrollTop(fromtop);
						
						},200);						

						loadmore = 'on';
									
					});
				
				});		
		});



