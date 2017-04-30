<?php

/**
 * Add clubmaster specific behaviour to SiteConfig
 *
 * @package clubmaster
 * @subpackage extensions
 */
class ClubAdminSiteConfig extends DataExtension
{

    private static $db = array(
        'MembersDisplayed' => 'Int(25)'
    );

    // Store relation to folder(FolderID)
    private static $has_one = array(
        // Store selected folder
        'PendingFolder' => 'Folder'
    );


    public function updateCMSFields(FieldList $fields)
    {

        $clubAdminTabTitle = _t('ClubAdmin.MENUTITLE', 'ClubAdmin');

        $fields->addFieldToTab('Root.' . $clubAdminTabTitle,
            TreeDropdownField::create('PendingFolderID', _t('ClubAdminSiteConfig.PENDINGFOLDER', 'PendingFolder'), 'Folder')->setTreeBaseID('0')
        );
        $fields->addFieldToTab('Root.' . $clubAdminTabTitle,
            NumericField::create('MembersDisplayed', _t('ClubAdminSiteConfig.MEMBERSDISPLAYED', 'Amount of members displayed'))
        );
    }

}
