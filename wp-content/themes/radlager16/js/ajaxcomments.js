function init_commentbutton(commentform) {
	commentform.prepend('<div id="comment-status" ></div>'); // add info panel before the form to provide feedback or errors
	var statusdiv=commentform.find('#comment-status'); // define the infopanel

	commentform.submit(function(){
		//serialize and store form data in a variable
		var formdata=commentform.serialize();
		//Add a status message
		statusdiv.html('<p>Einen Moment bitte...</p>');
		//Extract action URL from commentform
		var formurl=commentform.attr('action');
		//Post Form with data
		jQuery.ajax({
			type: 'post',
			url: formurl,
			data: formdata,
			error: function(XMLHttpRequest, textStatus, errorThrown){
				statusdiv.html('<p class="wdpajax-error" >You might have left one of the fields blank, or be posting too quickly</p>');
			},
			success: function(data, textStatus){
				commentform.parents(".comments-area").find("ol.comment-list").append(data);
				statusdiv.html('');
				commentform.find('textarea[name=comment]').val('');
			}
		});
		return false;
	});
}

var already_loaded_commentbutton;
jQuery(document).on("ready resize scroll", function() {
	// prepare array
	if(!(already_loaded_commentbutton instanceof Array))
		already_loaded_commentbutton = [];

	//for each map
	jQuery(".comment-form").each( function() {
		var postid = jQuery(this).find("input#comment_post_ID").val();

		// check if we loaded this specific map already
		if(-1 === already_loaded_commentbutton.indexOf(postid)) {
			init_commentbutton(jQuery(this));

			// memorize that we already loaded this specific map
			already_loaded_commentbutton.push(postid);
		}
	});
});
