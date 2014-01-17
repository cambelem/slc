<?php 

class POSTNewIssue extends AJAX {
	private $_table = "slc_visit_issue_index";
	
	public function execute() {

		if ( !isset($_REQUEST['visit_id']) ) {
			$this->addResult("msg", "No Visit ID Supplied");
			throw new Exception('Missing visit ID.');
			return;
		}
		
		if ( !isset($_REQUEST['problem_id']) ) {
			$this->addResult("msg", "No Problem ID Supplied");
			throw new Exception('Missing problem ID.');
			return;
		}

		// INSERT INTO ISSUE
		$db = new PHPWS_DB("slc_issue");
        $i = new Issue();
        $i->problem_id = $_REQUEST['problem_id'];
        
        if ($_REQUEST['landlord_id'] != -1) {
            // If $_REQUEST['landlord_id'] is set, use that for $i->landlord_id
            $i->landlord_id = $_REQUEST['landlord_id'];
        } elseif (($_REQUEST['problem_id'] >= 1 && $_REQUEST['problem_id'] <= 24) || $_REQUEST['problem_id'] == 47) {
            // If $_REQUEST['landlord_id'] is not set, but the problem specified by $_REQUEST['problem_id']
            // is in the Landlord-Tenant tree, use id 94 which is 'Other / Unspecified' in the database.
            $i->landlord_id = 94;
        } else {
            // If $_REQUEST['landlord_id'] is not set, and the problem specified by $_REQUEST['problem_id']
            // is not in the Landlord_Tenant tree, use null because the problem is not linked with a landlord.
            $i->landlord_id = null;
        }
	
        $results = $db->saveObject($i);

		$this->addResult("createdIssue", $results);
		
		// $results will be index
		// INSERT INTO VISIT_ISSUE_INDEX
		$db = new PHPWS_DB("slc_visit_issue_index");
        $vi = new VisitIssue();
        $vi->v_id = $_REQUEST['visit_id'];
        $vi->i_id = $results;
        $vi->counter = 1;
        $vi->last_access = timestamp();
		$results = $db->saveObject($vi);
        
        if(PHPWS_Error::logIfError($results)){
        //    throw new DatabaseException();
        }

        $this->addResult("msg", $results);
	}

}

?>
