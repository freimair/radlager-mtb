function frontend_create_post_stuff(current){
	if(jQuery("div#edit-post-form").is(':empty')) {
		var post_id = current.attr("data-post_id");
		var categories = JSON.parse(current.attr("data-categories"));

		jQuery('div#edit-post-form').load(data.ajax_url, {"action" : "frontend_edit_post_form", "post_id" : post_id, "category_ids" : categories}, function() {
			// trigger setup for all ACF fields in case there are some that need initializing
			jQuery(document).trigger('acf/setup_fields', jQuery('div#edit-post-form'));

			// hook the submit button in order to do an ajax submit
			jQuery('div#edit-post-form input#submit').click(function(e) {
				e.preventDefault();

				// the new and shiny editor does some woodoo with iframes and stuff. Hence,
				// the content you see is not in the submittable form. Hence, we have to
				// manually save its contents back to the form
				tinymce.triggerSave();

				var postData = new FormData(jQuery('div#edit-post-form form')[0]);
				postData.append("action", "frontend_save_post_form");
				jQuery.ajax({
					type: "post",
					url: data.ajax_url,
					data: postData,
					contentType: false,
					cache: false,
					processData: false,
					success: function (returndata) {
						location.reload();
					}
				});
			});

			// replace button action
			jQuery("input#edit-post").val("nein, sorry, doch nicht...");
		});
	} else {
		jQuery("div#edit-post-form").empty();
		tinyMCE.editors=[];
		jQuery(".mce-container").remove();
		jQuery("input#edit-post").val("<?php echo $caption; ?>");
	}
}
