<?php
namespace slc\ajax;

class GETLawByAgency extends AJAX {
		public function execute() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['startDate']);
        $end_date = strtotime($_REQUEST['endDate']) + 86400;   // +1 day to make date range inclusive

		$db = new \PHPWS_DB('slc_problem');
        $db->addColumn('slc_problem.description', NULL, 'agency');
        $db->addJoin('inner', 'slc_problem', 'slc_issue', 'id', 'problem_id');
        $db->addJoin('inner', 'slc_issue', 'slc_visit_issue_index', 'id', 'i_id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');
        $db->addWhere('slc_problem.type', 'Law Enforcement Agency', 'LIKE');
        $db->addWhere('slc_visit.initial_date', $start_date, '>=', 'AND');
        $db->addWhere('slc_visit.initial_date', $end_date, '<', 'AND');
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
            $agencies = array();
            
            foreach ($results as $r) {
            	if ( !array_key_exists($r['agency'], $agencies) )
            		$agencies[$r['agency']] = 1;
            	else
            		$agencies[$r['agency']]++;
            }

            $content["TITLE"] = "Agency";
            $contant["STATS"] = "Statistics";

            $row = array();
            foreach($agencies as $key => $condition)
            {
                $row["AGENCIES"] = $key;
                $row["COUNT"] = $condition;

                $content['agency_repeat'][] = $row;
            }
        } 
        $tpl = \PHPWS_Template::process($content, 'slc','LawByAgency.tpl');
        $this->addResult("__html", $tpl); 
	}
}
?>