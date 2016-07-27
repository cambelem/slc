<?php
namespace slc\ajax;

class POSTLandlord extends AJAX{

	public function execute(){

		$req = \Server::getCurrentRequest();
        $landlordName = json_decode($req->getRawData(), true);
        LandlordFactory::saveLandlords($landlordName);

	}
}