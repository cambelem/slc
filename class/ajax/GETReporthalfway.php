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
	
	private function REPORTintakebyproblemtype() {
		$query = "SELECT COUNT(DISTINCT i.id) as count, p.description FROM slc_issue i, slc_problem p WHERE i.problem_id = p.id GROUP BY problem_id";
		
		$db = new PHPWS_DB();
        //$db->setTestMode();
        $results = $db->select(null, $query);
        
        if(PHPWS_Error::logIfError($results)){
            throw new DatabaseException();
        }
        
        $html = "";
        $html .= "<table class='reportTable'>";
        $html .= "<tr><th style='width:150px;'>Situation</th><th>Record</th></tr>";
        $bgcolor = array(0=>"#FFFFFF",1=>"#FFEC8B");
        $rc = 0;
		
        foreach( $results as $r ) {
        	$count = $r['count'];
        	$type = $r['description'];
        	
        	$html .= "<tr style='background-color:".$bgcolor[$rc].";'><td>".$type."</td><td>".$count."</td></tr>"; 
        	$rc = !$rc;
        }
                
        $html .= "</table>";

		return $html;
	}

	       	//' (SELECT COUNT(DISTINCT id) FROM landlord) AS landlords, '.
			//' (SELECT COUNT(DISTINCT id) FROM issue WHERE landlord_id IS NOT NULL) AS issues";
	
	private function REPORTlandlordtenant() {
        $landlords = "SELECT * FROM slc_landlord";
        $db = new PHPWS_DB();
        $landlords = $db->select(null, $landlords);
        $landlordnames = array();
        foreach( $landlords as $landlord )
        	$landlordnames[] = $landlord['name'];
        $landlordnames[] = "not specified"; // Create a "NULL" row
            
        $issues = "SELECT * FROM slc_problem WHERE tree LIKE '%Landlord-Tenant%' OR description LIKE 'Conditions' OR description LIKE 'Landlord-Tenant' "; // Covers generic landlord-tenant, too
        $db = new PHPWS_DB();
        $issues = $db->select(null, $issues);
        $issuenames = array();
        foreach( $issues as $issue )
        	$issuenames[] = $issue['description'];
    	
        
		// Get the issues listed ( all others 0 )
		$query = 'SELECT p.description, l.name '.
			' FROM slc_issue i '.
			' LEFT JOIN slc_problem p ON i.problem_id = p.id '.
			' LEFT JOIN slc_landlord l ON i.landlord_id = l.id ';
		
		$db = new PHPWS_DB();
        //$db->setTestMode();
        $results = $db->select(null, $query);
        
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
            //if(!isset($r['name'])){
            //    continue;
            //}

        	$name = isset($r['name']) ? $r['name'] : "not specified";
        	$description = $r['description'];
        	
        	$theMatrix[$name][$description]++; // increment that value
        }

        
        $html = "";
        $html .= "<div style='overflow-x: scroll'>";
        $html .= "<table class='tableWithFloatingHeader reportTable'>"; //#E3E3E3
        $html .= "<tr><th id='firstTd' style='width:220px;height:54px;background-color:rgba(227,227,227,0.96);z-index:999;'></th>";//<th style='padding-right:220px;'>&nbsp;</th>";
		$html .= '<th><div id="divHeader" style="overflow: hidden; width: 284px;"><table><tr>';
        // generate issue headers
        foreach( $issuenames as $issue ) {
        	$html .= "<th style=''>$issue</th>";	
        }
        $html .=  "<th style=''>Landlord Total</th></tr></table></div></th>";	
        $html .= "</tr>";
        
        // generate rows
        $bgcolor = array(0=>"255,255,255",1=>"255,236,139");
		$rc = 0;
		$colTotals = array();
		$landlordHTML = "";
        foreach( $landlordnames as $landlord) { // ROWS!
			$html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'>";
			$html .= "<th><div id='firstcol' style='overflow: hidden; height: 80px;'><table><th class='landlordcell' style='background-color:rgba(".$bgcolor[$rc].",0.96);position:absolute;width:220px;'>".$landlord."</td>";
			$landlordHTML .= "<tr><td style='background-color:rgba(".$bgcolor[$rc].",0.96);width:220px;'>".$landlord."</td></tr>";
			$html .= "<td style='padding-right:220px;'>&nbsp;</td>";
			$rowtotal = 0;
			
			foreach ($issuenames as $issue) { // COLUMNS!
				
				$value = $theMatrix[$landlord][$issue];
				$vStyle = ($value == 0) ? "color:#ABABAB;" : "color:#000000; font-weight:bold;"; 
								
				$html .= "<td class='countValue' style='width:600px;".$vStyle."'>".$value."</td>";
				$rowtotal += $value;
				$colTotals[$issue] += $value;
			}
			
			$vStyle = ($rowtotal == 0) ? "color:#ABABAB;" : "color:#000000; font-weight:bold;"; 
			$html .= "<td class='countValue' style='width:600px;".$vStyle."'>".$rowtotal."</td>";
			$html .= "</tr>"; 
        	$rc = !$rc; // flip color
        }
        
        $html .= "<tr style='background-color:rgba(".$bgcolor[$rc].",0.96);'>";
        $html .= "<td style='background-color:rgba(".$bgcolor[$rc].",0.96);position:absolute;width:220px;'><span style='font-weight:bold;'>Issue Totals</span></td>";
		$html .= "<td style='padding-right:220px;'>&nbsp;</td>";
		foreach ($issuenames as $issue) { // COLUMNS!
			$vStyle = ($colTotals[$issue] == 0) ? "color:#ABABAB;" : "color:#000000; font-weight:bold;"; 
			$html .= "<td class='countValue' style='width:600px;".$vStyle."'>".$colTotals[$issue]."</td>";
			$rowtotal += $colTotals[$issue];	
		}
		
		$vStyle = ($rowtotal == 0) ? "color:#ABABAB;" : "color:#000000; font-weight:bold;"; 
		$html .= "<td class='countValue' style='width:600px;".$vStyle."'>".$rowtotal."</td>";
        $html .= "</tr>";
        $html .= "</table></div><script type='text/javascript'>fnAdjustTable();</script>";

        //$landlordHTML = "<div id='landlordListing' style='position:absolute;top:0px;'><table style=''>".$landlordHTML."</table></div>";
        //$html .= $landlordHTML;
        
		return $html; // for current table        
	}
	
//	private function REPORTconditionbylandlord() {
		
//	}
}
