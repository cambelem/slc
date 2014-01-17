<script type="text/javascript">
var clientID;
var new_type;

$(document).ready( function() {
	clientID = $("#CLIENTID").html();
	
	/********************
	 * Activate buttons *
	 ********************/

	// Followup Button
	$('[id^="FOLLOWUP"]').on('click', null, null, function() { // Followup buttons
		idString = $(this).prop('id');
		idString = idString.split('-');
		var visitIssueID = idString[1];
		var followupDiv = $(this);
		var counterDiv = $("#VISITCOUNT-"+visitIssueID);
		var shakeDiv = $("#TD-VISITCOUNT-"+visitIssueID);
		
		$.post(
			'index.php?module=slc&action=POSTIncrementVisit',
			{"visit_issue_id":visitIssueID},
			function(data) {
				data = jQuery.parseJSON(data);
				counterDiv.html(data.count);

				shakeDiv.effect('shake', { times:1 }, 21);
				//shakeDiv.animate({'left' : "-=1px"}); // return floating div to original position
				//followupDiv.animate({'left' : "-=1px"}); // return floating div to original position
				
			});
	}); // End Followup Button
	
	
	// New Visit Button
	$('[id^="NEWVISIT"]').on('click', null, null, function() { // Followup buttons
		var clientID = $("#CLIENT_ID").html();
		var visitsDiv = $("#VISITLIST"); // HTML will be APPENDED onto this
		
		$.post(
				'index.php?module=slc&action=POSTNewVisit',
				{"banner_id":clientID},
				function(data) {
					//alert(data);
					data = jQuery.parseJSON(data);
					visitsDiv.append(data.html);
					
					// Redirect as if the new visit were clicked
					window.location = "index.php?module=slc&view=NewIssue&visitid="+data.visitID;
				});
	});
	
	
	/*********************
	 * Activate Dropdown *
	 *********************/
	$("#referral_picker").change(function() {
		var clientID = $("#CLIENT_ID").html();
		var referral_type = ($("#referral_picker").val() != -1 ? $("#referral_picker").val() : null);
		new_type = referral_type; // global access inside timeout
		
		$.post(
				'index.php?module=slc&action=POSTReferralType',
				{"banner_id":clientID, "referral_type":referral_type},
				
				function(data) {
					// Give 6.3 seconds to change option before comitting
					setTimeout(function(){
						if ( new_type != null ) {
							$("#referral_picker").fadeOut("slow", function () {
							  $("#referralDiv").html("&nbsp;Referred By: "+$("#referral_picker option:selected").text());
							  $("#referralDiv").animate({backgroundColor: '#FFFFFF'}, 1000);	
							  $("#referral_picker").hide();
				
						  });
						}}, 6300);
				});
		});
});
</script>
