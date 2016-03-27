function post_user_data(myObject) {
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
			alert(returndata);
		}
	});
}
