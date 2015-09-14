<?php 
namespace slc\ajax;

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
		$db = new \PHPWS_DB($this->_table);
		$db->addWhere("id", $banner);
        $results = $db->count();
        
        if(\PHPWS_Error::logIfError($results)){
            throw new \slc\exceptions\DatabaseException();
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
        
        $client = new \slc\Client($banner);
        // Place these in constructor
        if (isset($this->data['classification']))
        {
            $client->setClassification($this->data['classification']);
        }
        else
        {
            $client->setClassification("Uknown");
        }
        
        if (isset($this->data['living_location']))
        {
            $client->setLivingLocation($this->data['living_location']);
        }
        else
        {
            $client->setLivingLocation("Uknown");
        }

        if (isset($this->data['major']))
        {
            $client->setMajor($this->data['major']);
        }
        else
        {
            $client->setMajor("Uknown");
        }
        
        $time = timestamp();
        $client->setFirstVisit($time);
        
        $db->reset();
        $db->setTable($this->_table);
        $results = $db->saveObject($client);
        
        if(\PHPWS_Error::logIfError($results)){
            throw new \slc\exceptions\DatabaseException();
        }

        $this->addResult("msg", $results);
        $this->addResult("client", $client);
	}

}

?>
