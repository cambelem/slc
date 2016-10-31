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


        $db = \Database::newDB();
        $pdo = $db->getPDO();
        $query = 'SELECT sum(transfer) as transfer, sum(international) as international
                  FROM slc_client';
        $sth = $pdo->prepare($query);
        $sth->execute();
        $result = $sth->fetch();
   
        $content = array();

        $content['TRANSFER'] = $result['transfer'];
        $content['INTERNAT'] = $result['international'];


        $this->content = $content;
	}

    public function getHtmlView()
    {
        return \PHPWS_Template::process($this->content, 'slc','TransferInternatStats.tpl');
    }

}
