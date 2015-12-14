<?php
namespace slc\reports;

class ReportTypeOfRef extends Report {

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
        $db = new \PHPWS_DB('slc_referral_type');
        $db->addColumn('name');
        $db->addWhere('id', 'slc_client.referral');
        $db->addWhere('slc_client.first_visit', $this->startDate, '>=', 'AND');
        $db->addWhere('slc_client.first_visit', $this->endDate, '<', 'AND');
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
            $referrals = array();
            
            foreach ($results as $r) {
                if ( $r['name'] != '')
                    if ( !array_key_exists($r['name'], $referrals) )
                        $referrals[$r['name']] = 1;
                    else
                        $referrals[$r['name']]++;
            }

            $row = array();
            foreach($referrals as $key => $condition)
            {
                $row["R_TYPE"] = $key;
                $row["COUNT"] = $condition;

                $content['referral_type'][] = $row;
            }
        }

        $this->content = $content;          
    }

    public function getHtmlView()
    {
        return \PHPWS_Template::process($this->content, 'slc','TypeOfReferral.tpl');
    }
}

?>