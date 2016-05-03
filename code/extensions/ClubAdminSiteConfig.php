<?php

class ClubAdminSiteConfig extends DataExtension {

    private static $db = array(
    );

    // Store relation to folder(FolderID)
    private static $has_one = array(
        // Store selected folder
        'PendingFolder' => 'Folder'
    );

    /*public function getTitle() {
        return  $this->Folder()->getTitle();
    }*/

    public function updateCMSFields(FieldList $fields) {

        $clubAdminTabTitle = _t('ClubAdmin.MENUTITLE', 'ClubAdmin');
        $fields->addFieldsToTab('Root.'.$clubAdminTabTitle,
            TreeDropdownField::create('PendingFolderID', _t('ClubAdminSiteConfig.PENDINGFOLDER', 'PendingFolder'),'Folder')
            ->setTreeBaseID('0')
        );
    }

}
