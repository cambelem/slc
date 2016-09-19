<?php
namespace slc;

class Issue {

	// database variables
	public $id;
	public $problem_id;
	public $landlord_id;

	// other variables
	public $name; //legacy
	public $counter;
	public $v_id;
	public $landlord_name;


	public function __construct($problemID = null, $landlordID = null) {
		$this->problem_id = $problemID;

		if ($landlordID == null)
		{
			// Sets landlord equal to null
			$this->landlord_id = null;
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

	public function getCounter()
	{
		return $this->counter;
	}

	public function getVisitId()
	{
		return $this->v_id;
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

	public function setCounter($counter)
	{
		$this->counter = $counter;
	}

	public function setVisitId($vId)
	{
		$this->v_id = $vId;
	}

	public function setLandlordName($lName)
	{
		$this->landlord_name = $lName;
	}
}
