<?php 
namespace slc\ajax;

class POSTIncrementVisit extends AJAX {
	private $_table = "slc_visit_issue_index";
	
	public function execute() {
		if ( !isset($_REQUEST['visit_issue_id']) ) {
			$this->addResult("msg", "No Visit_Issue ID Supplied");
//			throw new IDNotSuppliedException();
			return;
		}
		
		$db = new \PHPWS_DB($this->_table);
        $db->addWhere('id', $_REQUEST['visit_issue_id']);
		$results = $db->count();
		
		if(\PHPWS_Error::logIfError($results)){
            //throw new DatabaseException();
            $this->addResult("msg", "No Visit_Issue ID Supplied");
			return;
        }
        
        if ( $results == 0 ) {
        	// Row does not exist
        	$this->addResult("msg", "Row does not exist");
        	return;
        }
        
        $visitIssue = new \slc\indexes\VisitIssue();
		$results = $db->loadObject($visitIssue); // load
		
        if(\PHPWS_Error::logIfError($results)){
            //throw new DatabaseException();
            $this->addResult("msg", "No Visit_Issue ID Supplied");
			return;
        }
		
        $count = $visitIssue->getCounter();
        $visitIssue->setCounter($count + 1); // increment counter

        $time = timestamp();
        $visitIssue->setLastAccess($time); 


        $results = $db->saveObject($visitIssue);
		
        if(\PHPWS_Error::logIfError($results)){
            //throw new DatabaseException();
            $this->addResult("msg", "No Visit_Issue ID Supplied");
			return;
        }
		
        $this->addResult("msg", $results);
        $this->addResult("count", $visitIssue->getCounter());
	}

}

?>
