<?php
namespace slc\reports;

class ReportTypeOfCond extends Report {

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
        $db->addColumn('slc_problem.description', NULL, 'descript');
        $db->addJoin('inner', 'slc_problem', 'slc_issue', 'id', 'problem_id');
        $db->addJoin('inner', 'slc_issue', 'slc_visit_issue_index', 'id', 'i_id');
        $db->addJoin('inner', 'slc_visit_issue_index', 'slc_visit', 'v_id', 'id');
        $db->addWhere('slc_problem.type', 'Conditions', 'LIKE');
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

        $this->content = $content;          
    }

    public function getHtmlView()
    {
        return \PHPWS_Template::process($this->content, 'slc','TypeOfCondition.tpl');
    }
}

 