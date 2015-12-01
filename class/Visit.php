<?php
namespace slc;

class Visit {
	// database variables
	public $id;
	public $initial_date;
	
	
	// useful one
	public $client_id;
	public $issues = array();

	public function __construct($client_id, $time) {
		$this->client_id = $client_id;
        $this->initial_date = $time;
	}
	
	public function addIssue(Issue $issue) {
		$this->issues[$issue->id] = $issue;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function setInitialDate($initDate)
	{
		$this->initial_date = $initDate;
	}

	public function setClientId($cId)
	{
		$this->client_id = $cId;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getInitialDate()
	{
		return $this->initial_date;
	}

	public function getClientId()
	{
		return $this->client_id;
	}
}

?>