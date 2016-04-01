<?php

class ClubCategory extends DataObject
{

    private static $db = array(
        'Title' => 'Text'
    );

    private static $has_many = array(
        'ClubMembers' => 'ClubMember'
    );
}
