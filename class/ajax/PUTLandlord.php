<?php
namespace slc\ajax;

class PUTLandlord extends AJAX{

	public function execute(){
		$req = \Server::getCurrentRequest();
        $landlordData = json_decode($req->getRawData(), true);

        $landlords = LandlordFactory::getLandlords();
        $name = $landlordData['name'];

        if(trim($name) == '')
        {
        	$this->addResult("msg", "Landlord name is invalid");
			$this->addResult("errorType", "warning");
			return;
        }

		foreach($landlords as $l){
			if($l['name'] == $name){
				$this->addResult("msg", "Unable to edit landlord: Duplicate Name [$name].");
				$this->addResult("errorType", "warning");
				return;
			}
		}

        LandlordFactory::editLandlords($landlordData);

        $this->addResult("msg", "Successfully edited $name");
        $this->addResult("errorType", "success");
	}
}
