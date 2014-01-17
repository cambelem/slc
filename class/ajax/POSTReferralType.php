<?php 

class POSTReferralType extends AJAX {
	private $_table = "client";
	
	public function execute() {
		$this->addResult("avail_vars", $_REQUEST);
		
		if ( !isset($_REQUEST['banner_id']) ) {
			$this->addResult("msg", "No Banner ID Supplied");
//			throw new IDNotSuppliedException();
			return;
		}
		
		if ( !isset($_REQUEST['referral_type']) ) {
			$this->addResult("msg", "No Refferal ID Supplied");
//			throw new ReferralIDNotSuppliedException();
			return;
		}

		$qry = "UPDATE slc_client SET referral=".$_REQUEST['referral_type']." WHERE id='".$_REQUEST['banner_id']."'";
		
		$results = PHPWS_DB::query($qry);
        
		if(PHPWS_Error::logIfError($results)){
            $this->addResult("error", "Database Exception");
			//throw new DatabaseException();
			return;
        }
        
        $this->addResult("msg", $results);
	}
}
