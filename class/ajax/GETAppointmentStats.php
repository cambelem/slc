<?php
namespace slc\ajax;

class GETAppointmentStats extends AJAX {
	/**
     * This method builds the Appointment Statistics report.
     */
	public function execute() {
        $initialVisits  = 0;
        $clients        = 0;
        $issues         = 0;
        $followups      = 0;
        
        // Get date range from user
        $start_date = strtotime($_REQUEST['startDate']);
        $end_date = strtotime($_REQUEST['endDate']) + 86400;   // +1 day to make date range inclusive

        // Get the array of all visits whose initial visit happened in the time period. Equivalent to this query:
        // SELECT DISTINCT(id) FROM slc_visit WHERE initial_date >= $start_date AND initial_date < $end_date;

        $db = \Database::newDB();
        $pdo = $db->getPDO();
        $query = 'SELECT distinct(slc_visit.id) FROM slc_visit 
                  WHERE (slc_visit.initial_date >= :start AND slc_visit.initial_date < :end) 
                  GROUP BY slc_visit.id';
        $sth = $pdo->prepare($query);
        $sth->execute(array('start'=>$start_date, 'end'=>$end_date));
        $visitIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);

        // The next query relies on an array of strings, if the array is empty, add one.
        if (empty($visitIds))
        {
            $visitIds[0] = '';
        }
        // Get # of initial visits. Equivalent to this query: 
        // SELECT COUNT(DISTINCT(v_id)) FROM slc_visit_issue_index
        // WHERE v_id IN $visitIds;
       
        $db = new \PHPWS_DB('slc_visit_issue_index');
        $db->addColumn('v_id', null, null, true, true);
        $db->addWhere('v_id', $visitIds, 'IN', 'AND');
        $initialVisits = $db->select('one');


        // Get the array of different 'counts' greater than 1. Equivalent to this query:
        // SELECT DISTINCT(counter) FROM slc_visit_issue_index WHERE counter>'1' ORDER BY counter DESC;
        $db = \Database::newDB();
        $pdo = $db->getPDO();
        $query = 'SELECT distinct(slc_visit_issue_index.counter) FROM slc_visit_issue_index 
                  WHERE (slc_visit_issue_index.counter > "1") 
                  GROUP BY slc_visit_issue_index.counter 
                  ORDER BY slc_visit_issue_index.counter desc';
        $sth = $pdo->prepare($query);
        $sth->execute();
        $counters = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);


        //TODO: Make followups only count if they occured during the time period.
        // As of 05/20/2013 this is impossible due to the structure of the DB. We need to track when each followup occured.
        // For now we just count all followups for visits whose initial visit took place within the time period.

        // Calculate the number of followup visits.
        $visits = array();
        $db = new \PHPWS_DB('slc_visit_issue_index');
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
        $db = \Database::newDB();
        $pdo = $db->getPDO();
        $query = 'SELECT count(distinct(slc_client.id)) FROM slc_client 
                  WHERE (slc_client.first_visit >= :startDate AND slc_client.first_visit < :endDate)';
        $sth = $pdo->prepare($query);
        $sth->execute(array('startDate'=>$start_date, 'endDate'=>$end_date));
        $clients = $sth->fetchColumn();


        // Get # of issues. Equivalent to this query: 
        // SELECT COUNT(DISTINCT(id)) FROM slc_issue
        // JOIN slc_visit_issue_index ON slc_issue.id = slc_visit_issue_index.i_id
        // WHERE slc_visit_issue_index.v_id IN $visitIds;
        $db = new \PHPWS_DB('slc_issue');
        $db->addColumn('id', null, null, true, true);
        $db->addTable('slc_visit_issue_index', 'svii');
        $db->addWhere('slc_issue.id', 'svii.i_id');
        $db->addWhere('svii.v_id', $visitIds, 'IN', 'AND');
        $issues = $db->select('one');
     


        $content = array();

        $content['CLIENTS'] = $clients;
        $content['ISSUES'] = $issues;
        $content['INITIAL_VISITS'] = $initialVisits;
        $content['FOLLOWUPS'] = $followups;

        if ($clients == 0)
        {
            $content['IVP'] = 0;
            $content['VISITS_WO'] = 0;
            $content['VISITS_WITH'] = 0;
            $content['FPI'] = 0;
            $content['FPV'] = 0;
        }
        else
        {
            $content['IVP'] = round($issues / $initialVisits, 2);
            $content['VISITS_WO'] = round($initialVisits / $clients, 2);
            $content['VISITS_WITH'] = round(($initialVisits + $followups) / $clients, 2);
            $content['FPI'] = round($followups / $issues, 2);
            $content['FPV'] = round($followups / $initialVisits, 2);
        }



        $tpl = \PHPWS_Template::process($content, 'slc','AppointmentStatistics.tpl');
        
        //__html needed for React dangerouslySetInnerHTML to work.
        $this->addResult("__html", $tpl);  
	}
}

?>