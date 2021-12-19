<?php

namespace Sybeha\Clubmaster\Extensions;

use SilverStripe\Admin\LeftAndMainExtension;
use SilverStripe\Admin\CMSMenu;
/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

/**
 * Class ClubAdminLeftAndMain
 *
 * @package  Sybeha\Clubmaster\Extensions
 * @property \SilverStripe\Admin\LeftAndMain $owner
 */
class ClubAdminLeftAndMain extends LeftAndMainExtension
{

    public function init()
    {
        // Hide Help
        CMSMenu::remove_menu_item('Help');

        // Hide SilverStripe-CampaignAdmin-CampaignAdmin
        CMSMenu::remove_menu_item('SilverStripe-CampaignAdmin-CampaignAdmin');
        /*
        $items = CMSMenu::get_menu_items();
        foreach ($items as $key => $value) {
            Injector::inst()->get(LoggerInterface::class)
            ->debug('code =' . CMSMenu::get_menu_code($key));
            //foreach ($value as $key => $value) {
            //    Injector::inst()->get(LoggerInterface::class)
            //    ->debug('key =' . $key . ' value =' . $value);
            //}
        }
        */
    }
}
