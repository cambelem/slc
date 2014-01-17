<?php
class GETReportBox extends AJAX {
	
	public function execute() {
		$reportPicker = "";
		
		// List of reports
		$reports = array();
		//$reports['studentsseen'] = "Students Seen";
		$reports['followupappts'] = "Appointment Statistics";
		$reports['intakebyproblemtype'] = "Intake by Problem Type";
		$reports['landlordtenant'] = "Landlord Tenant";
		$reports['conditionbylandlord'] = "Condition by Landlord";

		$reports['problembyyear'] = "Problem by Year in School";
		
		$reports['typeofcondition'] = "Type of Condition";
		$reports['typeofreferral'] = "Type of Referral";
		$reports['lawbyagency'] = "Problems With Law Enforcement by Agency";
		
		
		$rTypes = array();
        foreach( $reports as $r => $v ) { // types
        	$rTypes[] = array("VALUE" => $r, "NAME"=>$v);
        }
		
        $reportPicker = PHPWS_Template::process(array("reports"=>$rTypes), 'slc', 'ReportPicker.tpl');
		
		$this->addResult("report_picker", $reportPicker);    
	}
}
?>
