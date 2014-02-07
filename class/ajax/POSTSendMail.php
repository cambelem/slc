<?php 

require_once 'lib/Swift/lib/swift_required.php';

class POSTSendMail extends AJAX {

	public function execute(){
	
		if ( !isset($_REQUEST['client_id']) ) {
			throw new Exception('Client no longer in database.');
			return;
		}
		
		
		$template = array("cName" => $_SESSION['cname']);
		$module = "slc";
		$file  = "emailslc.tpl";

		$content = PHPWS_Template::process($template, $module, $file);

		$banner = $_SESSION['actID'];		

		$query = 'SELECT username FROM slc_student_data WHERE id =' . $banner;
		
		$db = new PHPWS_DB();
		$username = $db->select(null, $query);
		$username = $username[0]['username'];
		
		$message = Swift_Message::newInstance();

		$message->setSubject('Check-in Confirmation');
		$message->setFrom('studentconduct@appstate.edu');
		$message->setTo($username . '@appstate.edu');

		$message->setBody($content);
		
		$transport = Swift_SmtpTransport::newInstance('localhost');
		$mailer = Swift_Mailer::newInstance($transport);

		$mailer->send($message);
		
		//echo $username . '@appstate.edu';
		//exit;
				
	}
}

?>