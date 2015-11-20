<?php 
namespace slc;

class Issue {
	
	// database variables
	public $id;
	public $problem_id;
	public $landlord_id;
	
	// other variables
	public $name; //legacy
	public $last_access; // legacy
	public $counter;
	public $resolution_date;
	public $visit_issue_id;
	public $landlord_name;
	

	public function __construct($problemID = null, $landlordID = null) {
		$this->problem_id = $problemID;

		if ($landlordID == null)
		{
			// Sets the landlord id to 999 for Other/Unspecified
			$this->landlord_id = 999;
		}
		else
		{
			$this->landlord_id = $landlordID;
		}

	}

	public function getId()
	{
		return $this->id;
	}

	public function getProblemId()
	{
		return $this->problem_id;
	}

	public function getLandlordId()
	{
		return $this->landlord_id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getLastAccess()
	{
		return $this->last_access;
	}

	public function getCounter()
	{
		return $this->counter;
	}

	public function getResolutionDate()
	{
		return $this->resolution_date;
	}

	public function getVisitIssueId()
	{
		return $this->visit_issue_id;
	}

	public function getLandlordName()
	{
		return $this->landlord_name;
	}


	public function setId($id)
	{
		$this->id = $id;
	}

	public function setProblemId($problem_id)
	{
		$this->problem_id = $problem_id;
	}

	public function setLandlordId($landlord_id)
	{
		$this->landlord_id = $landlord_id;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setLastAccess($lAccess)
	{
		$this->last_access = $lAccess;
	}

	public function setCounter($counter)
	{
		$this->counter = $counter;
	}

	public function setResolutionDate($rDate)
	{
		$this->resolution_date = $rDate;
	}

	public function setVisitIssueId($vIssueId)
	{
		$this->visit_issue_id = $vIssueId;
	}

	public function setLandlordName($lName)
	{
		$this->landlord_name = $lName;
	}
}

?>
