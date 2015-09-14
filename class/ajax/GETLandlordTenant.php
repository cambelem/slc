<?php
namespace slc\ajax;

class GETLandlordTenant extends AJAX {

    private $overallCount = 0;
    private $issuenames;
    private $issueCount;
    private $pdo;
    private $emptyLandlord = true;
    private $start_date;
    private $end_date;

    public function execute() {
        // Get date range from user
        $this->start_date = strtotime($_REQUEST['startDate']);
        $this->end_date = strtotime($_REQUEST['endDate']) + 86400;   // +1 day to make date range inclusive

        $landlords = "SELECT * FROM slc_landlord";
        $db = new \PHPWS_DB();
        $landlords = $db->select(null, $landlords);


        $issues = 'SELECT * FROM slc_problem  
                   WHERE (slc_problem.type IN ("Landlord-Tenant", "Conditions") 
                   OR (slc_problem.type = "Generic" 
                   AND slc_problem.description IN ("Conditions", "Landlord-Tenant")))';  // Covers generic landlord-tenant, too
       
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
        
        $tpl = \PHPWS_Template::process($content, 'slc','LandlordTenant.tpl');
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
              WHERE (slc_problem.type IN ("Landlord-Tenant", "Conditions") 
              OR (slc_problem.type = "Generic" 
              AND slc_problem.description IN ("Conditions", "Landlord-Tenant"))) 
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
?>