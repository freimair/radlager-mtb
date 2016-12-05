function init_map(id, lat, lng, address) {
	var myOptions = {zoom:14,
							center:new google.maps.LatLng(lat, lng),
							mapTypeId: google.maps.MapTypeId.ROADMAP
							};
	var map = new google.maps.Map(document.getElementById("gmap_canvas_" + id), myOptions);
	var marker = new google.maps.Marker({map: map,position: new google.maps.LatLng(lat, lng)});
	var infowindow = new google.maps.InfoWindow({content:address});

	google.maps.event.addListener(marker, "click", function(){infowindow.open(map,marker);});
	infowindow.open(map,marker);
}


var already_loaded_maps;
jQuery(document).on("ready resize scroll", function() {
	// prepare array
	if(!(already_loaded_maps instanceof Array))
		already_loaded_maps = [];

	//for each map
	jQuery(".gmap_canvas").each( function() {
		var postid = jQuery(this).attr('postid');

		// check if we loaded this specific map already
		if(-1 === already_loaded_maps.indexOf(postid)) {
			init_map(postid, jQuery(this).attr('lat'), jQuery(this).attr('lng'), jQuery(this).attr('address'));

			// memorize that we already loaded this specific map
			already_loaded_maps.push(postid);
		}
	});
});

