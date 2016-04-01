<?php

class ClubAdmin extends ModelAdmin {

    private static $managed_models = array(
        'ClubMember',
        'ClubCategory'
    );

    private static $url_segment = 'clubmembers';

    private static $menu_title = 'Clubmanager';
}
