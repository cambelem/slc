<?php
namespace slc\ajax;

class GETLandlordData extends AJAX {

    public function execute() {

    	$landlords = LandlordFactory::getLandlords();
		$this->addResult("landlords", $landlords);

	}
}

 
