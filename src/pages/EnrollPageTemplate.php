<?php

namespace Sybeha\Clubmaster\Pages;

use Page;

/**
 * Enroll page template
 *
 * @package    clubmaster
 * @subpackage pages
 */
class EnrollPageTemplate extends Page
{
    /*
     * Important: Please note: It is strongly recommended to define a table_name for all namespaced models.
     * Not defining a table_name may cause generated table names to be too long
     * and may not be supported by your current database engine.
     * The generated naming scheme will also change when upgrading to SilverStripe 5.0 and potentially break.
     */
    private static $table_name = 'EnrollPageTemplate';
    private static $singular_name = 'Mitgliedsantrag';
    private static $description = 'Seite für den Mitgliedsantrag';
    private static $can_be_root = false;
    private static $allowed_children = array('EnrollPageSuccess');

    private static $db = array();

    // Store relation to folder(FolderID)
    private static $has_one = array(
        // Store selected folder
        'Folder' => 'Folder'
    );

    /**
     *
     * @config
     */
    private static $request_folder = 'antraege';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', LabelField::create('Das Formular wird im PHP-Code gepflegt.'), 'Content');
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
                _t('EnrollPage.REQUESTSFOLDER', 'Folder:'),
                'Folder'
            )
                ->setDescription(
                    _t(
                        'EnrollPage.REQUESTSFOLDERDESCRIPTION',
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
