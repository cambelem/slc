<?php
class GETClientVisits extends AJAX {
	private $_table = "slc_visit";
	
	public function execute() {
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("msg", "No ID Supplied");
//			throw new IDNotSuppliedException();
			return;
		}

		$banner = $_REQUEST['banner_id'];
		
		$client = new Client($banner);
		
		// check that client exists in database, if not create
		$db = new PHPWS_DB("slc_client");
        $results = $db->loadObject($client);
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }

        if ( !$results ) {
        	$this->addResult("msg", "Client does not exist");
	        return;
        }
        
        $query = 'SELECT v.id as "VISITID", v.initial_date as "INITIALDATE"'.
        			' FROM slc_visit as v'.
        			' WHERE v.c_id = "'.$client->id.'"';
        
        $db = new PHPWS_DB();
		//$db->setTestMode();
        $results = $db->select(null, $query);
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }

        $visits = null;
        //test($results);
        foreach( $results as $r ) { // visits
        	$vid = $r['VISITID'];
        	
        	if (!isset($visits[$vid])) {
        	//	test("Creating new visit for id: ".$vid);
        		$visits[$vid] = new Visit();
        	}
        	
        	$visit = $visits[$vid];
        	$visit->id = $vid;
        	$visit->initial_date = $r['INITIALDATE'];
        	$visit->client_id = $client->id;
        	
        	        	
        	
        	// issues
	        $query = 'SELECT vii.id AS "VIIID", p.description AS "ISSUENAME", l.name as "LANDLORDNAME", i.landlord_id as "LANDLORDID", i.problem_id as "PROBLEMID", vii.i_id AS "ISSUEID", vii.counter AS "COUNTER", vii.resolve_date AS "RESOLVEDATE", vii.last_access AS "LASTACCESS"'.
	        			' FROM slc_visit_issue_index as vii'.
						' INNER JOIN slc_issue i ON vii.i_id=i.id'.
	        			' INNER JOIN slc_problem p ON i.problem_id=p.id'.
	        			' LEFT JOIN (slc_landlord l) ON (i.landlord_id = l.id)'.
						' WHERE vii.v_id = "'.$vid.'"';
	        
	        
	        $db = new PHPWS_DB();
			$iresults = $db->select(null, $query);
	        
	        if(PHPWS_Error::logIfError($results)){
	            throw new DatabaseException();
	        }
        	
        	foreach( $iresults as $ir ) { // visits
        		// set up issue
	        	$issue = new Issue($ir['ISSUEID']);
	        	$issue->name = $ir['ISSUENAME'];
	        	$issue->last_access = $ir['LASTACCESS'];
				$issue->counter = $ir['COUNTER'];
				$issue->resolution_date = $ir['RESOLVEDATE'];
				$issue->visit_issue_id = $ir['VIIID'];
				$issue->problem_id = $ir['PROBLEMID'];
				$issue->landlord_id = $ir['LANDLORDID'];
				$issue->landlord_name = (isset($issue->landlord_id)) ? " <span style='font-style:italic;'>with</span> ".$ir['LANDLORDNAME'] : null;

	        	// add issue
	        	$visit->addIssue($issue);
        	}
        	
        	//test($visit);
        	
        	//$visits[$vid] = $visit;
        }
        
        $this->addResult("visits", $visits);       
	}
}
?>
