<div id="CLIENT_ID" style="display:none;">{CLIENTID}</div>
<h1>{TITLE}
	<span style="float:right;margin-right:5px;font-size:12px;">
		<button type="button" id="RETURN-{VISITID}" class="btn btn-default"><i class="fa fa-chevron-left"></i> Return</button>
	</span>
</h1>

{START_FORM}
	<div style="display:block;margin-left:5px;" id="committedIssues"><h3 style="border-bottom:1px solid #272727;">Issues Added: </h3><!-- Issues selected go here --></div>
	<span style="display:block;background:#FFEC8B;margin-top:8px;margin-bottom:8px;">
		<div style="display:inline-block;margin-left:5px;background:#FFEC8B;margin-top:8px;margin-bottom:8px;"><span style="font-weight:bold;">Selected Issue:</span> <span id="selectedIssueSpan">{SELECTED_ISSUES}</span></div>
		<div style="display:inline-block;margin-left:5px;" id="selectedLandlordSpan">{LANDLORD_PICKER}</div>
	</span>
	
{END_FORM}<hr />
<h2>Problems<div style="float:right;"><button type="button" id="CREATEISSUE-{VISITID}" class="btn btn-primary"><i class="fa fa-plus"></i> Create Issue</button></div></h2>
{PROBLEMS}
