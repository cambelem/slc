<?php

class ViewNewIssue extends SLCView {
	public function display(CommandContext $context) {
		$content = array();
		    	
		$form = new PHPWS_Form('filter_box');
	 	$form->addHidden('module', 'slc');
	 	$form->addHidden('view','NewIssue');
	 	$form->addText('issuename');
	 	$form->setLabel('issuename', 'Filter: ');

		$content = $form->getTemplate();
		
		$this->setupTree();
		
		$tree = new PHPWS_Template('slc');
		$tree->setFile('IssueTree.tpl');

		foreach( $this->theTree["Type Of Problem"] as $pType ) {
		
			if ( $pType["DBNAME"] == "problemlandlord") {
				foreach( $this->theTree["Conditions"] as $data ) {
					$tree->setCurrentBlock("problemlandlordcondition");
					$tree->setData($data);
					$tree->parseCurrentBlock();
				}
				
				foreach ($this->theTree["Landlord-Tenant"] as $data) {
					$tree->setCurrentBlock("problemlandlordnormal");
			    	$tree->setData($data);
			    	$tree->parseCurrentBlock();
				}
			} else if ( $pType["DBNAME"] == "problemregular") {
				foreach( $this->theTree["Problem"] as $data ) {
					$tree->setCurrentBlock("problemregular");
			    	$tree->setData($data);
			    	$tree->parseCurrentBlock();
				}
			} 
            // This displays the 'Criminal' sub-types. We don't use those anymore, so this is commented out to hide them.
            /*else if ( $pType["DBNAME"] == "problemcriminal") {
				foreach( $this->theTree["Law Enforcement Agency"] as $data ) {
					$tree->setCurrentBlock("problemcriminalagency");
			    	$tree->setData($data);
			    	$tree->parseCurrentBlock();
				}
				
				foreach( $this->theTree["Type of Criminal Problem"] as $data ) {
					$tree->setCurrentBlock("problemcriminaltype");
			    	$tree->setData($data);
			    	$tree->parseCurrentBlock();
				}
            }*/
			
	  
	    	$tree->setCurrentBlock($pType["DBNAME"]);
	    	$tree->setData($pType);
	    	$tree->parseCurrentBlock();
		}		
		
		$content['VISITID'] = $_REQUEST['visitid'];
		
		// extract client from visitid
		$query = "SELECT c_id FROM slc_visit WHERE id='".$_REQUEST['visitid']."'";	
		$db = new PHPWS_DB("slc_visit");
		$results = $db->select(NULL, $query);
		$content['CLIENTID'] = $results[0]['c_id'];
		$content['TITLE'] = "Create New Issue for ".unserialize($_SESSION['cname']);
		$content['SELECTED_ISSUES'] = "<span style='width:100%;' id='selectedIssue' title='-1'>[ none ]</span>";
		$content['PROBLEMS'] = $tree->get();
		$content['LANDLORD_PICKER'] = PHPWS_Template::process(array("landlords"=>$this->landlords), 'slc', 'LandlordPicker.tpl');
    	$content = PHPWS_Template::process($content, 'slc', 'NewIssue.tpl');
    	
    	return parent::useTemplate($content);
	}
	
	private function setupTree() {			
		$this->theTree["Type Of Problem"] = array();
		$this->theTree["Type Of Problem"][] = array("PROBLEM_ID" => 997, "PROBLEM" => "Landlord-Tenant", "DBNAME" => "problemlandlord");
		$this->theTree["Type Of Problem"][] = array("PROBLEM_ID" => 998, "PROBLEM" => "Criminal", "DBNAME" => "problemcriminal");
		$this->theTree["Type Of Problem"][] = array("PROBLEM_ID" => 999, "PROBLEM" => "Other", "DBNAME" => "problemregular");
		
		//$query = "SELECT * FROM slc_problem";
        // Don't select Criminal sub-types
		$query = "SELECT * FROM slc_problem WHERE id NOT IN (25,26,27,28,29,30,31,32,33,34,995)";
		
		$db = new PHPWS_DB("slc_problem");
		$results = $db->select(NULL, $query);
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        	$this->addResult("msg", "Database Exception");
	        return;
        }

        foreach ($results as $r) {
        	if (!isset($this->theTree[$r['type']]))
        		$this->theTree[$r['type']] = array();
        	$this->theTree[$r['type']][] = array("PROBLEM_ID" => $r['id'], "NAME"=>$r['description'], "TREE"=>$r['tree']);
        }
		
		$this->landlords = array();
		$query = "SELECT * FROM slc_landlord";
		
		$db = new PHPWS_DB("slc_landlord");
		$results = $db->select(NULL, $query);
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        	$this->addResult("msg", "Database Exception");
	        return;
        }

        foreach ($results as $r) {
        	$this->landlords[] = array("LANDLORD_ID" => $r['id'], "NAME"=>$r['name']);
        }

		javascriptMod('slc', 'newIssue');
	}
	
	private $theTree = array();
	private $landlords = array();
	
}

?>
