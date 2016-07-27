<?php
namespace slc\ajax;

class PUTLandlord extends AJAX{

	public function execute(){
		$req = \Server::getCurrentRequest();
        $landlordData = json_decode($req->getRawData(), true);
        LandlordFactory::editLandlords($landlordData);
	}
}