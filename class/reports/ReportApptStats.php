<?php
namespace slc\reports;

class ReportApptStats extends Report {

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
        $followups      = 0;


        // Get the array of all visits whose initial visit happened in the time period. Equivalent to this query:
        // SELECT DISTINCT(id) FROM slc_visit WHERE initial_date >= $startDate AND initial_date < $endDate;

        $db = \Database::newDB();
        $pdo = $db->getPDO();
        $query = 'SELECT distinct(slc_visit.id) FROM slc_visit
                  WHERE (slc_visit.initial_date >= :start AND slc_visit.initial_date < :end)
                  GROUP BY slc_visit.id';
        $sth = $pdo->prepare($query);
        $sth->execute(array('start'=>$this->startDate, 'end'=>$this->endDate));
        $visitIds = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);


        // The next query relies on an array of strings, if the array is empty, add one.
        if (empty($visitIds))
        {
            $visitIds[0] = '';
        }
        // Get # of initial visits. Equivalent to this query:
        // SELECT COUNT(DISTINCT(v_id)) FROM slc_visit_issue_index
        // WHERE v_id IN $visitIds;
        $db = new \PHPWS_DB('slc_issue');
        $db->addColumn('v_id', null, null, true, true);
        $db->addWhere('v_id', $visitIds, 'IN', 'AND');
        $initialVisits = $db->select('one');

        // Get # of issues w/o follow ups
        $db = new \PHPWS_DB('slc_issue');
        $db->addColumn('id', null, null, true, true);
        $db->addWhere('v_id', $visitIds, 'IN', 'AND');
        $db->addWhere('counter', 0, '=');
        $issuesWO = $db->select('one');

        // Get # of issues with follow ups.
        $db = new \PHPWS_DB('slc_issue');
        $db->addColumn('id', null, null, true, true);
        $db->addWhere('v_id', $visitIds, 'IN', 'AND');
        $db->addWhere('counter', 0, '>');
        $issuesWith = $db->select('one');

        // Get the array of different 'counts' greater than 1. Equivalent to this query:
        // SELECT DISTINCT(counter) FROM slc_visit_issue_index WHERE counter>'1' ORDER BY counter DESC;
        $db = \Database::newDB();
        $pdo = $db->getPDO();
        $query = 'SELECT distinct(slc_issue.counter)
                  FROM slc_issue
                    WHERE (slc_issue.counter > 0)
                  GROUP BY slc_issue.counter
                  ORDER BY slc_issue.counter desc';
        $sth = $pdo->prepare($query);
        $sth->execute();
        $counters = $sth->fetchAll(\PDO::FETCH_COLUMN, 0);


        //TODO: Make followups only count if they occured during the time period.
        // As of 05/20/2013 this is impossible due to the structure of the DB. We need to track when each followup occured.
        // For now we just count all followups for visits whose initial visit took place within the time period.

        // Calculate the number of followup visits.
        $visits = array();
        $db = new \PHPWS_DB('slc_issue');
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


            $followups += ($count) * count($result);
            $db->resetWhere();
        }

        // Get # of clients. Equivalent to this query:
        // SELECT COUNT(DISTINCT(id)) FROM slc_client
        // WHERE first_visit >= $startDate AND first_visit < $endDate;
        $db = \Database::newDB();
        $pdo = $db->getPDO();
        $query = 'SELECT count(distinct(slc_visit.client_id))
                  FROM slc_visit
                        WHERE (slc_visit.initial_date >= :startDate
                              AND slc_visit.initial_date < :endDate)';
        $sth = $pdo->prepare($query);
        $sth->execute(array('startDate'=>$this->startDate, 'endDate'=>$this->endDate));
        $clients = $sth->fetchColumn();


        // Get # of issues. Equivalent to this query:
        // SELECT COUNT(DISTINCT(id)) FROM slc_issue
        // JOIN slc_visit_issue_index ON slc_issue.id = slc_visit_issue_index.i_id
        // WHERE slc_visit_issue_index.v_id IN $visitIds;
        $db = new \PHPWS_DB('slc_issue');
        $db->addColumn('id', null, null, true, true);
        $db->addTable('slc_issue', 'svii');
        $db->addWhere('svii.v_id', $visitIds, 'IN', 'AND');
        $issues = $db->select('one');

        $content = array();

        $content['CLIENTS'] = $clients;
        $content['INITIAL_VISITS'] = $initialVisits;
        $content['ISSUES'] = $issues;
        $content['FOLLOWUPS'] = $followups;

        if ($clients == 0)
        {
            $content['I_WO'] = 0;
            $content['I_WITH'] = 0;
            $content['IPV'] = 0;
        }
        else
        {

            $content['I_WO'] = $issuesWO;
            $content['I_WITH'] = $issuesWith;
            $content['IPV'] = round($issues / $initialVisits, 2);
        }

        $this->content = $content;
	}

    public function getHtmlView()
    {
        return \PHPWS_Template::process($this->content, 'slc','AppointmentStatistics.tpl');
    }

    public function getCsvView()
    {
        $csvReport = new CsvReport();

        $cols = array(  'Total Students',
                        'Total Visits',
                        'Total Issues',
                        'Total Followups',
                        'Total Issues (w/o Followups)',
                        'Total Issues (with Followups)',
                        'Issue per Visit');
        $rows = array(  $this->content['CLIENTS'],
                        $this->content['INITIAL_VISITS'],
                        $this->content['ISSUES'],
                        $this->content['FOLLOWUPS'],
                        $this->content['I_WO'],
                        $this->content['I_WITH'],
                        $this->content['IPV']);
        $data = $csvReport->sputcsv($cols);
        $data .= $csvReport->sputcsv($rows);

        return $data;
    }
}
