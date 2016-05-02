<?php

class ClubMemberSettings extends DataObject
{

    private static $db = array(
    );

    // Store relation to folder(FolderID)
    private static $has_one = array(
        // Store selected folder
        'Folder' => 'Folder'
    );

    private static $summary_fields = array(
        'Title'
    );

    private static $searchable_fields = array();

    function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['Folder'] = _t('ClubMemberSettings.FOLDER', 'Folder');
        return $labels;
    }

    function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Folder');
        $fields->addFieldToTab('Root.Main',
            TreeDropdownField::create('FolderID', _t('ClubMemberSettings.FOLDER', 'Folder'),'Folder')->setTreeBaseID('0'));
        return $fields;
    }

    public function getTitle() {
        return  $this->Folder()->getTitle();
    }

    public function canView($member = null) {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    public function canEdit($member = null) {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    public function canDelete($member = null) {
        return Permission::check('CMS_ACCESS_LeftAndMain', 'any', $member);
    }

    public function canCreate($member = null) {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }
}
