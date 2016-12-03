function frontend_create_post_stuff(current, copy){
	if(!copy)
		copy=false;
	var post_id = current.attr("data-post_id");
	var type = current.attr("data-type");
	if(jQuery("div#edit-post-"+post_id+"-form").is(':empty')) {
		var categories = JSON.parse(current.attr("data-categories"));

		// show spinner
		jQuery(current).parent().children(".ajax_spinner").show();

		jQuery("div#edit-post-"+post_id+"-form").load(fcpdata.ajax_url, {"action" : "frontend_edit_post_form", "post_id" : post_id, "category_ids" : categories, type : type}, function() {
			jQuery(current).parent().children(".ajax_spinner").hide();

			// trigger setup for all ACF fields in case there are some that need initializing
			jQuery(document).trigger('acf/setup_fields', jQuery("div#edit-post-"+post_id+"-form"));

			// hook the submit button in order to do an ajax submit
			jQuery("div#edit-post-"+post_id+"-form input#submit").click(function(e) {
				e.preventDefault();

				// show spinner
				jQuery(this).parent().parent().children(".ajax_spinner").show();

				// the new and shiny editor does some woodoo with iframes and stuff. Hence,
				// the content you see is not in the submittable form. Hence, we have to
				// manually save its contents back to the form
				tinymce.triggerSave();

				// get form
				form = jQuery("div#edit-post-"+post_id+"-form form");

				// if we want to copy the post
				if(copy)
					form.find("input[name='post_id']").attr("value", "new");

				// create formdata for transfer
				var postData = new FormData(form[0]);
				postData.append("action", "frontend_save_post_form");
				jQuery.ajax({
					type: "post",
					url: fcpdata.ajax_url,
					data: postData,
					contentType: false,
					cache: false,
					processData: false,
					success: function (returndata) {
						if('new' !== post_id) {
							// remove input field
							remove_edit_field(post_id, type);
						} else {
							if('event' == type)
								location.reload();
							else {
								remove_edit_field(post_id, type);
							}
						}
						jQuery(current).parent().children(".ajax_success").show();
						jQuery(current).parent().children(".ajax_success").fadeOut(5000);
					}
				});
			});

			// replace button action
			jQuery("input#edit-post-"+post_id).val(fcpdata.cancel);
		});
	} else {
		remove_edit_field(post_id, type);
	}
}

function remove_edit_field(post_id, type) {
		jQuery("div#edit-post-"+post_id+"-form").empty();
		if('undefined' !== typeof tinyMCE)
			tinyMCE.editors=[];
		jQuery(".mce-container").remove();
		jQuery("input#edit-post-"+post_id).val("new" != post_id ? fcpdata.edit_button : ("event" == type ? fcpdata.edit_event : fcpdata.edit_media));
}
