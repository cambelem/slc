<?php 

class POSTNewVisit extends AJAX {
	private $_table = "slc_visit";
	
	public function execute() {
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("msg", "No Banner ID Supplied");
//			throw new IDNotSuppliedException();
			return;
		}
		
		$visit = new Visit();
		$visit->initial_date = timestamp(); // TOOD: Use current timestamp as int
		$visit->c_id = $_REQUEST['banner_id'];
		
		//test($visit);
		
		// Save the Visit
        $db = new PHPWS_DB($this->_table);
		$results = $db->saveObject($visit);
        
		if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }
        
        $visitID = $results;
        
        $html = '<table style="width:100%;"><tr><td colspan="2"><span style="font-weight:bold;">'.prettyTime($visit->initial_date).'</span><span style="position:relative; font-size:10px;right:-5px;font-weight:100;padding-top:10px;"><span style="font-size:12px;font-weight:bold;">[</span> <a href="index.php?module=slc&view=NewIssue&visitid='.$visitID.'">NEW ISSUE</a> <span style="font-size:12px;font-weight:bold;">]</span></span></td></tr>';
        
        
        $this->addResult("msg", $results);
        $this->addResult("html", $html);
        $this->addResult("visitID", $visitID);
	}

}

?>
