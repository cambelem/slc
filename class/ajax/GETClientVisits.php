<?php

namespace slc\ajax;


class GETClientVisits extends AJAX {
	private $_table = "slc_visit";
	
	public function execute() {
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("msg", "No ID Supplied");
//			throw new IDNotSuppliedException();
			return;
		}

		$banner = $_REQUEST['banner_id'];
		$client = new \slc\Client($banner);
		
		// check that client exists in database, if not create
		$db = new \PHPWS_DB("slc_client");
        $results = $db->loadObject($client);
        
        if(\PHPWS_Error::logIfError($results)){
            throw new \slc\exceptinos\DatabaseException();
        }

        if ( !$results ) {
        	$this->addResult("msg", "Client does not exist");
	        return;
        }

        $clientId = $client->getId();
        $results = VisitFactory::getVisitByClientId($clientId);

        $visits = null;
        //test($results);

        if ($results != null)
        {
            foreach( $results as $r ) { // visits
            	// id being the id from slc_visit
                $vid = $r['id'];
            	
            	if (!isset($visits[$vid])) {
            	//	test("Creating new visit for id: ".$vid);
            		$visits[$vid] = new \slc\Visit();
            	}
            	
                // initial_date being initial_date from slc_visit
            	$visit = $visits[$vid];
            	$visit->setId($vid);
            	$visit->setInitialDate($r['initial_date']);
            	$visit->setClientId($client->getId());
            	
            	        	
            	// issues
    			$issue = IssuesFactory::getIssueByVisitId($vid);
            	for ($i = 0; $i < count($issue); $i++)
                {
                    $visit->addIssue($issue[$i]);
                }
            }
        }

        $this->addResult("visits", $visits);       
	}
}
?>
