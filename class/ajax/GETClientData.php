<?php
class GETClientData extends AJAX {
	private $_table = "slc_client";
	
	public function execute() {
		$tClient = new Client();
		
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("msg", "No ID Supplied");
//			throw new IDNotSuppliedException();
			return;
		}

		$client = new Client();
		
		// check if it's already encoded
		if ( !startsWith($_REQUEST['banner_id'], "$") ) {			
			 
	        unset($_SESSION['cname']);
			
			// Grab extra information from ASU Database
	        $db = new PHPWS_DB('slc_student_data');
			$db->addWhere("id", $_REQUEST['banner_id']);
	        $results = $db->loadObject($tClient);
	        
	        if(PHPWS_Error::logIfError($results)){
	            throw new DatabaseException();
	        	$this->addResult("msg", "Database Exception");
		        return;
	        }
	
	        if ( !$results ) {
	        	$this->addResult("msg", "Client not in ASU Database");
	        	return;
	        }
	       
	        
	        // Store the name in session, as after the banner is encrypted, there's no way to get it
	        $_SESSION['cname'] = serialize($tClient->fname . ' ' . $tClient->lname);
	        
	        // encode banner
	        $_REQUEST['banner_id'] = encode($_REQUEST['banner_id']);
		}
			
		$banner = $_REQUEST['banner_id'];
		
		//$client = new Client($banner);
        $client->id = $banner;
		
       
        
		// check that client exists in database
		$db = new PHPWS_DB($this->_table);
        $results = $db->loadObject($client);
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        	$this->addResult("msg", "Database Exception");
	        return;
        }

        if ( !$results ) {
        	$this->addResult("msg", "Client does not exist");
       	
        	// create new client
        	$ajax = AjaxFactory::get("newClient");
			$ajax->loadCall("POSTNewClient");
			$ajax->setData(array("classification"=>$tClient->classification, "major"=>$tClient->major, "living_location"=>$tClient->living_location));
			$ajax->execute();
			$client = $ajax->result();
		
			$client = $client['client'];
			$this->addResult('newFlag', true);
        } else
        	$this->addResult('newFlag', false);
        $client->fname = $tClient->fname;
        $client->lname = $tClient->lname;
        $client->name = $client->fname . ' ' . $client->lname;
       
        // Check if existing client has referral set
        if ( isset($client->referral) ) {
        	// Add actual text of referral into client
        	$query = 'SELECT * '.
        			' FROM slc_referral_type '.
        			' WHERE id=' . $client->referral;
	        
	        $db = new PHPWS_DB();
	        //$db->setTestMode();
	        $results = $db->select(null, $query);
	        
	        if(PHPWS_Error::logIfError($results)){
	            throw new DatabaseException();
	        }
        	//test($results);
        	$client->referralString = $results[0]["name"]; 
        	$this->addResult('referralSet', true);
        } else
        	$this->addResult('referralSet', false);
        
        
        $this->addResult("client", $client);
        
	}
}
