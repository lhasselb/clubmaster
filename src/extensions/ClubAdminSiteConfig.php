<?php

namespace SYBEHA\Clubmaster\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Assets\Folder;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\TreeDropdownField;

/**
 * Add clubmaster specific behaviour to SiteConfig
 * Class ClubAdminSiteConfig
 * @package  SYBEHA\Clubmaster\Extensions
 * @property \SilverStripe\SiteConfig\SiteConfig $owner
 */
class ClubAdminSiteConfig extends DataExtension
{
    /*
     * Important: Please note: It is strongly recommended to define a table_name for all namespaced models.
     * Not defining a table_name may cause generated table names to be too long
     * and may not be supported by your current database engine.
     * The generated naming scheme will also change when upgrading to SilverStripe 5.0 and potentially break.
     */
    private static $table_name = 'SiteConfig';

    private static $db = [
        'MembersDisplayed' => 'Int(25)',
        'AddMandate' => 'Boolean',
        'SendApprovalMail' => 'Boolean'
    ];

    /**
     * Store (selected folder) relation to folder(FolderID)
     * @config $has_one
     * @var array
     */
    private static $has_one = [
        'PendingFolder' => Folder::class
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {

        $clubAdminTabTitle = _t('ClubAdmin.MENUTITLE', 'ClubAdmin');

        // Create a configuration variable to store pending member files
        $fields->addFieldToTab(
            'Root.' . $clubAdminTabTitle,
            TreeDropdownField::create(
                'PendingFolderID',
                _t(
                    'ClubAdminSiteConfig.PENDINGFOLDER',
                    'PendingFolder'
                ),
                Folder::class
            )->setTreeBaseID('0')
        );

        // Create a configuration variable to the amount of members displayed in the member view
        $fields->addFieldToTab(
            'Root.' . $clubAdminTabTitle,
            NumericField::create(
                'MembersDisplayed',
                _t(
                    'ClubAdminSiteConfig.MEMBERSDISPLAYED',
                    'Amount of members displayed'
                )
            )
        );

        // Create a configuration variable to enable / disable the automatic addition of a mandate reference
        $fields->addFieldToTab(
            'Root.' . $clubAdminTabTitle,
            CheckboxField::create(
                'AddMandate',
                _t(
                    'ClubAdminSiteConfig.ADDMANDATE',
                    'Automatic addition of mandate reference'
                )
            )
        );

        // Create a configuration variable to enable / disable the confirmation mail after been approved
        $fields->addFieldToTab(
            'Root.' . $clubAdminTabTitle,
            CheckboxField::create(
                'SendApprovalMail',
                _t(
                    'ClubAdminSiteConfig.SENDAPPROVEMAIL',
                    'Send E-Mail  after approval'
                )
            )
        );
    }
}
