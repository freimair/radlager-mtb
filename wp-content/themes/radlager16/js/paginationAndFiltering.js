	// these scripts control the ajax pagination
	jQuery(function(){
		var page = 2; // start at page 2
		var loadmore = 'on'; // ready to go

		// hook the scroll, resize and ready events to see if the spinner is visible
		jQuery(document).on('scroll resize ready', function() {
			if ('on' == loadmore && jQuery(window).scrollTop() + jQuery(window).height() + 60 > jQuery('#spinner').offset().top) {
				loadmore = 'off';
				jQuery('#spinner').css('visibility', 'visible');
				// do the funny string concatination because whenever this js gets pulled in by the following ajax call, the very same string is found and the cleanup job cannot determine if there are more sites to load. Hence, leaven the '+' out results in an infinite loop.
				jQuery('#main').append(jQuery('<div class'+'="page" id="p' + page + '">').load(window.location + '?page=' + page + ' .page > *', function() {
					page++;
					loadmore = 'on';
					jQuery('#spinner').css('visibility', 'hidden');
				}));
			}
		});

		// also hook the ajaxComplete event in order to clean up after each ajax load
		jQuery( document ).ajaxComplete(function( event, xhr, options ) {
updateFilter();
			// do the funny string concatination because whenever this js gets delivered via ajax, the very same string is found which of course results in an infinite loop
			if (xhr.responseText.indexOf('<div class'+'="page"') == -1) {
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
	}
