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
        else
        {
        	$this->addResult('referralSet', false);
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
		
		/*
		$ajax = \slc\AjaxFactory::get("client");
		$ajax->loadCall("GETClientData");
		$ajax->execute();
		
		$result = $ajax->result(); 
		


		if (isset($result['msg']) && $result['msg'] == "Client not in ASU Database" ) {
			$HTMLcontent .= "<span style='font-weight:bold;'>Client not in ASU Database<br /></span><span style='font-size:-1;margin-left:20px;'>This could be due to the client being a non-student or the database not being updated.</span>";
			return parent::useTemplate($HTMLcontent); // Insert into the accessible div
		}
			
		$client = $result['client'];
		
		// Test for new client creation
		$newClient = $result['newFlag'] || !$result['referralSet'];
		//test($result);
		
		if ( $newClient ) {
			$ajax = \slc\AjaxFactory::get("referral");
			$ajax->loadCall("GETReferralBox");
			$ajax->execute();
			
			$result = $ajax->result(); 
			$r = $result['referral_picker'];
		
			$referral = "<div id='referralDiv'>" . $r . "</div>";
		} else {
			$referral = "<div id='referralDiv'>" . $client->referralString."</div>";
		}
		
		
		$banner = $client->id;

		$content = array();
		$tpl = new \PHPWS_Template('slc');
		$tpl->setFile('ClientVisits.tpl');
		$content['CLIENT_ID'] = $client->id;
		$content['CLIENT_NAME'] = $_SESSION['cname'];
		$content['CLIENT_INFO'] = $client->classification." - ".$client->major." Major";
		$content['FIRST_VISIT'] = prettyTime($client->first_visit);
		
		
		
		// Get visits
		$ajax = \slc\AjaxFactory::get("visits");
		$ajax->loadCall("GETClientVisits");
		$ajax->execute();
		$visits = $ajax->result(); 
		$visits = $visits['visits'];
		
        if(!empty($visits)){
            foreach ($visits as $visit) {
                //print_r($visit);
                $visitTpl['VISITID'] = $visit->id;
                $visitTpl['VISIT_DATE'] = prettyTime($visit->initial_date);
                $visitTpl['NEW_ISSUE'] = "<a href='index.php?module=slc&view=NewIssue&visitid=".$visit->id."'>NEW ISSUE</a>";
        
                
                // foreach issue per visit, keep array with array pointer
                foreach ($visit->issues as $issue) {
                    //print_r($issue);
                    $issueTpl['ISSUEID'] = $issue->id;
                    $issueTpl['ISSUE'] = $issue->name;
                    $issueTpl['VISITCOUNT'] = $issue->counter;
                    $issueTpl['FOLLOWUP'] = "Follow Up";
                    $issueTpl['LASTACCESS'] = prettyTime($issue->last_access)." (".prettyAccess($issue->last_access).")";
                    $issueTpl['VISSITISSUEID'] = $issue->visit_issue_id;
                    $issueTpl['LANDLORD'] = $issue->landlord_name;
                    
                    $tpl->setCurrentBlock("issues");
                    $tpl->setData($issueTpl);
                    $tpl->parseCurrentBlock();
                }
                
                $tpl->setCurrentBlock("visits");
                $tpl->setData($visitTpl);
                $tpl->parseCurrentBlock();
            
            }
        }
			
	 	$content['CLIENT_VISITS'] = $tpl->get();
	 	$content['CLIENT_BANNER'] = $banner;
	 	$content['REFERRAL'] = $referral; 
	 		
		javascriptMod('slc', 'viewClient');
			 
	 	$HTMLcontent .= \PHPWS_Template::process($content, 'slc', 'Client.tpl');
	 	
	 	return parent::useTemplate($HTMLcontent); // Insert into the accessible div
        */
        
	}
}
?>