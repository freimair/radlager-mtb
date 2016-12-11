var $j = jQuery.noConflict();

$j(document).on('resize ready', function()
				
				{
					$j('.more-link').off('click');
					$j('.more-link').click(function(e)
							{
						
						e.preventDefault();
						var post_id = jQuery(this).attr("data-post_id");
						loadmore = 'off';
						var position = $j('#post-' + post_id).position();
						var ptop = position.top;
						var pleft = position.left;
						var fromtop = $j(document).scrollTop();				
					
			
                $j('#post-' + post_id).clone().css({
                    top: ptop,
                    left: pleft,
			
                }).appendTo('.slidewindow');
			
								
					$j('.slidewindow #readmoreinline' + post_id).toggle();
						  $j( '.slidewindow #post-' + post_id).animate({
												
							
							width: '100%',
							margin : '0 auto',
							height : $j( window ).height(),
							},0 );
						
						function del() {
							$j('.banner').css({'display' : 'none'});
							$j('#masonry-grid').css({'display' : 'none'});
							$j('#filterbutton').css({'display' : 'none'});
							$j('#closearticlebutton').css({'display' : 'block'});
							$j('#searchbutton').css({'display' : 'none'});
							
				
                        }
						$j(document).scrollTop(10);
						$j('.slidewindow #post-' + post_id).css({'top' :'80px', 'position' : 'relative', 'left' : '0px'});
						setTimeout(del,0)
						
					

						$j('#custom-bg').css ({'height' : '300px', 'object-fit' : 'cover' });
						$j('.slidewindow .more-link').css ({'display' : 'none'});
							
						$j('#closearticlebutton').click(function(e){
								
						$j('.banner').css({'display' : 'block'});
						$j('#masonry-grid').css({'display' : 'block'});
						$j('#filterbutton').css({'display' : 'block'});
						$j('#searchbutton').css({'display' : 'block'});
						$j('#closearticlebutton').css({'display' : 'none'});
									  		
						$j(document).scrollTop(fromtop);										
						$j( '.slidewindow').animate({}, 0 );
						
						$j('.slidewindow').empty();
						loadmore = 'on';
						
						
					});
				
				});		
		});



