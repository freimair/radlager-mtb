jQuery(document).ready(function(e){
	jQuery("form#notification_center_settings").submit(function(e){
		e.preventDefault();
		var formData = new FormData(this);
		formData.append("action", "notification_center_save_settings");

		jQuery.ajax({
			type : "post",
			url : data.ajax_url,
			data : formData,
			processData: false,
			contentType: false,
			success: function(response) {
					alert(response.result);
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

