<table id="visits_table" class="table">
	<tr>
		<th><span class="pull-left">Visits:</span></th>
		<th><button type="button" id="NEWVISIT" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> New Visit</button></th>
	</tr>
	<tr><td colspan="2">
	<div id="VISITLIST">
		<!-- BEGIN visits -->
		<table style="width:100%;">
		<tr>
			<td colspan="2"><span style="font-weight:bold;">{VISIT_DATE}</span><span style="position:relative; font-size:10px;right:-5px;font-weight:100;padding-top:10px;"><span style="font-size:12px;font-weight:bold;">[</span> {NEW_ISSUE} <span style="font-size:12px;font-weight:bold;">]</span></span></td>
		</tr>
		<tr>
			<td id="VISIT{VISITID}-Issues" style="" COLSPAN="2">
			<!-- BEGIN issues -->
			<table id="VISIT_ISSUE-{VISITID}-{ISSUEID}" class="table issue">
				<tr>
					<td id="ISSUE{ISSUEID}-Issue" style="width:350px;">
					{ISSUE}{LANDLORD}
					</td>
					<td style="width:75px;" id="TD-VISITCOUNT-{VISSITISSUEID}">
					<span id="VISITCOUNT-{VISSITISSUEID}">{VISITCOUNT}</span> visit(s)
					</td>
					<td style="width:75px;text-align:right;">
					<button type="button" id="FOLLOWUP-{VISSITISSUEID}" class="btn btn-default"><i class="fa fa-plus"></i> {FOLLOWUP}</button>
					</td>
				</tr>
				<tr>
					<td id="ISSUE{ISSUEID}-Details" style="font-size:10px;position:relative;top:-7px;">
					<span style="font-style:italic;">Last Accessed {LASTACCESS}</span>
					</td>
				</tr>
				
			</table>
			<!-- END issues -->
			</td>
		</tr>
		</table>
		<!-- END visits -->
	</div>
	</td></tr>
</table>
