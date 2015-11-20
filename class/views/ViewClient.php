<?php 
namespace slc\views;

class ViewClient extends View {
	public function display(\slc\CommandContext $context) {
		$banner_id = $_REQUEST['banner_id'];
		$result = $this->checkBannerID($banner_id);
		
		if ($result)
		{
			$HTMLcontent = "";
			$referral = "";
		
		 	$content = array();

			\javascriptMod('slc', 'viewClient', array('BANNER_ID'=>$banner_id));
				 
		 	$HTMLcontent .= \PHPWS_Template::process($content, 'slc', 'Client.tpl');
		 	
		 	return parent::useTemplate($HTMLcontent); // Insert into the accessible div
		}
		else
		{
			\NQ::simple('slc', \slc\NotificationView::ERROR, 'Banner ID is invalid.');
			header('Location: ./?module=slc');
		}	
	}

	public function checkBannerID($banner_id)
	{
		$db = \Database::newDB();
		$pdo = $db->getPDO();

		$query = 'SELECT id 
				  FROM slc_student_data 
				  WHERE id = :bannerId';

		$sth = $pdo->prepare($query);
		$sth->execute(array('bannerId'=>$banner_id));
		$result = $sth->rowCount();
		return $result;
	}
}
?>
