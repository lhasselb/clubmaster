<?php

/**
 * Disable Help link within LeftAndMain
 *
 * @package clubmaster
 * @subpackage extensions
 */

use SilverStripe\Admin\LeftAndMainExtension;

class CustomLeftAndMain extends LeftAndMainExtension
{

    public function init()
    {
        CMSMenu::remove_menu_item('Help');
    }
}
