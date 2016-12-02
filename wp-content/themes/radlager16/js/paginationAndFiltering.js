	// make it global so that we can stop loading things if we need it
	var loadmore = 'on'; // ready to go

	// these scripts control the ajax pagination
	jQuery(function(){
		var page = 2; // start at page 2

		// hook the scroll, resize and ready events to see if the spinner is visible
		jQuery(document).on('scroll resize ready', function() {
			if ('on' == loadmore && jQuery(window).scrollTop() + jQuery(window).height() + 60 > jQuery('#spinner').offset().top) {
				loadmore = 'off';
				jQuery('#spinner').css('visibility', 'visible');

				// load new content into temporary container <newContent> and append its .children() to the main content
				if(-1 < window.location.href.indexOf("?"))
					delimiter = "&";
				else
					delimiter = "?";
				result = jQuery('<newContent>').load(window.location + delimiter + "page=" + page + " .post", function() {
					page++;
					loadmore = 'on';
					jQuery('#spinner').css('visibility', 'hidden');
					contents = result.children();
					jQuery('#masonry-grid').append(contents).masonry( 'appended', contents );
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
			// cancel filter handling if we have the searchbox at hand
			if(jQuery.contains(this, jQuery("#searchbox")[0]))
				return;
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

		jQuery("#searchbox").on("input", function() {
			updateFilter();
			// retrigger the check event. in case the spinner just moved into view due to search inputs
			jQuery(document).trigger("resize");
		});

		// fetch searchterm from URL for permalinks
		searchterm = window.location.href.split("?")[1];
		if(null != searchterm) {
			permalinkbox = jQuery("#permalink");
			permalinkbox.attr("value", searchterm);
			permalinkbox.addClass("selected");
			permalinkbox.show();
			updateFilter();
			// retrigger the check event. in case the spinner just moved into view due to search inputs
			jQuery(document).trigger("resize");
		}
	});

	// this function checks applies the filter to the articles
	function updateFilter() {
		loadmore = 'on';
		selected = jQuery('.filter.selected');
		if(0 == selected.length) {
			jQuery("article[class^=filter-]").show();
		} else if("permalink" === selected.attr('id')) {
			jQuery("article[class^=filter-]").hide();
			result = jQuery("#" + jQuery('#permalink')[0].getAttribute('value'));
			if(1 <= result.length) {
				result.show();
				// stop loading stuff
				loadmore = 'off';
				jQuery('#spinner').css('visibility', 'hidden');
			}
		} else {
			jQuery("article[class^=filter-]").hide();
			jQuery(".filter-" + selected[0].getAttribute('value')).show();
		}

		// do the search box stuff. logical AND!
		searchterms = jQuery("#searchbox").val();
		if(3 < searchterms.length) {
			jQuery("article[class^=filter-]:not(:contains(" + searchterms + "))").hide();
		}
		jQuery('#masonry-grid').masonry(); // update grid
	}
