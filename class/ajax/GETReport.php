<?php
class GETReport extends AJAX {
	
	public function execute() {
		if ( !isset($_REQUEST['report_type']) ) {
			$this->addResult("msg", "No Report Type Supplied");
			// throw new ReportTypeNotSuppliedException();
			return;
		}
		
		$reportHTML = "";
		
		$func = 'REPORT'.$_REQUEST['report_type'];

		if ( method_exists("GETReport", $func) )
			$reportHTML .= call_user_func(array("GETReport", $func));
		else 
			$reportHTML .= "The selected report type (".$_REQUEST['report_type'].") is not yet implemented in this version";
			
		$this->addResult('report_html', $reportHTML);
	}

	function REPORTstudentsseen() {
		$query = "SELECT "; 
		$query .= "(SELECT COUNT(DISTINCT id) FROM slc_visit) AS visits, ";
		$query .= "(SELECT COUNT(DISTINCT id) FROM slc_client) AS clients, ";
		$query .= "(SELECT COUNT(DISTINCT id) FROM slc_issue) AS issues, ";
		$query .= "(SELECT SUM(counter) FROM slc_visit_issue_index) AS followups ";
		
		$db = new PHPWS_DB();
        //$db->setTestMode();
        $results = $db->select(null, $query);
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }
        
        $results = $results[0]; // since it's a count, should always return a number
        $visits = $results['visits'];
        $clients = $results['clients'];
        $issues = $results['issues'];
        $followups = $results['followups'] - $issues; // Every issue has at least one visit, so remove initial
        
        $bgcolor = array(0=>"#FFFFFF",1=>"#FFEC8B");
        $r = 0;
        
        $html = "";
        $html .= "<table class='reportTable'>";
        $html .= "<tr><th style='width:150px;'>Situation</th><th>Record</th></tr>";
        $html .= "<tr style='background-color:$bgcolor[$r];'><td>Total Visits</td><td>".$visits."</td></tr>"; $r = !$r;
        $html .= "<tr style='background-color:$bgcolor[$r];'><td>Total Clients</td><td>".$clients."</td></tr>"; $r = !$r;
        $html .= "<tr style='background-color:$bgcolor[$r];'><td>Total Issues</td><td>".$issues."</td></tr>"; $r = !$r;
        $html .= "<tr style='background-color:$bgcolor[$r];'><td>Visits per Client</td><td>".(round($visits/$clients,2))."</td></tr>"; $r = !$r;
        $html .= "<tr style='background-color:$bgcolor[$r];'><td>Issues per Visit</td><td>".(round($issues/$visits,2))."</td></tr>"; $r = !$r;
        $html .= "<tr style='background-color:$bgcolor[$r];'><td>Followups per Issue</td><td>".(round($followups/$issues, 2))."</td></tr>"; $r = !$r;
        $html .= "</table>";
        
        return $html;
	}
	
    /**
     * This method builds the Intake by Problem Type report.
     */
	private function REPORTintakebyproblemtype() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['start_date']);
        $end_date = strtotime($_REQUEST['end_date']) + 86400;   // +1 day to make date range inclusive

        // Get the list of all Landlord-Tenant type problems
        $db = new PHPWS_DB('slc_problem');
        $db->addColumn('description');
        $db->addWhere('type', 'Landlord-Tenant', 'LIKE');
        $landlord = $db->select();

        if(PHPWS_Error::logIfError($landlord)){
            throw new DatabaseException();
        }

        // Get the list of all Conditions type problems
        $db = new PHPWS_DB('slc_problem');
        $db->addColumn('description');
        $db->addWhere('type', 'Conditions', 'LIKE');
        $conditions = $db->select();

        if(PHPWS_Error::logIfError($conditions)){
            throw new DatabaseException();
        }

		$db = new PHPWS_DB();
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
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
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

        $html = "";

        if (count($results) != 0) { // Return the empty string if there are no results
            $html .= "<table class='reportTable'>";
            $html .= "<tr><th style='width:200px;'>Situation</th><th>Record</th></tr>";
            $bgcolor = array(0=>"#FFFFFF",1=>"#FFEC8B");
            $rc = 0;
            $total = 0;

            foreach( $results as $r ) {
        	    $count = $r['count'];
                
                // Don't include counts for sub-categories in the total count, we have already counted those.
                if (strpos($r['description'], '->') === FALSE) {
                    $total += $count;
                }
        	    
                $type = $r['description'];
        	
        	    $html .= "<tr style='background-color:".$bgcolor[$rc].";'><td>".$type."</td><td>".$count."</td></tr>"; 
        	    $rc = !$rc;
            }

            // Add a final row with the total # of problems
            $html .= "<tr style='background-color:".$bgcolor[$rc].";'><td><strong>Total Number Of Problems</strong></td><td><strong>".$total."</strong></td></tr>";
            $html .= "</table>";
        }

		return $html;
	}

	private function REPORTlandlordtenant() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['start_date']);
        $end_date = strtotime($_REQUEST['end_date']) + 86400;   // +1 day to make date range inclusive

        $landlords = "SELECT * FROM slc_landlord";
        $db = new PHPWS_DB();
        $landlords = $db->select(null, $landlords);
        $landlordnames = array();
        foreach( $landlords as $landlord )
        	$landlordnames[] = $landlord['name'];
            
        $issues = "SELECT * FROM slc_problem WHERE tree LIKE '%Landlord-Tenant%' OR description LIKE 'Conditions' OR description LIKE 'Landlord-Tenant' "; // Covers generic landlord-tenant, too
        $db = new PHPWS_DB();
        $issues = $db->select(null, $issues);
        $issuenames = array();
        foreach( $issues as $issue )
        	$issuenames[] = $issue['description'];
    	
        
		// Get the issues listed ( all others 0 )
		$db = new PHPWS_DB();
        $db->addTable('slc_issue');
        $db->addTable('slc_problem');
        $db->addTable('slc_landlord');
        $db->addTable('slc_visit');
        $db->addTable('slc_visit_issue_index');
        $db->addColumn('slc_problem.description');
        $db->addColumn('slc_landlord.name');
        
        // 1st Join set
        // needs to be a left join to allow for landlord "not specified" which is recorded as NULL in DB
        $db->addJoin('left', 'slc_issue', 'slc_landlord', 'landlord_id', 'id');
        
        // 2nd Join set
        $db->addJoin('inner', 'slc_problem', 'slc_issue', 'id', 'problem_id');
        $db->addJoin('inner', 'slc_issue', 'slc_visit_issue_index', 'id', 'i_id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');
        $db->addWhere('slc_visit.initial_date', $start_date, '>=');
        $db->addWhere('slc_visit.initial_date', $end_date, '<', 'AND');
        $results = $db->select();
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }
        
        $theMatrix = $landlordnames;
        foreach($landlordnames as $lname) { 
        	$theMatrix[$lname] = $issuenames;
        	foreach($issuenames as $iname)
        		$theMatrix[$lname][$iname] = 0; // Populate with 0s 
        }
              
        
        foreach( $results as $r ) {
            // If landlord name is NULL, set as "Other / Unspecified"
        	$name = isset($r['name']) ? $r['name'] : "Other / Unspecified";
        	$description = $r['description'];
        	
        	if ( !array_key_exists($description,$theMatrix[$name]) )
					$theMatrix[$name][$description] = 0;
				
        	$theMatrix[$name][$description]++; // increment that value
        }

        // Row Colors
		$stripes = array(0=>"255,255,255",1=>"255,236,139");
        $zebra = 0;

        $html = "<table class='reportTable'>";

        // Header Row
        $html .= "<tr><th id=r0c0></th>";
        $col = 1;
        foreach ($issuenames as $issue) {
            $html .= "<th id=r0c" . $col++ . ">" . $issue . "</th>";
        }
        $html .= "<th>Landlord Total</th></tr>";

        // Table Body
        $colTotals = array();
        $row = 1;
        foreach ($landlordnames as $landlord) {
            $col = 0;
            $html .= "<tr style='background-color:rgba(" . $stripes[$zebra] . ", 0.96)'><td id=r" . $row . "c" . $col++ . " class='landlord'>" . $landlord . "</td>";
            
            $rowTotal = 0;
            foreach ($issuenames as $issue) {
				$value = $theMatrix[$landlord][$issue];
				$style = ($value == 0) ? "color:#ABABAB;" : "color:#000000; font-weight:bold;"; 
								
				$html .= "<td id=r" . $row . "c" . $col . " class='dataCell' style='" . $style . "'>" . $value . "</td>";
				$rowTotal += $value;
				
                // Increment the column total
                if (array_key_exists($issue, $colTotals)) {
					$colTotals[$issue] += $value;
                } else {
                	$colTotals[$issue] = $value;
                }
            }

            $html .= "<td id=r" . $row . "c" . $col . " class='Total'>" . $rowTotal . "</td>";

            $row++;
            $zebra = !$zebra;   // flip the row color
        }

        // Condition Totals Row
        $html .= "<tr style='background-color:rgba(" . $stripes[$zebra] . ", 0.96)'><td id=r" . $row . "c0 class='landlord'>Condition Total</td>";
        $col = 1;
        $rowTotal = 0;
        foreach ($colTotals as $val) {
            $html .= "<td id=r" . $row . "c" . $col++ . " class='Total'>" . $val . "</td>";
            $rowTotal += $val;
        }
        $html .= "<td id=r" . $row . "c" . $col . " class='Total'>" . $rowTotal . "</td></tr>";

        
        $html .= "</table>";
        return $html;
	}
	
	private function REPORTconditionbylandlord() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['start_date']);
        $end_date = strtotime($_REQUEST['end_date']) + 86400;   // +1 day to make date range inclusive

		$landlords = "SELECT * FROM slc_landlord";
        $db = new PHPWS_DB();
        $landlords = $db->select(null, $landlords);
        $landlordnames = array();
        foreach( $landlords as $landlord )
        	$landlordnames[] = $landlord['name'];
            
        $issues = "SELECT * FROM slc_problem WHERE type LIKE 'Conditions' "; // Covers generic landlord-tenant, too
        $db = new PHPWS_DB();
        $issues = $db->select(null, $issues);
        $issuenames = array();
        foreach( $issues as $issue )
        	$issuenames[] = $issue['description'];
        $issuenames[] = "Conditions";

        // Get the issues listed ( all others 0 )
		$db = new PHPWS_DB();
        $db->addTable('slc_issue');
        $db->addTable('slc_problem');
        $db->addTable('slc_landlord');
        $db->addTable('slc_visit');
        $db->addTable('slc_visit_issue_index');
        $db->addColumn('slc_problem.description');
        $db->addColumn('slc_landlord.name');
        
        // 1st Join set
        // needs to be a left join to allow for landlord "not specified" which is recorded as NULL in DB
        $db->addJoin('left', 'slc_issue', 'slc_landlord', 'landlord_id', 'id');
        
        // 2nd Join set
        $db->addJoin('inner', 'slc_problem', 'slc_issue', 'id', 'problem_id');
        $db->addJoin('inner', 'slc_issue', 'slc_visit_issue_index', 'id', 'i_id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');
        
        // WHERE (type LIKE "Conditions" OR description LIKE "Conditions") AND initial_date BETWEEN $start_date AND $end_date
        $db->addWhere('slc_problem.type', 'Conditions', 'LIKE', NULL, 'conditions');
        $db->addWhere('slc_problem.description', 'Conditions', 'LIKE', 'OR', 'conditions');
        $db->addWhere('slc_visit.initial_date', $start_date, '>=', 'AND');
        $db->addWhere('slc_visit.initial_date', $end_date, '<', 'AND');
        $results = $db->select();
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }
        $html = "";
                
        $theMatrix = $landlordnames;
        foreach($landlordnames as $lname) { 
        	$theMatrix[$lname] = $issuenames;
        	foreach($issuenames as $iname)
        		$theMatrix[$lname][$iname] = 0; // Populate with 0s 
        }
        

        foreach( $results as $r ) {
            // If landlord name is NULL, set as "Other / Unspecified"
        	$name = isset($r['name']) ? $r['name'] : "Other / Unspecified";
        	$description = $r['description'];
        	
        	$theMatrix[$name][$description]++; // increment that value
        }
        
        // Row Colors
        $stripes = array(0=>"255, 255, 255", 1=>"255, 236, 139");
        $zebra = 0;

        $html .= "<table class='reportTable'>";

        // Header Row
        $html .= "<tr><th id=r0c0></th>";
        $col = 1;
        foreach ($issuenames as $issue) {
            $html .= "<th id=r0c" . $col++ . ">" . $issue . "</th>";
        }
        $html .= "<th>Landlord Total</th></tr>";

        // Table Body
        $colTotals = array();
        $row = 1;
        foreach ($landlordnames as $landlord) {
            $col = 0;
            $html .= "<tr style='background-color:rgba(" . $stripes[$zebra] . ", 0.96)'><td id=r" . $row . "c" . $col++ . " class='landlord'>" . $landlord . "</td>";
            
            $rowTotal = 0;
            foreach ($issuenames as $issue) {
                $value = $theMatrix[$landlord][$issue];
                $style = ($value == 0) ? "color:#ABABAB;" : "color:#000000; font-weight:bold;";

                $html .= "<td id=r" . $row . "c" . $col . " class='dataCell' style='" . $style . "'>" . $value . "</td>";
                $rowTotal += $value;

                // Increment the column total
                if (array_key_exists($issue, $colTotals)) {
                    $colTotals[$issue] += $value;
                } else {
                    $colTotals[$issue] = $value;
                }
            }

            $html .= "<td id=r" . $row . "c" . $col . " class='Total'>" . $rowTotal . "</td>";
            
            $row++;
            $zebra = !$zebra;   // flip the row color
        }

        // Condition Totals Row
        $html .= "<tr style='background-color:rgba(" . $stripes[$zebra] . ", 0.96)'><td id=r" . $row . "c0 class='landlord'>Condition Total</td>";
        $col = 1;
        $rowTotal = 0;
        foreach ($colTotals as $val) {
            $html .= "<td id=r" . $row . "c" . $col++ . " class='Total'>" . $val . "</td>";
            $rowTotal += $val;
        }
        $html .= "<td id=r" . $row . "c" . $col . " class='Total'>" . $rowTotal . "</td></tr>";


        $html .= "</table>";
        return $html;
	}
	
	private function REPORTproblembyyear() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['start_date']);
        $end_date = strtotime($_REQUEST['end_date']) + 86400;   // +1 day to make date range inclusive

		$db = new PHPWS_DB();
        $db->addTable('slc_visit_issue_index');
        $db->addTable('slc_visit');
        $db->addTable('slc_client');
        $db->addTable('slc_issue');
        $db->addTable('slc_problem');
        $db->addColumn('slc_client.classification');
        $db->addColumn('slc_problem.description');
        $db->addColumn('slc_problem.tree');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');
        $db->addJoin('inner', 'slc_visit', 'slc_client', 'c_id', 'id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_issue', 'i_id', 'id');
        $db->addJoin('inner', 'slc_issue', 'slc_problem', 'problem_id', 'id');
        $db->addWhere('slc_visit.initial_date', $start_date, '>=');
        $db->addWhere('slc_visit.initial_date', $end_date, '<', 'AND');
        $results = $db->select();
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }
        
        // return an "empty" message if $results is empty
        if (count($results) == 0) {
            $html = "There are no records for that time period.";
            return $html;
        }

        $html = "";
        
        $theMatrix = array();
        $problems = array();
        $years = array();

        /*
         * For each row where the type is Conditions or Landlord-Tenant, change
         * the type to a generic Landlord-Tenant problem.
         */
        foreach ($results as $key=>$r) {
            if (isset($r['description']) && ($r['description'] == 'Conditions')) {
                $results[$key]['description'] = 'Landlord-Tenant';
                $results[$key]['tree'] = '';
            }
            if (isset($r['tree']) && ($r['tree'] == 'Landlord-Tenant -> ' || $r['tree'] == 'Landlord-Tenant -> Condition -> ')) {
                $results[$key]['description'] = 'Landlord-Tenant';
                $results[$key]['tree'] = '';
            }
        }
       
        // Replace the 'FR' with 'Freshman', 'SO' with 'Sophomore', and so on
        foreach ($results as $key=>$val) {
            switch ($val['classification']) {
                case 'FR':
                    $results[$key]['classification'] = 'Freshman';
                    break;
                case 'SO':
                    $results[$key]['classification'] = 'Sophomore';
                    break;
                case 'JR':
                    $results[$key]['classification'] = 'Junior';
                    break;
                case 'SR':
                    $results[$key]['classification'] = 'Senior';
                    break;
                case '':
                    $results[$key]['classification'] = 'Other';
                    break;
                default:
                    break;
            }
        }

        foreach( $results as $r ) {
        	if ( !in_array($r['classification'], $years) ) {
        		$years[] = $r['classification'];
            }
        }
        
        // Sort the 'years' array
        $classes = array('Freshman', 'Sophomore', 'Junior', 'Senior', 'Other');
        $tempArray = array_intersect($classes, $years);
        $years = array_unique(array_merge($tempArray, $years));

        foreach( $results as $r ) {

        	$description = isset($r['description']) && isset($r['tree']) ? $r['tree'].' '.$r['description'] : "not specified";
        	$year = $r['classification'];

        	if ( !in_array($description, $problems) )
        		$problems[] = $description;	

        	if ( isset($theMatrix[$description]) ) { 
        		$theMatrix[$description][$year]++;
        	} else {
        		$theMatrix[$description] = array();
        		foreach ($years as $tempyear) {
        			$theMatrix[$description][$tempyear] = 0;
        		}
        		
        		$theMatrix[$description][$year] = 1;
        	} 
        }
		
        $bgcolor = array(0=>"255,255,255",1=>"255,236,139");
        $rc = 0;

        $html .= "<table>";
        $html .= "<tr><th>Problem Type</th>";
        
        // Total the columns
        $totals = array_flip($years);
        foreach ($totals as $key=>$val) {
            $totals[$key] = 0;
        }

        foreach ( $years as $year ) {
        	$html .= "<th>&nbsp;".$year."&nbsp;</th>";
        }
        $html .= "</tr>";
        foreach ( $problems as $description ) {
        	$html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'>";
        	$html .= "<td>".$description."</td>";
        	$prevCount = 0;
        	foreach ( array_keys($theMatrix[$description]) as $year ) {
        		$html .= "<td style='text-align:center;'>";
        		$html .= ($theMatrix[$description][$year] != 0) ? $theMatrix[$description][$year] : '<span style="color:#BFBFBF;">0</span>';
        		$html .= "</td>";
                
                if ($theMatrix[$description][$year] != 0) {
                    $totals[$year] += $theMatrix[$description][$year];
                }
        	}
        	
        	$html .= "</tr>";
        	$rc = !$rc;
        }
        $html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'><td><strong>Totals</strong></td>";
        foreach ($totals as $total) {
            $html .= "<td style='text-align:center;'>".$total."</td>";
        }
        $html .= "</tr>";
        $html .= "</table>";
        
    	return $html;   
	}

    /**
     * This method builds the Appointment Statistics report.
     */
	private function REPORTfollowupappts() {
        $initialVisits  = 0;
        $clients        = 0;
        $issues         = 0;
        $followups      = 0;
        
        // Get date range from user
        $start_date = strtotime($_REQUEST['start_date']);
        $end_date = strtotime($_REQUEST['end_date']) + 86400;   // +1 day to make date range inclusive

        // Get the array of all visits whose initial visit happened in the time period. Equivalent to this query:
        // SELECT DISTINCT(id) FROM slc_visit WHERE initial_date >= $start_date AND initial_date < $end_date;
        $db = new PHPWS_DB('slc_visit');
        $db->addColumn('id', null, null, null, true);
        $db->addWhere('initial_date', $start_date, '>=');
        $db->addwhere('initial_date', $end_date, '<', 'AND');
        $visitIds = $db->select('col');
        
        // Get # of initial visits. Equivalent to this query: 
        // SELECT COUNT(DISTINCT(v_id)) FROM slc_visit_issue_index
        // WHERE v_id IN $visitIds;
        $db = new PHPWS_DB('slc_visit_issue_index');
        $db->addColumn('v_id', null, null, true, true);
        $db->addWhere('v_id', $visitIds, 'IN', 'AND');
        $initialVisits = $db->select('one');
        
        // Get the array of different 'counts' greater than 1. Equivalent to this query:
        // SELECT DISTINCT(counter) FROM slc_visit_issue_index WHERE counter>'1' ORDER BY counter DESC;
        $db->reset();
        $db->addColumn('counter', null, null, null, true);
        $db->addWhere('counter', '1', '>');
        $db->addOrder('counter desc');
        $counters = $db->select('col');
        
        //TODO: Make followups only count if they occured during the time period.
        // As of 05/20/2013 this is impossible due to the structure of the DB. We need to track when each followup occured.
        // For now we just count all followups for visits whose initial visit took place within the time period.

        // Calculate the number of followup visits.
        $visits = array();
        $db = new PHPWS_DB('slc_visit_issue_index');
        $db->addColumn('v_id', null, null, null, true);
        foreach ($counters as $count) {
            $db->addWhere('v_id', $visitIds, 'IN');
            
            // Make sure you don't count visits with multiple issues if they've already been counted.
            if (!empty($visits)) {
                $db->addWhere('v_id', $visits, 'NOT IN', 'AND');
            }
            
            $db->addWhere('counter', $count, '=', 'AND');
            $result = $db->select('col');
            $visits = $visits + $result;
            $followups += ($count-1) * count($result);
            $db->resetWhere();
        }

        // Get # of clients. Equivalent to this query:
        // SELECT COUNT(DISTINCT(id)) FROM slc_client
        // WHERE first_visit >= $start_date AND first_visit < $end_date;
        $db = new PHPWS_DB('slc_client');
        $db->addColumn('id', null, null, true, true);
        $db->addWhere('first_visit', $start_date, '>=');
        $db->addWhere('first_visit', $end_date, '<', 'AND');
        $clients = $db->select('one');

        // Get # of issues. Equivalent to this query: 
        // SELECT COUNT(DISTINCT(id)) FROM slc_issue
        // JOIN slc_visit_issue_index ON slc_issue.id = slc_visit_issue_index.i_id
        // WHERE slc_visit_issue_index.v_id IN $visitIds;
        $db = new PHPWS_DB('slc_issue');
        $db->addColumn('id', null, null, true, true);
        $db->addTable('slc_visit_issue_index', 'svii');
        $db->addWhere('slc_issue.id', 'svii.i_id');
        $db->addWhere('svii.v_id', $visitIds, 'IN', 'AND');
        $issues = $db->select('one');

        $bgcolor = array(0=>"255,255,255",1=>"255,236,139");
        $rc = 0;
        
        $html = "";
        $html .= "<table style='width:300px;'><tr><th>Category</th><th>Statistic</th></tr>";
        $html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'><td>Total Clients </td><td>".$clients."</td></tr>";
        $html .= "<tr style='background-color:rgba(".$bgcolor[!$rc].",0.96);'><td>Total Issues </td><td>".$issues."</td></tr>";
        $html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'><td>Total Initial Visits </td><td>".$initialVisits."</td></tr>";
        $html .= "<tr style='background-color:rgba(".$bgcolor[!$rc].",0.96);'><td>Total Followups </td><td>".$followups."</td></tr>";
        if ($clients == 0) {   // If $clients == 0, then all the statistics will be 0
            $html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'><td>Issues per Visit (w/o Followups)</td><td>0</td></tr>";
            $html .= "<tr style='background-color:rgba(".$bgcolor[!$rc].",0.96);'><td>Visits per Client (w/o Followups)</td><td>0</td></tr>";
            $html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'><td>Visits per Client (with Followups)</td><td>0</td></tr>";
            $html .= "<tr style='background-color:rgba(".$bgcolor[!$rc].",0.96);'><td>Followups per Issue </td><td>0</td></tr>";
            $html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'><td>Followups per Visit </td><td>0</td></tr>";
        } else {
            $html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'><td>Issues per Visit (w/o Followups)</td><td>" . round($issues / $initialVisits, 2) . "</td></tr>";
            $html .= "<tr style='background-color:rgba(".$bgcolor[!$rc].",0.96);'><td>Visits per Client (w/o Followups)</td><td>" . round($initialVisits / $clients, 2) . "</td></tr>";
            $html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'><td>Visits per Client (with Followups)</td><td>" . round(($initialVisits + $followups) / $clients, 2) . "</td></tr>";
            $html .= "<tr style='background-color:rgba(".$bgcolor[!$rc].",0.96);'><td>Followups per Issue </td><td>" . round($followups / $issues, 2) . "</td></tr>";
            $html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'><td>Followups per Visit </td><td>" . round($followups / $initialVisits, 2) . "</td></tr>";
        }
    	$html .= "</table>";
        
        return $html;
	}
	
	
	private function REPORTtypeofcondition() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['start_date']);
        $end_date = strtotime($_REQUEST['end_date']) + 86400;   // +1 day to make date range inclusive

		$db = new PHPWS_DB('slc_problem');
        $db->addColumn('slc_problem.description', NULL, 'descript');
        $db->addJoin('inner', 'slc_problem', 'slc_issue', 'id', 'problem_id');
        $db->addJoin('inner', 'slc_issue', 'slc_visit_issue_index', 'id', 'i_id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');
        $db->addWhere('slc_problem.type', 'Conditions', 'LIKE');
        $db->addWhere('slc_visit.initial_date', $start_date, '>=', 'AND');
        $db->addWhere('slc_visit.initial_date', $end_date, '<', 'AND');
        $results = $db->select();
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }
        
        // return an "empty" message if $results is empty
        if (count($results) == 0) {
            $html = "There are no records for that time period.";
            return $html;
        }
        
        $conditions = array();
        
        foreach ($results as $r) {
        	if ( !array_key_exists($r['descript'], $conditions) )
        		$conditions[$r['descript']] = 1;
        	else
        		$conditions[$r['descript']]++;
        }
        
        $bgcolor = array(0=>"255,255,255",1=>"255,236,139");
        $rc = 1;
        
        $html = "";
        $html .= "<table style='width:200px;'><tr><th>Category</th><th>Statistic</th></tr>";
                
        foreach($conditions as $k=>$c)
        	$html .= "<tr style='background-color:rgba(".$bgcolor[($rc = !$rc)].",0.96);'><td>".$k."</td><td>".$c."</td></tr>";

        $html .= "</table>";
        	
        return $html;
	}
	
	
	private function REPORTtypeofreferral() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['start_date']);
        $end_date = strtotime($_REQUEST['end_date']) + 86400;   // +1 day to make date range inclusive

		$db = new PHPWS_DB('slc_referral_type');
        $db->addColumn('name');
        $db->addWhere('id', 'slc_client.referral');
        $db->addWhere('slc_client.first_visit', $start_date, '>=', 'AND');
        $db->addWhere('slc_client.first_visit', $end_date, '<', 'AND');
        $results = $db->select();
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }
        
        // return an "empty" message if $results is empty
        if (count($results) == 0) {
            $html = "There are no records for that time period.";
            return $html;
        }
        
        $referrals = array();
        
        foreach ($results as $r) {
        	if ( $r['name'] != '')
	        	if ( !array_key_exists($r['name'], $referrals) )
	        		$referrals[$r['name']] = 1;
	        	else
	        		$referrals[$r['name']]++;
        }
        
        $bgcolor = array(0=>"255,255,255",1=>"255,236,139");
        $rc = 1;
        
        $html = "";
        $html .= "<table style='width:200px;'><tr><th>Referral Type</th><th>Statistic</th></tr>";
                
        foreach($referrals as $k=>$c)
        	$html .= "<tr style='background-color:rgba(".$bgcolor[($rc = !$rc)].",0.96);'><td>".$k."</td><td>".$c."</td></tr>";

        $html .= "</table>";
        	
        return $html;
	}
	
	
	private function REPORTlawbyagency() {
        // Get date range from user
        $start_date = strtotime($_REQUEST['start_date']);
        $end_date = strtotime($_REQUEST['end_date']) + 86400;   // +1 day to make date range inclusive

		$db = new PHPWS_DB('slc_problem');
        $db->addColumn('slc_problem.description', NULL, 'agency');
        $db->addJoin('inner', 'slc_problem', 'slc_issue', 'id', 'problem_id');
        $db->addJoin('inner', 'slc_issue', 'slc_visit_issue_index', 'id', 'i_id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');
        $db->addWhere('slc_problem.type', 'Law Enforcement Agency', 'LIKE');
        $db->addWhere('slc_visit.initial_date', $start_date, '>=', 'AND');
        $db->addWhere('slc_visit.initial_date', $end_date, '<', 'AND');
        $results = $db->select();
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }
        
        // return an "empty" message if $results is empty
        if (count($results) == 0) {
            $html = "There are no records for that time period.";
            return $html;
        }
        
        $agencies = array();
        
        foreach ($results as $r) {
        	if ( !array_key_exists($r['agency'], $agencies) )
        		$agencies[$r['agency']] = 1;
        	else
        		$agencies[$r['agency']]++;
        }
        
        $bgcolor = array(0=>"255,255,255",1=>"255,236,139");
        $rc = 1;
        
        $html = "";
        $html .= "<table style='width:200px;'><tr><th>Agency</th><th>Statistic</th></tr>";
                
        foreach($agencies as $k=>$c)
        	$html .= "<tr style='background-color:rgba(".$bgcolor[($rc = !$rc)].",0.96);'><td>".$k."</td><td>".$c."</td></tr>";

        $html .= "</table>";
        	
        return $html;
	}
	
}
