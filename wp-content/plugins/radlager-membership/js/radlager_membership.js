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
						location.reload();
				}
			});
		}
	});
});

function radlager_membership_confirm(object, userid) {
	function Task(object, userid) {
		this.id = userid;
		this.domobject = object;
		this.execute = function() {jQuery.ajax({
				type : "post",
				async : true,
				dataType : "json",
				url : data.ajax_url,
				context: this,
				data : {action: "radlager_membership_confirm", userid:this.id},
				success: function(response) {
						this.domobject.parent()[0].innerHTML="confirmed";
				}
			});
		}
	}
	var a = new Task(object, userid);
	a.execute();
}
