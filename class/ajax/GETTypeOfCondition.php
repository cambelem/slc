<?php
namespace slc\ajax;

class GETTypeOfCondition extends AJAX {
    public function execute() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['startDate']);
        $end_date = strtotime($_REQUEST['endDate']) + 86400;   // +1 day to make date range inclusive

		$db = new \PHPWS_DB('slc_problem');
        $db->addColumn('slc_problem.description', NULL, 'descript');
        $db->addJoin('inner', 'slc_problem', 'slc_issue', 'id', 'problem_id');
        $db->addJoin('inner', 'slc_issue', 'slc_visit_issue_index', 'id', 'i_id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');
        $db->addWhere('slc_problem.type', 'Conditions', 'LIKE');
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
        
        $conditions = array();
        
        foreach ($results as $r) {
        	if ( !array_key_exists($r['descript'], $conditions) )
        		$conditions[$r['descript']] = 1;
        	else
        		$conditions[$r['descript']]++;
        }
        
                
        $row = array();
        foreach($conditions as $key => $condition)
        {
        	$row["CATEGORY"] = $key;
            $row["STATS"] = $condition;

            $content['type_condition'][] = $row;
        }

        
        $tpl = \PHPWS_Template::process($content, 'slc','TypeOfCondition.tpl');
        $this->addResult("__html", $tpl); 
	}
}
?>