<?php

namespace SYBEHA\Clubmaster\Pages;

use Page;

use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Assets\Folder;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Forms\LabelField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\View\Requirements;
/* Configuration */
use SilverStripe\Core\Config\Config;

/**
 * Enroll page template
 * Class EnrollPage
 * @package SYBEHA\Clubmaster\Pages
 */
class EnrollPage extends Page
{
    /*
     * Important: Please note: It is strongly recommended to define a table_name for all namespaced models.
     * Not defining a table_name may cause generated table names to be too long
     * and may not be supported by your current database engine.
     * The generated naming scheme will also change when upgrading to SilverStripe 5.0 and potentially break.
     */
    private static $table_name = 'EnrollPage';

    private static $singular_name = 'Mitgliedsantrag';
    private static $description = 'Seite für den Mitgliedsantrag';
    private static $can_be_root = false;
    private static $allowed_children = ['SYBEHA\Clubmaster\Pages\EnrollSuccessPage'];

    private static $db = [];

    // Store relation to folder(FolderID)
    private static $has_one = [
        // Store selected folder
        'Folder' => Folder::class
    ];

    /**
     *
     * @config
     */
    private static $request_folder = 'antraege';
    
    /**
     * {@inheritdoc}
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab(
            'Root.Main', 
            LabelField::create(
                _t('SYBEHA\Clubmaster\Pages\EnrollPage.CONTENT_LABEL','Form data is maintained within PHP code.')
            ), 'Content'
            );
        $fields->removeFieldFromTab('Root.Main', 'Content');
        $fields->addFieldToTab(
            'Root.Main',
            HtmlEditorField::create(
                'Content',
                'Inhalt',
                $this->Content,
                'cms'
            ),
            'Metadata'
        );
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        // Create a default folder to store forms
        if ($this->Folder()->ID == '0') {
            Config::inst()->get('EnrollPage', 'request_folder');
            $defaultFolderID = Folder::find_or_make('antraege')->ID;
            $this->owner->FolderID = $defaultFolderID;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFields()
    {
        $fields = parent::getSettingsFields();
        // Get the current member
        $member = $this->getMember();

        // Limit user access to settings by permission for "Change site structure" (SITETREE_REORGANISE)
        if (Permission::checkMember($member, 'SITETREE_REORGANISE')) {
            // Add folder to be selectable from settings (Root.Settings)
            $requestFolderTreeDropDown = TreeDropdownField::create(
                'FolderID',
                _t('SYBEHA\Clubmaster\Pages\EnrollPage.REQUESTSFOLDER', 'Folder:'),
                Folder::class
            )
                ->setDescription(
                    _t(
                        'SYBEHA\Clubmaster\Pages\EnrollPage.REQUESTSFOLDERDESCRIPTION',
                        'Folder to store files created by form'
                    ),
                    'Folder to store files created by form'
                );

            $fields->addFieldToTab("Root.Settings", $requestFolderTreeDropDown);
        }

        return $fields;
    }

    /**
     * Obtain a Member
     *
     * @param null|int|Member $member
     *
     * @return null|Member
     */
    protected function getMember($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        if (is_numeric($member)) {
            $member = Member::get()->byID($member);
        }

        return $member;
    }
}
