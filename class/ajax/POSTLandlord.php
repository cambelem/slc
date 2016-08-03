<?php
namespace slc\ajax;

class POSTLandlord extends AJAX{

	public function execute(){

		$req = \Server::getCurrentRequest();
        $landlordName = json_decode($req->getRawData(), true);

        $landlords = LandlordFactory::getLandlords();

        if(trim($landlordName) == '')
        {
        	$this->addResult("msg", "Landlord name is invalid");
			$this->addResult("errorType", "warning");
			return;
        }

		foreach($landlords as $l){
			if($l['name'] == $landlordName){
				$this->addResult("msg", "Unable to add landlord: Duplicate Name [$landlordName].");
				$this->addResult("errorType", "warning");
				return;
			}
		}

        LandlordFactory::saveLandlords($landlordName);
        $this->addResult("msg", "Successfully added $landlordName");
        $this->addResult("errorType", "success");

	}
}