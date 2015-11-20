<?php
namespace slc\ajax;

class GETStudentClientData extends AJAX {
    public function execute() {
       
    	$HTMLcontent = "";
		$referral = "";

		// Need to check for banner id's that aren't real.

		$student = ClientFactory::getClientByBannerId($_REQUEST['banner_id']);
		
		// encode banner
        $encrypedBanner = encode($_REQUEST['banner_id']);

		$client = ClientFactory::getClientByEncryBanner($encrypedBanner,  $student->getFirstName(), 
                                               $student->getLastName(), 
                                               $student ->getFirstName() . ' ' . $student->getLastName());

		if ($client == null)
		{
			$client = new \slc\Client($encrypedBanner, $student->getClassification(), 
											   $student->getMajor(), $student->getLivingLocation());
			ClientFactory::saveClient($client);
		}

		// Turns the epoch value to a better date and time.
		$client->setFirstVisit(prettyTime($client->getFirstVisit()));

		 // Check if existing client has referral set
        $cReferral = $client->getReferral();


		if ($cReferral > 0) {						
        	// Add actual text of referral into client

    
        	$results = ClientFactory::getReferralType($cReferral);
        	
        	$client->setReferralString($results[0]["name"]); 
   

        	//$client->setReferralString($results[0]["name"]); 
        	//$this->addResult('referralSet', true);
            
        } 


		//Get Visits
		$visits = VisitFactory::getVisitByCId($client->getId());

		if (!empty($visits))
		{
			foreach ($visits as $visit)
			{
				// Turns the epoch value to a better date and time.
				$visit->setInitialDate(prettyTime($visit->getInitialDate()));
				$visit->issues = IssuesFactory::getIssueByVisitId($visit->getId());
			}
		}

//      $issueTpl['LASTACCESS'] = prettyTime($issue->last_access)." (".prettyAccess($issue->last_access).")";

		$this->addResult("client", $client);
		$this->addResult("visit", $visits);     
	}
}
?>