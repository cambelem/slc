<div class="row">
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table table-striped">
			    <thead>
			        <tr>
			        	<th></th>
			            <!-- BEGIN landlord_issue_repeat -->
			    			<th>{ISSUE_NAME} </th>
			    		<!-- END landlord_issue_repeat -->
			    		<th>Landlord Total</th>
			        </tr>
			    </thead>
			    <tbody>
					<!-- BEGIN landlord_tenant_repeat -->
			    	<tr>
			    		<td>{LANDLORD_NAME}</td>
			    		<!-- BEGIN landlord_issues_repeat -->
                        <td>{LANDLORD_ISSUE}</td>
                        <!-- END landlord_issues_repeat -->
	    				<td>{LANDLORD_TOTAL}</td>
			    	</tr>
			    	<!-- END landlord_tenant_repeat -->

			    	<tr>
                        <td>Condition Total:</td>
                        <!-- BEGIN totals_repeat -->
			    		<td>{ISSUE_TOTAL}</td>
                        <!-- END totals_repeat -->
	    				<td>{OVERALL_TOTAL}</td>
	    			</tr>
			    </tbody>
			</table>
		</div>
	</div>
</div>
