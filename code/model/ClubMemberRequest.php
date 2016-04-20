<?php

class ClubMemberRequest extends DataObject
{

    private static $requestDate = array();

    private static $has_many = array(
        'MemberRequests' => 'File'
    );

    private static $summary_fields = array('MemberRequest');

    private static $searchable_fields = array();

    function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        return $labels;
    }

    function getCMSFields()
    {
        SS_Log::log("getCMSFields() called",SS_Log::WARN);
        $fields = parent::getCMSFields();

        $list = $this->getMemberRequests();
        //new GridField($name, $title, $list);
        $fields->addFieldToTab('Root.Main', GridField::create('MemberRequest','MemberRequest',$list));

        return $fields;
    }


    function getMemberRequests()
    {
        SS_Log::log("getMemberRequests() called",SS_Log::WARN);

        $folder = Folder::find_or_make('requests');

        //$files = Folder::get()->filter('Filename', $path)->First();
        //$folder = DataObject::get_one("Folder", "Filename = $path");
        $files = DataObject::get("File", "ParentID = '{$folder->ID}'");
        foreach ($files as $file) {
            SS_Log::log("type=".$file->getFileType(),SS_Log::WARN);
        }

        return $folder ? DataObject::get("File", "ParentID = '{$folder->ID}'") : false;
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
