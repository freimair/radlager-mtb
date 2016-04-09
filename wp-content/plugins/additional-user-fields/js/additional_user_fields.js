function post_user_data(myObject) {
	var object = myObject;

	// show spinner
	jQuery(object).children(".ajax_spinner").show();

	var postData = new FormData(myObject);
	postData.append("action", "update_user_data");
	jQuery.ajax({
		type: "post",
		url: data.ajax_url,
		data: postData,
		contentType: false,
		cache: false,
		processData: false,
		success: function (returndata) {
			// hide spinner and show success message
			jQuery(object).children(".ajax_spinner").hide();
			jQuery(object).children(".ajax_success").show();
			jQuery(object).children(".ajax_success").fadeOut(5000);
		},
		error: function (returndata) {
			// hide spinner and show failure message
			jQuery(object).children(".ajax_spinner").hide();
			jQuery(object).children(".ajax_error").show();
			jQuery(object).children(".ajax_error").fadeOut(5000);
		}
	});
}
