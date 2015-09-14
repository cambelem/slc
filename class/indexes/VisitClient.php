<?php
namespace slc\indexes; 

class VisitClient {
	public $id;
	public $c_id;
	public $v_id;

	public function getId()
	{
		return $this->id;
	}

	public function getCId()
	{
		return $this->c_id;
	}

	public function getVId()
	{
		return $this->v_id;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function setCId($c_id)
	{
		$this->c_id = $c_id;
	}

	public function setVId($v_id)
	{
		$this->v_id = $v_id;
	}
}

?>