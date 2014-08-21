<?php 

require_once PHPWS_SOURCE_DIR . 'lib/Swift/lib/swift_required.php';

class POSTSendMail extends AJAX {

	public function execute(){
	
		if ( !isset($_REQUEST['client_id']) ) {
			throw new Exception('Client no longer in database.');
			return;
		}
		
		
		$template = array("cName" => $_SESSION['cname']);

		$content = PHPWS_Template::process($template, 'slc', 'studentSurveyEmail.tpl');

		$banner = $_SESSION['actID'];

		$query = 'SELECT username FROM slc_student_data WHERE id =' . $banner;
		
		$db = new PHPWS_DB();
		$username = $db->select(null, $query);
		$username = $username[0]['username'];
		
		$message = Swift_Message::newInstance();

		$message->setSubject('How was your experience with Student Legal Clinic?');
		$message->setFrom('dos@appstate.edu');
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
