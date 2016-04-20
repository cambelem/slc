<?php
namespace slc\views;

class ViewNewIssue extends View {

	private $theTree = array();
	private $landlords = array();

	public function display(\slc\CommandContext $context) {
		$content = array();

		$form = new \PHPWS_Form('filter_box');
	 	$form->addHidden('module', 'slc');
	 	$form->addHidden('view','NewIssue');
	 	$form->addText('issuename');
	 	$form->setLabel('issuename', 'Filter: ');

		$content = $form->getTemplate();

		$this->setupTree();

		$content['VISITID'] = $_REQUEST['visitid'];

		// extract client from visitid
		$query = "SELECT client_id FROM slc_visit WHERE id='".$_REQUEST['visitid']."'";
		$db = new \PHPWS_DB("slc_visit");
		$results = $db->select(NULL, $query);

		$content['CLIENTID'] = $results[0]['client_id'];
		$content['TITLE'] = "Create New Issue for ".$_REQUEST['cname'];

		$content['PROBLEMS'] = $this->theTree;
		$content['LANDLORD_PICKER'] = \PHPWS_Template::process(array("landlords"=>$this->landlords), 'slc', 'LandlordPicker.tpl');


    	$content = \PHPWS_Template::process($content, 'slc', 'NewIssue.tpl');
    	return parent::useTemplate($content);
	}

	private function setupTree() {
		$this->theTree["Type Of Problem"] = array();
		$this->theTree["Type Of Problem"][] = array("PROBLEM_ID" => 997, "PROBLEM" => "Landlord-Tenant", "DBNAME" => "problemlandlord");
		$this->theTree["Type Of Problem"][] = array("PROBLEM_ID" => 998, "PROBLEM" => "Criminal", "DBNAME" => "problemcriminal");
		$this->theTree["Type Of Problem"][] = array("PROBLEM_ID" => 999, "PROBLEM" => "Other", "DBNAME" => "problemregular");

        // Don't select Criminal sub-types
		$query = "SELECT * FROM slc_problem WHERE id NOT IN (25,26,27,28,29,30,31,32,33,34,995)";

		$db = new \PHPWS_DB("slc_problem");
		$results = $db->select(NULL, $query);

        if(\PHPWS_Error::logIfError($results)){
            throw new \slc\exceptions\DatabaseException();
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

		$db = new \PHPWS_DB("slc_landlord");
		$results = $db->select(NULL, $query);

        if(\PHPWS_Error::logIfError($results)){
            throw new \slc\exceptions\DatabaseException();
        	$this->addResult("msg", "Database Exception");
	        return;
        }

        foreach ($results as $r) {
        	$this->landlords[] = array("LANDLORD_ID" => $r['id'], "NAME"=>$r['name']);
        }
	}



}
