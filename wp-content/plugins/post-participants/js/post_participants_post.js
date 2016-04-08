jQuery(document).on('resize ready', function(){
	jQuery(".post_participate").off(".click");
	jQuery(document).on("click", ".post_participate", function(e){
		e.preventDefault();
		var post_id = jQuery(this).attr("data-post_id");
		var task = jQuery(this).attr("data-task");

		jQuery.ajax({
			type : "post",
			async : true,
			dataType : "json",
			url : ppdata.ajax_url,
			data : {action: "post_participants_intent", post_id : post_id, task : task},
			success: function(response) {
				var current = jQuery("[class=post_participate][data-post_id=" + post_id + "]");
				if("leave" == response.result) {
					current.val(ppdata.leave);
					current.attr("data-task", response.result);
				} else if("join" == response.result) {
					current.val(ppdata.join);
					current.attr("data-task", response.result);
				} else
					console.log(response.result);
			}
		});
	});

	jQuery(".PostParticipantsKickParticipant").off("click");
	jQuery(".PostParticipantsKickParticipant").click(function(e) {
		var user_id = jQuery(this).attr("data-user_id");
		var post_id = jQuery(this).attr("data-post_id");
		jQuery.ajax({
			type : "post",
			async : true,
			dataType : "json",
			url : data.ajax_url,
			data : {action: "post_participants_intent", user_id : user_id, post_id : post_id, task : "kick"},
			success: function(response) {
				var current = jQuery("[class=PostParticipantsKickParticipant][data-post_id=" + post_id + "][data-user_id=" + user_id + "]");
				current.parent().remove();
			}
		});
	});
});
