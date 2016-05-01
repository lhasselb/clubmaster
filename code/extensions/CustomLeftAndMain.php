<?php

class CustomLeftAndMain extends LeftAndMainExtension {

    public function init() {

        CMSMenu::remove_menu_item('Help');

    }

}
