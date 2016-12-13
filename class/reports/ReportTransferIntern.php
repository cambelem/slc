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

        $db = \Database::newDB();
        $pdo = $db->getPDO();
        $query = 'SELECT sum(transfer) as transfer, 
                         sum(international) as international
                  FROM   slc_client
                  WHERE  first_visit >= :startDate
                  AND    first_visit < :endDate';

        $sth = $pdo->prepare($query);
        $sth->execute(array("startDate"=>$this->startDate, "endDate"=>$this->endDate));

        $result = $sth->fetch();

        $content = array();

        if ($result['transfer'] == null && $result['international'] == null) {

            $content['NO_RECORDS'] = "There are no records for that time period.";
        } else {
            $row = array();
            $row['TRANSFER'] = $result['transfer'];
            $row['INTERNAT'] = $result['international'];
            $content['tranInter'][] = $row;
        }
        
        $this->content = $content;
	}

    public function getHtmlView()
    {
        return \PHPWS_Template::process($this->content, 'slc','TransferInternatStats.tpl');
    }

}
