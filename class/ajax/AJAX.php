<?php 

abstract class AJAX {
	protected $result = array();
	public $data = array();
	abstract public function execute();
	
	public function setData( $data ) {
		$this->data = $data;
	}
	
	public function getResult() {
		return $this->result;
	}
	
	protected function resetResult() {
		$this->result = array();
	}
	
	protected function addResult($r, $v) {
		$this->result[$r] = $v;
	}
}

?>