<?php
namespace slc;

\PHPWS_Core::initModClass('notification', 'NQ.php');

class NotificationView
{
    // Notification type constants
    const SUCCESS   = 0;
    const ERROR     = 1;
    const WARNING   = 2;
    const UNKNOWN   = 3;

    private $notifications = array();

    public function popNotifications()
    {
        $this->notifications = \NQ::popAll('slc');
    }
    
    public function display()
    {
        $notifications = \NQ::popAll('slc');
        $tags = array();
        foreach($notifications as $notification)
        {
            $type = self::getType($notification);
            $tags['NOTIFICATIONS'][][$type] = $notification->toString();
        }
        $content = \PHPWS_Template::process($tags, 'slc', 'NotificationView.tpl');
        return $content;
    }

    private static function getType(\Notification $notification)
    {
        switch($notification->getType()) {
            case NotificationView::SUCCESS:
                return 'SUCCESS';
            case NotificationView::ERROR:
                return 'ERROR';
            case NotificationView::WARNING:
                return 'WARNING';
            default:
                return 'UNKNOWN';
        }
    }
}