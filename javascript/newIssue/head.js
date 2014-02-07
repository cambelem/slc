<script type="text/javascript">

var appendedIssues = 0;

    $(document).ready(function () {
    	$("#landlord_picker").hide();
    	
    	// Activate problem links
        $('span[id^="PROBLEM_"]').click( function() {
        	//alert("Link Clicked");
        	$("#selectedIssue").html($(this).prop('title'));
        	$("#selectedIssue").prop('title',$(this).prop('id').split("_")[1]); 
        	//alert("Selected Issue: "+$(this).prop('title'));
        	if ( $(this).prop('title').startsWith("Landlord") || $(this).prop('title').startsWith("Generic Landlord") || $(this).prop('title').startsWith("Generic Condition") ) { // of type landlord, show picker
        		$("#landlord_picker").val(0);
        		$("#landlord_picker").show();
        	} else { // not of type landlord, hide picker
        		$("#landlord_picker").hide();
        		$("#landlord_picker").val(0);
        	}
        });
        

    	// Create Issue Button
    	$('[id^="CREATEISSUE"]').on('click', null, null, function() { // Followup buttons
    		var visitID = $(this).prop('id').split('-')[1];
    		
    		// Get the other information ( problem, landlord )
    		var problemID = $("#selectedIssue").prop('title');
    		var landlordID = $("#landlord_picker").val();
    		
    		//alert("preparing to create new issue:\n  visitID: "+visitID);
    		
    		$.post(
    				'index.php?module=slc&action=POSTNewIssue',
    				{"visit_id":visitID,
    				 "problem_id":problemID,
    				 "landlord_id":landlordID},
    				function(data) {
    					 //alert(data);
    					
    					 // Display result
    					 $("#committedIssues").append("<span style='display:block;' id='appendedIssue-"+appendedIssues+"'>"+$("#selectedIssue").html()+"</span>");
    					 
    					 // Signify result
    					 $("#appendedIssue-"+appendedIssues)
	 					    .css('backgroundColor', '#FFFFFF')
	 					    .animate({backgroundColor: '#FADD76'}, 250);
    					 $("#appendedIssue-"+appendedIssues)
    					    .css('backgroundColor', '#FADD76')
    					    .animate({backgroundColor: '#FFFFFF'}, 1000);				 
    					 
    					 // reset
    					 $("#selectedIssue").html("[ none ]");
    					 $("#landlord_picker").hide();
    					 
    					 appendedIssues++;
    				});
    	});
    	
    	
    	// Return Button
    	$('[id^="RETURN"]').on('click', null, null, function() { // Followup buttons
    		var clientID = $("#CLIENT_ID").html();
    		$.post(
    			'index.php?module=slc&action=POSTSendMail',
    			{"client_id": clientID},
    			function() {
	    			
	    			//alert(clientID);
	    			
	    			// redirect 
					window.location ="index.php?module=slc&view=Client&banner_id="+ clientID;
	    			
    			})    		
    	});
    });
    
    // source: http://stackoverflow.com/questions/646628/javascript-startswith
    String.prototype.startsWith = function (str){
        return this.slice(0, str.length) == str;
    };
    
    String.prototype.endsWith = function (str){
        return this.slice(-str.length) == str;
    };
</script>
