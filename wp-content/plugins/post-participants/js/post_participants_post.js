jQuery(document).ready(function(){
	jQuery(document).on("click", ".post_participate", function(e){
		e.preventDefault();
		var post_id = jQuery(this).attr("data-post_id");
		var task = jQuery(this).attr("data-task");

		jQuery.ajax({
			type : "post",
			async : true,
			dataType : "json",
			url : data.ajax_url,
			data : {action: "post_participants_intent", post_id : post_id, task : task},
			success: function(response) {
				var current = jQuery("[class=post_participate][data-post_id=" + post_id + "]");
				if("leave" == response.result) {
					current.val("Abmelden");
					current.attr("data-task", response.result);
				} else if("join" == response.result) {
					current.val("Bin dabei!");
					current.attr("data-task", response.result);
				} else
					alert(response.result);
			}
		});
	});
});
