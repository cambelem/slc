<?php
namespace slc\ajax;

class GETTypeOfReferral extends AJAX {
    public function execute() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['startDate']);
        $end_date = strtotime($_REQUEST['endDate']) + 86400;   // +1 day to make date range inclusive

		$db = new \PHPWS_DB('slc_referral_type');
        $db->addColumn('name');
        $db->addWhere('id', 'slc_client.referral');
        $db->addWhere('slc_client.first_visit', $start_date, '>=', 'AND');
        $db->addWhere('slc_client.first_visit', $end_date, '<', 'AND');
        $results = $db->select();
        
        if(\PHPWS_Error::logIfError($results)){
            throw new \slc\exceptions\DatabaseException();
        }
        
        $content = array();

        // return an "empty" message if $results is empty
        if (count($results) == 0) {
            $content["NO_RECORDS"] = "There are no records for that time period.";
        }
        else
        {
            $referrals = array();
            
            foreach ($results as $r) {
            	if ( $r['name'] != '')
    	        	if ( !array_key_exists($r['name'], $referrals) )
    	        		$referrals[$r['name']] = 1;
    	        	else
    	        		$referrals[$r['name']]++;
            }

            $row = array();
            foreach($referrals as $key => $condition)
            {
                $row["R_TYPE"] = $key;
                $row["COUNT"] = $condition;

                $content['referral_type'][] = $row;
            }
        }
        $tpl = \PHPWS_Template::process($content, 'slc','TypeOfReferral.tpl');
        $this->addResult("__html", $tpl); 
	}
}
?>