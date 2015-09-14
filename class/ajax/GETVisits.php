<?php
namespace slc\ajax;

class GETVisits extends AJAX {
	private $_table = "slc_visits";
	
	public function execute() {
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("msg", "No ID Supplied");
//			throw new IDNotSuppliedException();
			return;
		}
		
		// Encode the banner id right off -- use this from now on
		$banner = encode($_REQUEST['banner_id']);

		// check that client exists in database, if not create
		$db = new \PHPWS_DB($this->_table);
		$db->addWhere("id", $banner);
        $results = $db->count();
        
        if(\PHPWS_Error::logIfError($results)){
            throw new \slc\exceptions\DatabaseException();
        }

        if ( $results == 0 ) {
        	$af = \AJAXFactory::get("POSTNewClient");
        	$af->loadCall("POSTNewClient"); // using the original since it will be encoded
		    $af->execute();
		    if ( !$af->result() ) {
		    	$this->addResult("msg", "Unable to add client");
	        	return;
		    }
        }
        
        $this->result = array();

        $db = new \PHPWS_DB($this->_table);
		$client = new \slc\Client($banner);
		$results = $db->loadObject($client);
        
        if(\PHPWS_Error::logIfError($results)){
            throw new \slc\exceptions\DatabaseException();
        }

        $this->addResult("client", $client);
        $this->addResult("msg", $results);
        
	}
}
?>
