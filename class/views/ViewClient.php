<?php 

class ViewClient extends SLCView {
	public function display(CommandContext $context) {
		$HTMLcontent = "";
		$referral = "";
		
		$ajax = AjaxFactory::get("client");
		$ajax->loadCall("GETClientData");
		$ajax->execute();
		
		$result = $ajax->result(); 
		
		if (isset($result['msg']) && $result['msg'] == "Client not in ASU Database" ) {
			$HTMLcontent .= "<span style='font-weight:bold;'>Client not in ASU Database<br /></span><span style='font-size:-1;margin-left:20px;'>This could be due to the client being a non-student or the database not being updated.</span>";
			return parent::useTemplate($HTMLcontent); // Insert into the accessible div
		}
			
		$client = $result['client'];
		
		// Test for new client creation
		$newClient = $result['newFlag'] || !$result['referralSet'];
		//test($result);
		
		if ( $newClient ) {
			$ajax = AjaxFactory::get("referral");
			$ajax->loadCall("GETReferralBox");
			$ajax->execute();
			
			$result = $ajax->result(); 
			$r = $result['referral_picker'];
		
			$referral = "<h3 id='referralDiv' style='width:100%; background:#FFEC8B;margin-top:-15px; padding-top:3px; padding-bottom:3px;margin-bottom:7px;'>&nbsp;Referred By: <span style='font-weight:bold;'>".$r."</span></h3>";
		} else {
			$referral = "<h3 id='referralDiv' style='width:100%; background:#FFFFFF;margin-top:-15px; padding-top:3px; padding-bottom:3px;margin-bottom:7px;'>&nbsp;Referred By: ".$client->referralString."</h3>";
		}
		
		
		$banner = $client->id;

		$content = array();
		$tpl = new PHPWS_Template('slc');
		$tpl->setFile('ClientVisits.tpl');
		$content['CLIENT_ID'] = $client->id;
		$content['CLIENT_NAME'] = unserialize($_SESSION['cname']);//$client->name;
		$content['CLIENT_INFO'] = $client->classification." - ".$client->major." Major";
		$content['FIRST_VISIT'] = prettyTime($client->first_visit);
		
		
		
		// Get visits
		$ajax = AjaxFactory::get("visits");
		$ajax->loadCall("GETClientVisits");
		$ajax->execute();
		$visits = $ajax->result(); 
		$visits = $visits['visits'];
		
        if(!empty($visits)){
            foreach ($visits as $visit) {
                //print_r($visit);
                $visitTpl['VISITID'] = $visit->id;
                $visitTpl['VISIT_DATE'] = prettyTime($visit->initial_date);
                $visitTpl['NEW_ISSUE'] = "<a href='index.php?module=slc&view=NewIssue&visitid=".$visit->id."'>NEW ISSUE</a>";
        
                
                // foreach issue per visit, keep array with array pointer
                foreach ($visit->issues as $issue) {
                    //print_r($issue);
                    $issueTpl['ISSUEID'] = $issue->id;
                    $issueTpl['ISSUE'] = $issue->name;
                    $issueTpl['VISITCOUNT'] = $issue->counter;
                    $issueTpl['FOLLOWUP'] = "Follow Up";
                    $issueTpl['LASTACCESS'] = prettyTime($issue->last_access)." (".prettyAccess($issue->last_access).")";
                    $issueTpl['VISSITISSUEID'] = $issue->visit_issue_id;
                    $issueTpl['LANDLORD'] = $issue->landlord_name;
                    
                    $tpl->setCurrentBlock("issues");
                    $tpl->setData($issueTpl);
                    $tpl->parseCurrentBlock();
                }
                
                $tpl->setCurrentBlock("visits");
                $tpl->setData($visitTpl);
                $tpl->parseCurrentBlock();
            
            }
        }
			
	 	$content['CLIENT_VISITS'] = $tpl->get();
	 	$content['CLIENT_BANNER'] = $banner;
	 	$content['REFERRAL'] = $referral; 
	 		
		javascriptMod('slc', 'viewClient');
			 
	 	$HTMLcontent .= PHPWS_Template::process($content, 'slc', 'Client.tpl');
	 	
	 	return parent::useTemplate($HTMLcontent); // Insert into the accessible div
	}
}
?>
