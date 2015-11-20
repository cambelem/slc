<?php
namespace slc\ajax; 

class POSTNewVisit extends AJAX {
	
	
	public function execute() {
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("warning", "No Banner ID Supplied");
//			throw new IDNotSuppliedException();
			return;
		}
		
		$time = timestamp();
		$visit = new \slc\Visit($_REQUEST['banner_id'], $time);

		$db = \Database::newDB();
		$pdo = $db->getPDO();
		$pdo->beginTransaction();
		try{

			// Save the Visit
	        $visitID = VisitFactory::saveVisit($pdo, $visit);

	        //$visitID = null;
	        if ($visitID == null)
	        {
	        	$warning = "Error with visitID";
	        	$this->addResult("error", $warning);
	        	$pdo->rollBack();
	        	return;
	        }
			$putBodyData = file_get_contents('php://input');
			$issuesData = json_decode($putBodyData);

			foreach ($issuesData as $issue)
			{
				$pid = null;
				$llid = null;

				if(array_key_exists('id', $issue))
				{
					$pid = $issue->id;
				}
				if(array_key_exists('llID', $issue))
				{
					$llid = $issue->llID;
				}

				if($pid == null)
				{
					$warning = "Error with issue problem id being null.";
		        	$pdo->rollBack();
		        	$this->addResult("error", $warning);
		        	return;
				}

				$i = new \slc\Issue($pid, $llid);
				$result = IssuesFactory::saveIssue($i);
				//$result = null;
				if ($result == null)
				{
					$warning = "Error with issue " . $pid . " " . $llid;
		        	$this->addResult("error", $warning);
		        	$pdo->rollBack();
		        	return;
				}

				$db = new \PHPWS_DB("slc_visit_issue_index");
	    		$vi = new \slc\indexes\VisitIssue($visitID, $result);
	    		$results = $db->saveObject($vi);

	    		if ($results == null)
				{
					$warning = "Error with visitIssue" . $pid . " " . $llid;
		        	$this->addResult("error", $warning);
		        	$pdo->rollBack();
		        	return;
				}	
			}
			
			$pdo->commit();
			$msg = "Successfully added: ";
			$this->addResult("success", $msg);
		} catch(PDOException $e){
			$db->rollBack();
			$this->addResult("error", $e);
		}

		
	}
}

?>
