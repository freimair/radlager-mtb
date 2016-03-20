function post_user_data(myObject) {
	var postData = myObject.serialize();
	postData = "action=update_user_data&" + postData;
	jQuery.ajax({
		type: "post",
		async: true,
		dataType: "json",
		url: data.ajax_url,
		data: postData,
		success: function (returndata) {
			alert(returndata);
		}
	});
}
