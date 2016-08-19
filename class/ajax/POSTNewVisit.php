<?php
namespace slc\ajax;

class POSTNewVisit extends AJAX {


//BREAK METHOD INTO HELPERS ***************************


	public function execute() {
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("warning", "No Banner ID Supplied");
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

	        if ($visitID == null)
	        {
	        	$warning = "Error with the visitID";
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

				if(isset($issue->id))
				{
					$pid = $issue->id;
				} else {
					$warning = "Error with the issue problem id being null.";
		        	$pdo->rollBack();
		        	$this->addResult("error", $warning);
		        	return;
				}

				if(isset($issue->llID))
				{
					$llid = $issue->llID;
				}

				$i = new \slc\Issue($pid, $llid);
				$i->setVisitId($visitID);
				$i->setCounter(0);
				$result = IssuesFactory::saveIssue($i);

				if ($result == null)
				{
					$warning = "Error with issue " . $pid . " " . $llid;
		        	$this->addResult("error", $warning);
		        	$pdo->rollBack();
		        	return;
				}
			}

			$pdo->commit();
			$msg = "Successfully Added: ";
			$this->addResult("success", $msg);
		} catch(\PDOException $e){
			$db->rollBack();
			$this->addResult("error", $e);
		}


	}
}
