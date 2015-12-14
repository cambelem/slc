<?php
namespace slc\reports;

class ReportPrbByYr extends Report {

    public $content;
    public $startDate;
    public $endDate;

    private $theMatrix;
    private $totals;
    private $classes;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->execute();
    }

    public function execute()
    {    
      $db = new \PHPWS_DB();
        $db->addTable('slc_visit_issue_index');
        $db->addTable('slc_visit');
        $db->addTable('slc_client');
        $db->addTable('slc_issue');
        $db->addTable('slc_problem');
        $db->addColumn('slc_client.classification');
        $db->addColumn('slc_problem.description');
        $db->addColumn('slc_problem.tree');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');
        $db->addJoin('inner', 'slc_visit', 'slc_client', 'client_id', 'id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_issue', 'i_id', 'id');
        $db->addJoin('inner', 'slc_issue', 'slc_problem', 'problem_id', 'id');
        $db->addWhere('slc_visit.initial_date', $this->startDate, '>=');
        $db->addWhere('slc_visit.initial_date', $this->endDate, '<', 'AND');

        $results = $db->select();


        /*
        SELECT slc_client.classification, slc_problem.description, slc_problem.tree, count(*) as myCount 
        FROM slc_visit_issue_index 
        INNER JOIN slc_visit ON slc_visit_issue_index.v_id = slc_visit.id 
        INNER JOIN slc_client ON slc_visit.client_id = slc_client.id 
        INNER JOIN slc_issue ON slc_visit_issue_index.i_id = slc_issue.id 
        INNER JOIN slc_problem ON slc_issue.problem_id = slc_problem.id 
        WHERE (slc_visit.initial_date >= '-2147540400' AND slc_visit.initial_date < '2147490000')
        */

        $content = array();
        
        // return an "empty" message if $results is empty
        if (count($results) == 0) {
            $content["NO_RECORDS"] = "There are no records for that time period.";
        }

        $this->theMatrix = array();
        $problems = array();

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

        
        // Sort the 'years' array
        $this->classes = array('Freshman', 'Sophomore', 'Junior', 'Senior', 'Other');

        foreach( $results as $r ) {

            $description = isset($r['description']) && isset($r['tree']) ? $r['tree'].' '.$r['description'] : "Not Specified";
            $year = $r['classification'];

            if ( !in_array($description, $problems) )
                $problems[] = $description; 

            if ( isset($this->theMatrix[$description]) ) { 
                $this->theMatrix[$description][$year]++;
            } else {
                $this->theMatrix[$description] = array();
                         
                foreach ($this->classes as $tempyear) {
                    $this->theMatrix[$description][$tempyear] = 0;
                }
                
                $this->theMatrix[$description][$year] = 1;
            } 
        }

        
        $this->totals = array_flip($this->classes);
        foreach ($this->totals as $key=>$val) {
            $this->totals[$key] = 0;
        }
          
        foreach ( $this->classes as $year ) {
            $content["problem_year_repeat"][] = array("YEAR" => $year);
        }

        foreach ( $problems as $description ) {

            $row = $this->problemTypeRow($description);
            $content['problem_repeat'][] = $row;
        }

        foreach ($this->classes as $key => $year)
        {

            $content[strtoupper($year."_TOTAL")] = $this->totals[$year];
        }
        $this->content = $content;
    }

    public function problemTypeRow($description)
    {
        $row = array();

        $row["PROBLEM_TYPE"] = $description; 

        // Adds missing classification if the database doesn't have it and
        // assigns the classification to 0.
        foreach ($this->classes as $classification)
        {
            if (!array_key_exists($classification, $this->theMatrix[$description]))
            {
                $this->theMatrix[$description][$classification] = 0;     
            }
        }

        // Stylizes the fields that are 0 so that it's opaque as well as assigns the number
        // to the template variable.
        foreach ( array_keys($this->theMatrix[$description]) as $year ) 
        {
            ($this->theMatrix[$description][$year] != 0) ? $row[strtoupper($year)] = $this->theMatrix[$description][$year]
                                                         : $row[strtoupper($year)] = '<span style="color:#BFBFBF;">0</span>';
            
            if ($this->theMatrix[$description][$year] != 0) 
            {
                $this->totals[$year] += $this->theMatrix[$description][$year];
            }
            else
            {
                 $this->totals[$year] += 0;
            }
        }
        return $row;
    }

    public function getHtmlView()
    {
        return \PHPWS_Template::process($this->content, 'slc','ProblemByYear.tpl');
    }
}

?>