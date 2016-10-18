<?php
namespace slc\ajax;

class PostTransferInternat extends AJAX {

	public function execute(){
		$req = \Server::getCurrentRequest();
        $tiData = json_decode($req->getRawData(), true);
        
        $client = ClientFactory::getClientByEncryBanner($tiData['id'], $tiData['fname'], $tiData['lname'], $tiData['fullName']);


        if($tiData['sType'] === "transfer"){
        	$client->setTransfer($tiData['checked']);
        } else {
        	$client->setTransfer($tiData['checked']);
        }
        
        ClientFactory::updateClient($client);

    //    $this->addResult("msg", "Successfully changed $tiData['sType']");
    //    $this->addResult("errorType", "success");
	}
}
