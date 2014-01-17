<?php 

class POSTNewClient extends AJAX {
	private $_table = "slc_client";
	
	public function execute() {
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("msg", "No ID Supplied");
//			throw new IDNotSuppliedException();
			return;
		}
		
		// Encode the banner id right off -- use this from now on
		$banner = $_REQUEST['banner_id'];

		// double check that client does not exist in database
		$db = new PHPWS_DB($this->_table);
		$db->addWhere("id", $banner);
        $results = $db->count();
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }

        if ( $results > 0 ) {
        	$this->addResult("msg", "Client already exists");
//			throw new ClientAlreadyExistsException();
        	return;
        }
        
        //$this->data = array();
        //$this->data['classification'] = $_REQUEST['classification'];
        //$this->data['living_location'] = $_REQUEST['living_location'];
        //$this->data['major'] = $_REQUEST['major'];
        
        $client = new Client($banner);
        
        $client->classification = (isset($this->data['classification']) ) ? $this->data['classification'] : "Unknown";
        $client->living_location = (isset($this->data['living_location']) ) ? $this->data['living_location'] : "Unknown";
        $client->major = (isset($this->data['major']) ) ? $this->data['major'] : "Unknown";
        
        $client->first_visit = timestamp();
        
        $db->reset();
        $db->setTable($this->_table);
        $results = $db->saveObject($client);
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }

        $this->addResult("msg", $results);
        $this->addResult("client", $client);
	}

}

?>
