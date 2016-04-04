<?php

class ClubCategory extends DataObject
{

    private static $db = array(
        'Title' => 'Varchar(255)'
    );

    private static $has_many = array(
        'ClubMembers' => 'ClubMember'
    );

    public function canView($member = null) {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    public function canEdit($member = null) {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    public function canDelete($member = null) {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    public function canCreate($member = null) {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }
}
