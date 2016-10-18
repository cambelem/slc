<?php
namespace slc\reports;

class ReportTransferIntern extends Report {

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


        $content = array();

        $content['TRANSFER'] = $clients;
        $content['INTERNAT'] = $initialVisits;

        if ($clients == 0)
        {
            $content['TRANSFER'] = 0;
            $content['INTERNAT'] = 0;
        }
        else
        {

            $content['I_WO'] = $issuesWO;
            $content['I_WITH'] = $issuesWith;
        }

        $this->content = $content;
	}

    public function getHtmlView()
    {
        return \PHPWS_Template::process($this->content, 'slc','TransferInternatStats.tpl');
    }

}
