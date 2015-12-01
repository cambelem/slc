<?php 
namespace slc\ajax;

class POSTReferralType extends AJAX {
	private $_table = "client";
	
	public function execute() {
		$this->addResult("avail_vars", $_REQUEST);
		
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("msg", "No Banner ID Supplied");
			return;
		}
		
		if ( !isset($_REQUEST['referral_type']) ) {
			$this->addResult("msg", "No Refferal ID Supplied");
			return;
		}

		// Update the client's referral type to the given 
		// drop down option.
		$db = \Database::newDB();
		$pdo = $db->getPDO();
		$query = "UPDATE slc_client SET referral=:rType WHERE id=:bId";	
		$sth = $pdo->prepare($query);
		$sth->execute(array('rType'=>$_REQUEST['referral_type'], 'bId'=>$_REQUEST['banner_id']));
	}
}
