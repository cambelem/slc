<?php
namespace slc\reports;

class ReportLawByAgency extends Report {

    public $content;
    public $startDate;
    public $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->execute();
    }

    public function execute()
    {    
        $db = new \PHPWS_DB('slc_problem');
        $db->addColumn('slc_problem.description', NULL, 'agency');
        $db->addJoin('inner', 'slc_problem', 'slc_issue', 'id', 'problem_id');
        $db->addJoin('inner', 'slc_issue', 'slc_visit_issue_index', 'id', 'i_id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');
        $db->addWhere('slc_problem.type', 'Law Enforcement Agency', 'LIKE');
        $db->addWhere('slc_visit.initial_date', $this->startDate, '>=', 'AND');
        $db->addWhere('slc_visit.initial_date', $this->endDate, '<', 'AND');
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

        $this->content = $content;       
    }

    public function getHtmlView()
    {
        return \PHPWS_Template::process($this->content, 'slc','LawByAgency.tpl');
    }

}

?>