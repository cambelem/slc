<?php
namespace slc\ajax;


// NO LONGER USED


class GETClientData extends AJAX {
	private $_table = "slc_client";
	
	public function execute() {
		
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("msg", "No ID Supplied");
			return;
		}

		// check if it's already encoded
		// Will run every time the user types in a banner ID.
		if ( !startsWith($_REQUEST['banner_id'], "$") ) {			
			$tClient = $this->encryptClient();	        
			var_dump("Made it");
		}
			
		$encrypedBanner = $_REQUEST['banner_id'];

		// Returns whether there is a client object in the database.
		$client = ClientFactory::getClientByEncryBanner($encrypedBanner,  $tClient->getFirstName(), 
                                               $tClient->getLastName(), $tClient ->getFirstName() . ' ' . $tClient->getLastName());
		
		// Checks to see if there is a client.
        if ( $client == null ) 
        {
        	// create a whole new client and set each individual field.
        	$this->addResult("msg", "Client does not exist");
       	
			$client = ClientFactory::newClient($encrypedBanner, $tClient->getClassification(), 
											   $tClient->getMajor(), $tClient->getLivingLocation());
			ClientFactory::saveClient($client);

			$this->addResult('newFlag', true);
        } 
        else
        {
        	// Gathers the existing client.
        	$this->addResult('newFlag', false);
	    }  


        // Check if existing client has referral set
        $cRefferal = $client->getReferral();

        if ($cRefferal > 0) {						
        	// Add actual text of referral into client
        	$results = ClientFactory::getRefferalType($cRefferal);
        	//test($results);
        	$client->setReferralString($results[0]["name"]); 
        	$this->addResult('referralSet', true);
            
        } else
        	$this->addResult('referralSet', false);
        
        
        $this->addResult("client", $client);
        
	}

	public function encryptClient()
	{
		unset($_SESSION['cname']);
			
		// Grab extra information from ASU Database
        $tClient = ClientFactory::getClientByBannerId($_REQUEST['banner_id']); 


        if ( !$tClient ) {
        	$this->addResult("msg", "Client not in ASU Database");
        	return;
        }
        
        //store id in session because about to be encoded
        $_SESSION['actID'] = $tClient->getId();
       
        
        // Store the name in session, as after the banner is encrypted, there's no way to get it
        $_SESSION['cname'] = $tClient->getName();
        
        // encode banner
        $_REQUEST['banner_id'] = encode($_REQUEST['banner_id']);

        return $tClient;
	}

}
