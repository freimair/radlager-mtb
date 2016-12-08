function renderWeather(postid, eventdate) {
	return function(data) {
		eventdate.setHours(0);
		eventdate.setMinutes(0);
		eventdate.setSeconds(0);
		eventdate.setMilliseconds(0);

		for(i = 0; i < data.query.results.channel.length; i++) {
			var forecastdate = new Date(data.query.results.channel[i].item.forecast.date);
			if(0 == eventdate - forecastdate) {
				var icontext = data.query.results.channel[i].item.forecast.text;
				icontext = icontext.toLocaleLowerCase().replace(" ", "_");
				jQuery("div#event_" + postid).append('<div class="weather_icon weather_' + icontext + '"/>');
				break;
			}
		}
	}
};

function init_weather(postid, address, eventdate) {
	jQuery.getJSON("https://query.yahooapis.com/v1/public/yql?q=select item.forecast from weather.forecast where woeid in (select woeid from geo.places(1) where text='" + address + "')&format=json", renderWeather(postid, eventdate));
}

var already_loaded_weather;
jQuery(document).on("ready resize scroll", function() {
	// prepare array
	if(!(already_loaded_weather instanceof Array))
		already_loaded_weather = [];

	//for each map
	jQuery(".event_weather").each( function() {
		var postid = jQuery(this).attr('postid');

		// check if we loaded this specific map already
		if(-1 === already_loaded_weather.indexOf(postid)) {
			init_weather(jQuery(this).attr('data-id'), jQuery(this).attr('data-address'), new Date(jQuery(this).attr('data-date')));

			// memorize that we already loaded this specific map
			already_loaded_weather.push(postid);
		}
	});
});

