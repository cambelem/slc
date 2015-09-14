<?php
namespace slc\ajax; 

class POSTNewVisit extends AJAX {
	
	
	public function execute() {
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("msg", "No Banner ID Supplied");
//			throw new IDNotSuppliedException();
			return;
		}
		
		$time = timestamp();
		$visit = new \slc\Visit($_REQUEST['banner_id'], $time);
		 // TOOD: Use current timestamp as int


		//test($visit);
		
		// Save the Visit
        $results = VisitFactory::saveVisit($visit);

        $visitID = $results;
        
        $html = '<table style="width:100%;"><tr><td colspan="2"><span style="font-weight:bold;">'.prettyTime($visit->getInitialDate()).'</span><span style="position:relative; font-size:10px;right:-5px;font-weight:100;padding-top:10px;"><span style="font-size:12px;font-weight:bold;">[</span> <a href="index.php?module=slc&view=NewIssue&visitid='.$visitID.'">NEW ISSUE</a> <span style="font-size:12px;font-weight:bold;">]</span></span></td></tr>';
        
        
        $this->addResult("msg", $results);
        $this->addResult("html", $html);
        $this->addResult("visitID", $visitID);
	}

}

?>
