jQuery(document).ready(function(){
	jQuery(document).on("click", ".post_participate", function(e){
		e.preventDefault();
		var post_id = jQuery(this).attr("data-post_id");

		jQuery.ajax({
			type : "post",
			async : true,
			dataType : "json",
			url : data.ajax_url,
			data : {action: "post_participants_intent", post_id : post_id},
			success: function(response) {
				alert("message: " + response.message);
			}
		});
	});
});
