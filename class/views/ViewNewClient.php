<?php
	class ViewNewClient extends SLCView {
		public function display(CommandContext $context) {
			$this->setupTree();
		}
			
		private function setupTree() {
			$this->theTree["How Referred"][] = "Friend / Word of Mouth";
			$this->theTree["How Referred"][] = "Former Client";
			$this->theTree["How Referred"][] = "Parents";
			$this->theTree["How Referred"][] = "Off-Campus Community Relations Office";
			$this->theTree["How Referred"][] = "Student Conduct";
			$this->theTree["How Referred"][] = "Housing / Residence Life";
			$this->theTree["How Referred"][] = "Counseling Center";
			$this->theTree["How Referred"][] = "Academic Advisor";
			$this->theTree["How Referred"][] = "Professor";
			$this->theTree["How Referred"][] = "ASU Police Department";
			$this->theTree["How Referred"][] = "Community Source";
			$this->theTree["How Referred"][] = "Sign on Door";
			$this->theTree["How Referred"][] = "Flyer in Residence Hall";
			$this->theTree["How Referred"][] = "Other Advertising";
			$this->theTree["How Referred"][] = "Other Referral";
			$this->theTree["How Referred"][] = "Internet";
			$this->theTree["How Referred"][] = "Off-Campus Presentation";
			$this->theTree["How Referred"][] = "Orientation";
			$this->theTree["How Referred"][] = "Meet and Greet Packet";
		}
		
		private $theTree = array();
	}
?>
