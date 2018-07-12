<?php

namespace SYBEHA\Clubmaster\Extensions;

use SilverStripe\Admin\LeftAndMainExtension;
use SilverStripe\Admin\CMSMenu;

/**
 * Class ClubAdminLeftAndMain
 * @package SYBEHA\Clubmaster\Extensions
 * @property \SilverStripe\Admin\LeftAndMain $owner
 */
class ClubAdminLeftAndMain extends LeftAndMainExtension
{

    public function init()
    {
        CMSMenu::remove_menu_item(Help::class);
        $items = CMSMenu::get_menu_items();
        foreach ($items as $key => $value) {
            Injector::inst()->get(LoggerInterface::class)->debug('key =' . $key . ' value = ' . $value);
        }
    }
}
