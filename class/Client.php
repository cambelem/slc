<?php 

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
	
	public function __construct($id = null) {
		$this->id = $id;
	}
}

?>
