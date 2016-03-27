jQuery(document).ready(function(){
	myObject = jQuery("input#radlager_membership_payment_claim");
	myObject.click(function(e){
		if(!myObject.attr("disabled")) {
			jQuery.ajax({
				type : "post",
				async : true,
				dataType : "json",
				url : data.ajax_url,
				data : {action: "radlager_membership_claim"},
				success: function(response) {
						myObject.attr("disabled", "true");
				}
			});
		}
	});
});
