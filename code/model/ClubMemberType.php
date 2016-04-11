<?php

class ClubMemberType extends DataObject
{

    private static $db = array(
        'TypeName' => 'Varchar(255)'
    );

    private static $has_many = array(
        'ClubMembers' => 'ClubMember'
    );

    private static $summary_fields = array(
        "TypeName"
    );

    private static $searchable_fields = array(
        "TypeName"
    );

    function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['TypeName'] = _t('ClubMemberType.TYPENAME', 'TypeName');
        $labels['ClubMembers'] = _t('ClubMemberType.CLUBMEMBERS', 'ClubMembers');
        return $labels;
    }

    function getCMSFields()
    {
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
