<?php

class NavLinks {

    public function display()
    {
        $links['BRAND'] = 'Issue Tracker';
        $links['BRAND_LINK'] = 'index.php';

        if (Current_User::isDeity()) {
            $links['CONTROL_PANEL'] = PHPWS_Text::secureLink('Control Panel', 'controlpanel');
            $links['ADMIN_OPTIONS'] = ''; //dummy tag to show dropdown menu in template
        }   

        $links['USER_FULL_NAME'] = Current_User::getDisplayName();

        $auth = Current_User::getAuthorization();
        $links['LOGOUT_URI'] = $auth->logout_link;

        // Plug the navlinks into the navbar
        return PHPWS_Template::process($links, 'slc', 'navLinks.tpl'); 
    }

}
