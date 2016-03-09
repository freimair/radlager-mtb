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
				if("ok" == response.result)
					jQuery("[class=post_participate][data-post_id=" + post_id + "]").val("Abmelden");
				else
					alert(response.result);
			}
		});
	});
});
