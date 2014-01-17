<div id="CLIENT_ID" style="display:none;">{CLIENTID}</div>
<h1>{TITLE}
	<span style="display:inline-block;float:right;margin-right:5px;font-size:12px;">
		<span style="float:right;" id="RETURN-{VISITID}" class="button">Return</span>
	</span>
</h1>

{START_FORM}
	<div style="display:block;margin-left:5px;" id="committedIssues"><h3 style="border-bottom:1px solid #272727;">Issues Added: </h3><!-- Issues selected go here --></div>
	<span style="display:block;background:#FFEC8B;margin-top:8px;margin-bottom:8px;">
		<div style="display:inline-block;margin-left:5px;background:#FFEC8B;margin-top:8px;margin-bottom:8px;"><span style="font-weight:bold;">Selected Issue:</span> <span id="selectedIssueSpan">{SELECTED_ISSUES}</span></div>
		<div style="display:inline-block;margin-left:5px;" id="selectedLandlordSpan">{LANDLORD_PICKER}</div>
	</span>
	
{END_FORM}<hr />
<h2>Problems<div style="display:inline-block;float:right;margin-right:5px;"><span style="font-size:14px;float:right;" id="CREATEISSUE-{VISITID}" class="button">Create Issue</span></div></h2>
{PROBLEMS}

