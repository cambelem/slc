<?php
namespace slc\ajax;

class GETIntakeProblemType extends AJAX {

    public function execute() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['startDate']);
        $end_date = strtotime($_REQUEST['endDate']) + 86400;   // +1 day to make date range inclusive

        // Get the list of all Landlord-Tenant type problems
        $db = new \PHPWS_DB('slc_problem');
        $db->addColumn('description');
        $db->addWhere('type', 'Landlord-Tenant', 'LIKE');
        $landlord = $db->select();

        if(\PHPWS_Error::logIfError($landlord)){
            throw new \slc\exceptions\DatabaseException();
        }

        // Get the list of all Conditions type problems
        $db = new \PHPWS_DB('slc_problem');
        $db->addColumn('description');
        $db->addWhere('type', 'Conditions', 'LIKE');
        $conditions = $db->select();

        if(\PHPWS_Error::logIfError($conditions)){
            throw new \slc\exceptions\DatabaseException();
        }

		$db = new \PHPWS_DB();
        $db->addTable('slc_issue');
        $db->addTable('slc_problem');
        $db->addTable('slc_visit');
        $db->addTable('slc_visit_issue_index');
        $db->addColumn('slc_issue.id', NULL, 'count', TRUE, TRUE);
        $db->addColumn('slc_problem.description');
        $db->addJoin('inner', 'slc_issue', 'slc_problem', 'problem_id', 'id');
        $db->addJoin('inner', 'slc_issue', 'slc_visit_issue_index', 'id', 'i_id');
        $db->addJoin('inner', 'slc_visit', 'slc_visit_issue_index', 'id', 'v_id');
        $db->addWhere('slc_visit.initial_date', $start_date, '>=');
        $db->addWhere('slc_visit.initial_date', $end_date, '<', 'AND');
        $db->addGroupBy('slc_issue.problem_id');
        $results = $db->select();
        
        if(\PHPWS_Error::logIfError($results)){
            throw new \slc\exceptions\DatabaseException();
        }
     
        /*
         * Remove all Landlord-Tenant and Conditions type problems from the main
         * results array, seperate them into individual arrays, and tally the 
         * number of problems recorded for each. Also grab the generic
         * Landlord-Tenant and Conditions types, tally their occurences, and put
         * them at the head of the proper set of results.
         */
        $landlordCount = 0;
        $conditionsCount = 0;
        $landlordResults = array(array('description'=>'Landlord-Tenant', 'count'=>$landlordCount));
        $conditionsResults = array(array('description'=>'Conditions', 'count'=>$conditionsCount));

        // Find generic problem types first and list them as the first sub-type
        foreach($results as $key=>$r) {
            if ($r['description'] == 'Landlord-Tenant') {   // generic type
                $r['description'] = 'Generic Landlord-Tenant';
                $landlordResults[] = $r;
                $landlordCount += $r['count'];
                unset($results[$key]);
            } elseif ($r['description'] == 'Conditions') {  // generic type
                $r['description'] = 'Generic Condition';
                $conditionsResults[] = $r;
                $conditionsCount += $r['count'];
                unset($results[$key]);
            }
        }

        foreach($results as $key=>$r) {
            if(in_array(array('description'=>$r['description']), $landlord, TRUE)) {
                $landlordResults[] = $results[$key];
                $landlordCount += $r['count'];
                unset($results[$key]);
            } elseif (in_array(array('description'=>$r['description']), $conditions, TRUE)) {
                $conditionsResults[] = $results[$key];
                $conditionsCount += $r['count'];
                unset($results[$key]);
            }
        }

        // re-index the main results array now that we have removed all landlord and conditions results
        $results = array_values($results);

        // If there are Conditions type problems, nest them in the Landlord-Tenant
        // results and include their total occurences in the Landlord-Tenant count.
        if ($conditionsCount > 0) {
            $conditionsResults[0]['count'] = $conditionsCount;

            // Use the generic Conditions problem as the main line for all Conditions problems
            $landlordResults[] = $conditionsResults[0];
            $landlordCount += $conditionsCount;
            $landlordResults[0]['count'] = $landlordCount;
            unset($conditionsResults[0]);

            // Indent all sub-types of Conditions problems
            foreach ($conditionsResults as $r3) {
                $string = "-> " . $r3['description'];
                $landlordResults[] = array('description'=>$string, 'count'=>$r3['count']);
            }
        }

        // If there are Landlord-Tenant type problems, nest them in the main results array
        if ($landlordCount > 0) {
            $landlordResults[0]['count'] = $landlordCount;

            // Use the generic Landlord-Tenant problem as the main line for all Landlord-Tenant problems
            $results[] = $landlordResults[0];
            unset($landlordResults[0]);

            // Indent all sub-types of Landlord-Tenant problems
            foreach ($landlordResults as $r2) {
                if (substr($r2['description'], 0, 2) === '->') {
                    // Don't add a second '-> ' to Conditions type problems
                    $string = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $r2['description'];
                } else {
                    $string = "-> " . $r2['description'];
                }
                $results[] = array('description'=>$string, 'count'=>$r2['count']);
            }
        }

        $content = array();

        if (count($results) != 0) { // Return the empty string if there are no results
            $total = 0;

            foreach( $results as $r ) {
        	    $count = $r['count'];
                
                // Don't include counts for sub-categories in the total count, we have already counted those.
                if (strpos($r['description'], '->') === FALSE) {
                    $total += $count;
                }
        	    
                $type = $r['description'];
        	
	            $content['intake_problem_repeat'][] = array('PROBLEM' => $type, 'COUNT' => $count);
            }

            // Add a final row with the total # of problems
            $content['TOTAL'] = $total;
        }

        $tpl = \PHPWS_Template::process($content, 'slc','IntakeProblemType.tpl');

		$this->addResult("__html", $tpl);  
	}
}
?>