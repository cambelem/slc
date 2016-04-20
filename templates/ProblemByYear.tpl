<div class="row">
	<div class="col-md-10">
		<table class="table table-striped table-condensed">
		    <thead>
		        <tr>
		        	<th>Problem Type</th>
		            <!-- BEGIN problem_year_repeat -->
		    			<th>{YEAR}</th> 
		    		<!-- END problem_year_repeat -->
		        </tr>
		    </thead>
		    <tbody>

		    	<!-- BEGIN problem_repeat -->
		    	<tr> 
		    		<td>{PROBLEM_TYPE}</td>
		    		<td>{FRESHMAN}</td>
		    		<td>{SOPHOMORE}</td>
		    		<td>{JUNIOR}</td>
		    		<td>{SENIOR}</td>
		    		<td>{OTHER}</td>	
		    	</tr>
		    	<!-- END problem_repeat -->
		    	
		    	<tr>
		    		<td><strong>Totals</strong></td>
		    		<td>{FRESHMAN_TOTAL}</td>
		    		<td>{SOPHOMORE_TOTAL}</td>
		    		<td>{JUNIOR_TOTAL}</td>
		    		<td>{SENIOR_TOTAL}</td>
		    		<td>{OTHER_TOTAL}</td>
		    	</tr>
		    </tbody>
		</table>
	</div>
</div>



