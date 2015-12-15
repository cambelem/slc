<?php
namespace slc\ajax;

class GETStudentClientData extends AJAX {
    public function execute() {

		// Grabs a student object.
		$student = ClientFactory::getClientByBannerId($_REQUEST['banner_id']);
		
		// Encode the banner id.
        $encrypedBanner = encode($_REQUEST['banner_id']);

        // Grabs a client object based on the student.
		$client = ClientFactory::getClientByEncryBanner($encrypedBanner,  $student->getFirstName(), 
                                               $student->getLastName(), 
                                               $student ->getFirstName() . ' ' . $student->getLastName());

		// Determines if the client is new
		if ($client == null)
		{
			$client = new \slc\Client($encrypedBanner, $student->getClassification(), 
											   $student->getMajor(), $student->getLivingLocation());
			ClientFactory::saveClient($client);
		}

		// Turns the epoch value to a better date and time.
		$client->setFirstVisit(prettyTime($client->getFirstVisit()));


        $cReferral = $client->getReferral();
		 // Check if existing client has referral set        
		if ($cReferral > 0) 
		{						
        	$results = ClientFactory::getReferralType($cReferral);
        	$client->setReferralString($results[0]["name"]);            
        } 


		//Get Visits
		$visits = VisitFactory::getVisitByCId($client->getId());

		if (!empty($visits))
		{
			foreach ($visits as $visit)
			{
				// Turns the epoch value to a better date and time.
				$visit->setInitialDate(prettyTime($visit->getInitialDate()));
				// Adds each issue object to each given visit
				$visit->issues = IssuesFactory::getIssueByVisitId($visit->getId());
			}
		}

		// Sends the data out to be jason encoded
		$this->addResult("client", $client);
		$this->addResult("visit", $visits);     
	}
}
 