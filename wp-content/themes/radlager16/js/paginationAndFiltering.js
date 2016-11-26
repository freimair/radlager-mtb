	// these scripts control the ajax pagination
	jQuery(function(){
		var page = 2; // start at page 2
		var loadmore = 'on'; // ready to go

		// hook the scroll, resize and ready events to see if the spinner is visible
		jQuery(document).on('scroll resize ready', function() {
			if ('on' == loadmore && jQuery(window).scrollTop() + jQuery(window).height() + 200 > jQuery('#spinner').offset().top) {
				loadmore = 'off';
				jQuery('#spinner').css('visibility', 'visible');

				// load new content into temporary container <newContent> and append its .children() to the main content
				result = jQuery('<newContent>').load(window.location + '?page=' + page + ' article', function() {
					page++;
					loadmore = 'on';
					jQuery('#spinner').css('visibility', 'hidden');
					contents = result.children();
					
				
					
				jQuery('#masonry-grid').append(contents).masonry( 'appended', contents );
				
				
				 // init Masonry after all images have loaded
				var $grid = jQuery('#masonry-grid').imagesLoaded( function() {
					 $grid.masonry({
					  
					});
				});


/* init masonry grid pictures are loaded
 * 
var $grid = jQuery('#masonry-grid').masonry({
  itemSelector: '.post',
        percentPosition: true,
});
// layout Masonry after each image loads
$grid.imagesLoaded().progress( function() {
  $grid.masonry('layout');
});


*/


				
				});
			}
			
			
		});

		// also hook the ajaxComplete event in order to clean up after each ajax load
		jQuery( document ).ajaxComplete(function( event, xhr, options ) {
updateFilter();
			// do the funny string concatination because whenever this js gets delivered via ajax, the very same string is found which of course results in an infinite loop
			if (xhr.responseText.indexOf('<article ') == -1) {
				// disable ajax loading if there is nothing more to get
				loadmore = 'off';
			} else if ('on' == loadmore) {
				// retrigger the check event. the event will seize creating new ajax events as soon as the spinner is out of sight
				jQuery(document).trigger("resize");
			}
		});

	});

	// these scripts control the filter mechanism
   jQuery(document).ready(function() {
	// this function controls selecting and deselecting filters and applies the filter afterwards
	jQuery(".filter").click(function() {
			if(jQuery(this).hasClass("selected")) {
				jQuery(".filter").removeClass("selected");
			} else {
				jQuery(".filter").removeClass("selected");
				jQuery(this).addClass("selected");
			}
			// apply the filter
			updateFilter();
			// see if we have room for more content after the filter was applied
		 	jQuery(document).trigger("resize");
			
		});
	
	});

	// this function checks applies the filter to the articles
	function updateFilter() {
		selected = jQuery('.filter.selected');
		if(0 == selected.length) {
			jQuery("article[class^=filter-]").show();
		} else {
			jQuery("article[class^=filter-]").hide();
			jQuery(".filter-" + selected[0].getAttribute('value')).show();
		}
			
			/*
		jQuery('#masonry-grid').imagesLoaded( function() {
    jQuery('#masonry-grid').masonry({
        itemSelector: '.post',
        percentPosition: true,
   
    }); 
}); */
	}
	
