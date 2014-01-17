<?php
class GETReferralBox extends AJAX {
	private $_table = "referral";
	
	public function execute() {
		
		$query = 'SELECT * '.
        			' FROM slc_referral_type';
        
        $db = new PHPWS_DB();
        //$db->setTestMode();
        $results = $db->select(null, $query);
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }
        
		$rTypes = array();
        foreach( $results as $r ) { // types
        	$rTypes[] = array("REFERRAL_ID" => $r['id'], "NAME"=>$r['name']);
        }
		
        $referralPicker = PHPWS_Template::process(array("referrals"=>$rTypes), 'slc', 'ReferralPicker.tpl');
        
	    $this->addResult("referral_picker", $referralPicker);    
	}
}
