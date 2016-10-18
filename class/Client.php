<?php 

namespace slc;

class Client {
	public $id;
	public $first_visit;
	public $classification;
	public $name;
	public $fname;
	public $lname;
	public $major;
	public $living_location;
	public $referral;
	public $referralString;
	public $transfer;
	public $international;
	
	public function __construct($id, $class = "unknown", $major = "unknown", $livingLocation = "unknown") {
		$this->id = $id;
		$this->classification = $class;
		$this->major = $major;
		$this->living_location = $livingLocation;

		$this->transfer = 0;
		$this->international = 0;

		$time = timestamp();
        $this->first_visit = $time;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getFirstVisit()
	{
		return $this->first_visit;
	}

	public function getClassification()
	{
		return $this->classification;
	}

	public function getName()
	{
		$this->name = $this->fname . ' ' . $this->lname;
		return $this->name;
	}

	public function getFirstName()
	{
		return $this->fname;
	}

	public function getLastName()
	{
		return $this->lname;
	}

	public function getMajor()
	{
		return $this->major;
	}

	public function getLivingLocation()
	{
		return $this->living_location;
	}

	public function getReferral()
	{
		return $this->referral;
	}

	public function getReferralString()
	{
		return $this->referralString;
	}

	public function getTransfer()
	{
		return $this->transfer;
	}

	public function getInternational()
	{
		return $this->international;
	}



	public function setId($id)
	{
		$this->id = $id;
	}

	public function setFirstVisit($first_visit)
	{
		$this->first_visit = $first_visit;
	}

	public function setClassification($classification)
	{
		$this->classification = $classification;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setFirstName($fname)
	{
		$this->fname = $fname;
	}

	public function setLastName($lname)
	{
		$this->lname = $lname;
	}

	public function setMajor($major)
	{
		$this->major = $major;
	}

	public function setLivingLocation($living_location)
	{
		$this->living_location = $living_location;
	}

	public function setReferral($referral)
	{
		$this->referral = $referral;
	}

	public function setReferralString($referralString)
	{
		$this->referralString = $referralString;
	}

	public function setTransfer($transfer)
	{
		$this->transfer = $transfer;
	}

	public function setInternational($international)
	{
		$this->international = $international; 
	}
}

 
