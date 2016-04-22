<?php

class ClubMemberPending extends ClubMember
{


    function getCMSFields()
    {
        //SS_Log::log('getCMSFields() called',SS_Log::WARN);
        $fields = parent::getCMSFields();
        return $fields;
    }




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
