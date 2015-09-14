<?php
namespace slc\ajax;

class GETReferralBox extends AJAX {
	private $_table = "referral";
	
	public function execute() {
		
        $db = \Database::newDB();
        $pdo = $db->getPDO();

		$query = 'SELECT * 
        		  FROM slc_referral_type';
        
        $sth = $pdo->prepare($query);
        $sth->execute();
        $results = $sth->fetchAll(\PDO::FETCH_ASSOC);
        
		$rTypes = array();
        foreach( $results as $r ) { // types
        	$rTypes[] = array("REFERRAL_ID" => $r['id'], "NAME"=>$r['name']);
        }
		
        $referralPicker = \PHPWS_Template::process(array("referrals"=>$rTypes), 'slc', 'ReferralPicker.tpl');
        
	    $this->addResult("referral_picker", $referralPicker);    
	}
}
