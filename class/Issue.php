<?php 

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
	

	public function __construct($issueID = null) {
		$this->id = $issueID;

	}
}

?>
