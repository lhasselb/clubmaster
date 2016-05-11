<?php
/**
 * Disable Help link within LeftAndMain
 *
 * @package clubmaster
 * @subpackage extensions
 */
class CustomLeftAndMain extends LeftAndMainExtension {

    public function init() {

        CMSMenu::remove_menu_item('Help');

    }

}
