<?php 
namespace slc\indexes;

class VisitIssue {
	public $v_id;
	public $i_id;
	public $counter = null;
	public $resolve_date;
	public $last_access;

/*
        $vi->setVId($_REQUEST['visit_id']);
        $vi->setIId($results);
        $vi->setCounter(1);
        $time = timestamp();
        $vi->setLastAccess($time);
*/

	public function __construct($visitId = null, $issueId = null) {
		$this->v_id = $visitId;
		$this->i_id = $issueId;

		$this->counter = 1;
		$this->last_access = timestamp();

	}

	public function getIId()
	{
		return $this->i_id;
	}

	public function getVId()
	{
		return $this->v_id;
	}

	public function getCounter()
	{
		return $this->counter;
	}

	public function getResolveDate()
	{
		return $this->resolve_date;
	}

	public function getLastAccess()
	{
		return $this->last_access;
	}


	public function setIId($i_id)
	{
		$this->i_id = $i_id;
	}

	public function setVId($v_id)
	{
		$this->v_id = $v_id;
	}

	public function setCounter($counter)
	{
		$this->counter = $counter;
	}

	public function setResolveDate($resolve_date)
	{
		$this->resolve_date = $resolve_date;
	}

	public function setLastAccess($last_access)
	{
		$this->last_access = $last_access;
	}
}

?>