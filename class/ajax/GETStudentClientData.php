<?php
namespace slc\ajax;

class GETStudentClientData extends AJAX {
    public function execute() {

		// Grabs a student object.
		$student = ClientFactory::getClientByBannerId($_REQUEST['banner_id']);

		// Encode the banner id.
        $encryptedBanner = encode($_REQUEST['banner_id']);

        // Grabs a client object based on the student.
		$client = ClientFactory::getClientByEncryBanner($encryptedBanner,  $student->getFirstName(),
                                               $student->getLastName(),
                                               $student->getFirstName() . ' ' . $student->getLastName());

		// Determines if the client is new
		if ($client == null)
		{
			$client = new \slc\Client($encryptedBanner, $student->getClassification(),
											   $student->getMajor(), $student->getLivingLocation());
            $client->setName($student->getFirstName() . ' ' . $student->getLastName());
            $client->setFirstName($student->getFirstName());
            $client->setLastName($student->getLastName());
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

        $clientData = array('id'                => $client->getId(),
                            'first_visit'       => $client->getFirstVisit(),
                            'name'              => $client->getName(),
                            'fname'             => $client->getFirstName(),
                            'lname'             => $client->getLastName(),
                            'major'             => $client->getMajor(),
                            'living_location'   => $client->getLivingLocation(),
                            'referral'          => $client->getReferral(),
                            'referralString'    => $client->getReferralString(),
                            'transfer'          => $client->getTransfer(),
                            'international'     => $client->getInternational());

        // Replace the 'FR' with 'Freshman', 'SO' with 'Sophomore', and so on

        switch ($client->getClassification()) {
            case 'FR':
                $clientData['classification'] = 'Freshman';
                break;
            case 'SO':
                $clientData['classification'] = 'Sophomore';
                break;
            case 'JR':
                $clientData['classification'] = 'Junior';
                break;
            case 'SR':
                $clientData['classification'] = 'Senior';
                break;
            default:
                $clientData['classification'] = 'Other';
                break;
        }

		// Sends the data out to be jason encoded
		$this->addResult("client", $clientData);
		$this->addResult("visit", $visits);
	}
}
