<?php

class ClubMemberRequest extends DataObject
{

    private static $requestDate = array();

    private static $has_one = array(
        'Request' => 'ClubMember'
    );

    private static $summary_fields = array(
        "Salutation",
        "FirstName",
        "LastName"
    );

    private static $searchable_fields = array();

    function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['Salutation'] = _t('ClubMember.SALUTATION', 'Salutation');
        $labels['FirstName'] = _t('ClubMember.FIRSTNAME', 'FirstName');
        $labels['LastName'] = _t('ClubMember.LASTNAME', 'LastName');
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
