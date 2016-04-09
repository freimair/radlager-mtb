jQuery(document).ready(function(e){
	jQuery("form#notification_center_settings").submit(function(e){
		var myObject = this;

		e.preventDefault();

		// show spinner
		jQuery(myObject).children(".ajax_spinner").show();

		var formData = new FormData(myObject);
		formData.append("action", "notification_center_save_settings");

		jQuery.ajax({
			type : "post",
			url : data.ajax_url,
			data : formData,
			processData: false,
			contentType: false,
			success: function (returndata) {
				// hide spinner and show success message
				jQuery(myObject).children(".ajax_spinner").hide();
				jQuery(myObject).children(".ajax_success").show();
				jQuery(myObject).children(".ajax_success").fadeOut(5000);
			},
			error: function (returndata) {
				// hide spinner and show failure message
				jQuery(myObject).children(".ajax_spinner").hide();
				jQuery(myObject).children(".ajax_error").show();
				jQuery(myObject).children(".ajax_error").fadeOut(5000);
			}
		});
	});

});

function NotificationCenter_DeleteMessage(object, messageid) {
	jQuery.ajax({
		type : "post",
		url : data.ajax_url,
		dataType : "json",
		data : {"action" : "notification_center_delete_message", "messageid": messageid},
		success: function(response) {
				object.parent().remove();
		}
	});
};

