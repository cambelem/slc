<?php
namespace slc\ajax;

class GETConditionByLandlord extends AJAX {

    private $pdo;
    private $issuenames;
    private $issueCount;
    private $overallCount;
    private $start_date;
    private $end_date;
    private $emptyLandlord = true;

    public function execute() {
        // Get date range from user
        $this->start_date = strtotime($_REQUEST['startDate']);
        $this->end_date = strtotime($_REQUEST['endDate']) + 86400;   // +1 day to make date range inclusive

        $landlords = "SELECT * FROM slc_landlord";
        $db = new \PHPWS_DB();
        $landlords = $db->select(null, $landlords);


        $issues = 'SELECT * FROM slc_problem WHERE (slc_problem.type IN ("Conditions") 
                   OR (slc_problem.type = "Generic" 
                   AND slc_problem.description IN ("Conditions")))';  
       
        $db = new \PHPWS_DB();
        $issues = $db->select(null, $issues);
        $this->issuenames = array();
        foreach( $issues as $issue )
            $this->issuenames[] = $issue['description'];


        $db = \Database::newDB();
        $this->pdo = $db->getPDO();

        // Building issues count based on the number of conditions
        $this->issueCount = array();
        for ($i = 0; $i < count($this->issuenames); $i++)
        {
            $this->issueCount[] = 0;
        }
       
        $content = array();
        foreach ($this->issuenames as $issue) {          
            $content['landlord_issue_repeat'][] = array('ISSUE_NAME' => $issue);
        }
        
        foreach ($landlords as $landlord)
        {      
            $row = $this->landlordRow($landlord);
            if ($row != null)
            {
                $content["landlord_tentant_repeat"][] = $row;
                $this->overallCount += $row["TOTAL"];
            }   
        }

        foreach ($this->issuenames as $key => $issue)
        {
            $word = str_replace(" ", "_", $issue);
            $word = str_replace("/", "OR", $word);

            $content[strtoupper($word)."_TOTAL"] = $this->issueCount[$key];

        }
        
        $content["OVERALL_TOTAL"] = $this->overallCount;
        /*
        echo('<pre>');
        var_dump($content);
        echo('</pre>');
        exit;
        */
        $tpl = \PHPWS_Template::process($content, 'slc','ConditionByLandlord.tpl');
        $this->addResult("__html", $tpl);
    }

    private function landlordRow($landlord)
    {
        $row = array();
        $rowCount = 0; 

        $query = 'SELECT slc_problem.description, count(*) as myCount FROM slc_issue 
              join slc_visit_issue_index ON slc_issue.id = slc_visit_issue_index.i_id 
              join slc_visit ON slc_visit.id = slc_visit_issue_index.v_id 
              LEFT OUTER JOIN slc_landlord ON slc_issue.landlord_id = slc_landlord.id 
              join slc_problem ON problem_id = slc_problem.id 
              WHERE (slc_problem.type IN ("Conditions") 
              OR (slc_problem.type = "Generic" 
              AND slc_problem.description IN ("Conditions"))) 
              AND initial_date >= :startDate
              AND initial_date <= :endDate
              AND slc_landlord.id = :lId 
              GROUP BY slc_problem.description'; //change by landlord id

        $sth = $this->pdo->prepare($query);
        $sth->execute(array("startDate"=>$this->start_date, "endDate"=>$this->end_date, "lId"=>$landlord['id']));
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
        $row["NAME"] = $landlord['name'];
        
        foreach ($result as $r)
        {
            foreach ($this->issuenames as $key => $issue)
            {
                
                if ($r["description"] == $issue)
                {
                    $word = str_replace(" ", "_", $r["description"]);
                    $word = str_replace("/", "OR", $word);

                    $row[strtoupper($word)] = $r["myCount"];
                    $rowCount += $r["myCount"];

                    
                    $this->issueCount[$key] += $r["myCount"];
                    $this->emptyLandlord = false;     
                }
                else
                {
                    $word = str_replace(" ", "_", $issue);
                    $word = str_replace("/", "OR", $word);

                    if (!isset($row[strtoupper($word)]) || $row[strtoupper($word)] < 1)
                    {
                        $row[strtoupper($word)] = 0;
                        $this->issueCount[$key] += 0;
                    }
                }
            }                     
        }

        $row["TOTAL"] = $rowCount;

        if ($this->emptyLandlord)
        {
            return null;
        }
        else
        {
            // Reset the flag otherwise it will remain false once a landlord has a value.
            $this->emptyLandlord = true;
            return $row;
        }
    }
}
/*
		$landlords = "SELECT * FROM slc_landlord";
        $db = new \PHPWS_DB();
        $landlords = $db->select(null, $landlords);
        $landlordnames = array();
        foreach( $landlords as $landlord )
        	$landlordnames[] = $landlord['name'];
            
        $issues = "SELECT * FROM slc_problem WHERE type LIKE 'Conditions' "; // Covers generic landlord-tenant, too
        $db = new \PHPWS_DB();
        $issues = $db->select(null, $issues);
        $issuenames = array();
        foreach( $issues as $issue )
        	$issuenames[] = $issue['description'];
        $issuenames[] = "Conditions";

        // Get the issues listed ( all others 0 )
		$db = new \PHPWS_DB();
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
        //$db->setTestMode();
        $results = $db->select();
        
        if(\PHPWS_Error::logIfError($results)){
            throw new \slc\exceptions\DatabaseException();
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
    
        //$tpl = \PHPWS_Template::process($html, 'slc','LandlordTenant.tpl');
        $this->addResult("__html", $html);
	}
}
*/
?>