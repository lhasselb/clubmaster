<?php

namespace SYBEHA\Clubmaster\Admins;

use SilverStripe\Core\Convert;
use SilverStripe\Dev\BulkLoader;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Search\SearchContext;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Security;
use SilverStripe\View\ArrayData;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\File;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Versioned\VersionedGridFieldState\VersionedGridFieldState;
use SilverStripe\Forms\GridField\GridState_Component;
//NEW: Added with 4.3
use SilverStripe\Forms\GridField\GridFieldLazyLoader;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\ListboxField; //Multiple selections not stored!
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\View\Requirements;
use SilverStripe\SiteConfig\SiteConfig;
/* Configuration */
use SilverStripe\Core\Config\Config;
/* Permissions */
use SilverStripe\Security\Permission;
use SilverStripe\Security\Member;

/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

use SYBEHA\Clubmaster\Models\ClubMember;
use SYBEHA\Clubmaster\Models\ClubMemberPending;
use SYBEHA\Clubmaster\Models\ClubMemberType;
use SYBEHA\Clubmaster\Forms\Fields\ZipField;
use SYBEHA\Clubmaster\Forms\Gridfields\Actions\ApproveClubMember;
use SYBEHA\Clubmaster\Forms\Gridfields\Actions\ActivateClubMember;
use SYBEHA\Clubmaster\Loader\ClubMemberCsvBulkLoader;

/**
 * ClubMember administration system within the CMS
 * Class ClubAdmin
 *
 * @package SYBEHA\Clubmaster
 * @subpackage Admins
 * @author Lars Hasselbach <lars.hasselbach@gmail.com>
 * @since 15.03.2016
 * @copyright 2016 [sybeha]
 * @license see license file in modules root directory
 */
class ClubAdmin extends ModelAdmin
{
    private static $url_segment = 'clubmanager';
    //private static $menu_icon = 'lhasselb/clubmaster:client/images/clubmaster.png';
    // Set within ModelAdmin to font-icon-database
    private static $menu_icon_class = 'modeladmin-icon-clubmanager';
    private static $menu_title = 'Clubmanager';

    private static $managed_models = [
        ClubMemberPending::class,
        ClubMember::class,
        ClubMemberType::class
    ];

    // Specific importer implementation
    private static $model_importers = [ClubMember::class => ClubMemberCsvBulkLoader::class];

    // Show importer for ClubMember only
    public $showImportForm = [ClubMember::class];

    // Declare allowed actions
    private static $allowed_actions = ['approvemember', 'activatemember', 'deactivatemember'];

    /**
     * @config
     * @var int Amount of results to show per page
     */
    private static $page_length = 30;

    /**
     * Override getList() from ModelAdmin {@link \SilverStripe\Admin\ModelAdmin::getList()}
     *
     * @return \SilverStripe\ORM\DataList
     */
    public function getList()
    {
        // Get all including inactive
        $list = parent::getList();

        // Limit list to valid members
        if ($this->modelClass === ClubMember::class) {
            $list = $list->filter('Pending', '0');
        } elseif ($this->modelClass === ClubMemberPending::class) {
            // Limit list to pending members
            $list = $list->filter('Pending', '1')->sort('Since', 'ASC');
        }
        return $list;
    }

    /**
     * Alter look & feel for EditForm
     * To alter how the results are displayed (via GridField),
     * you can also overload the getEditForm() method.
     * For example, to add or remove a new component.
     */
    public function getEditForm($id = null, $fields = null)
    {
        //Injector::inst()->get(LoggerInterface::class)->debug('ClubAdmin - getEditForm()');

        $form = parent::getEditForm($id, $fields);
        // $gridFieldName is generated from the ModelClass, eg if the Class 'ClubMember'
        // is managed by this ModelAdmin, the GridField for it will also be named
        // 'ClubMember'  NEW :  SYBEHA-Clubmaster-Models-ClubMember
        // and SYBEHA-Clubmaster-Models-ClubMemberPending
        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        //Injector::inst()->get(LoggerInterface::class)->debug('ClubAdmin - getEditForm() gridFieldName= ' . $gridFieldName);
        $gridField = $form->Fields()->fieldByName($gridFieldName);

        // Get gridfield config
        $gridFieldConfig = $gridField->getConfig();

        // Get configuration
        $siteConfig = SiteConfig::current_site_config();

        // Set rows displayed
        $itemsPerPage = Config::inst()->get('ClubAdmin', 'page_length'); // default 25, _config/config.yml
        $itemsPerPage = $siteConfig->MembersDisplayed;

        //if ($gridFieldName == 'ClubMember') {
        if ($gridFieldName === 'SYBEHA-Clubmaster-Models-ClubMember') {
            //$gridFieldConfig->addComponent(new SYBEHA\Forms\Gridfields\Actions\ShowHideButton('before'));
            $gridFieldConfig->getComponentByType(GridFieldPaginator::class)->setItemsPerPage($itemsPerPage);
            //Injector::inst()->get(LoggerInterface::class)->debug('Config: ' . implode(" +  ",$gridFieldConfig));

            //NEW: Added with 4.3
            $gridFieldConfig->addComponent(new GridFieldLazyLoader());

            // Add GridFieldBulkManager
            $gridFieldConfig->addComponent(new \Colymba\BulkManager\BulkManager());
            // Remove bulk actions
            $gridFieldConfig->getComponentByType('Colymba\\BulkManager\\BulkManager')
                ->removeBulkAction('Colymba\\BulkManager\\BulkAction\\UnlinkHandler');
            $gridFieldConfig->getComponentByType('Colymba\\BulkManager\\BulkManager')
                ->removeBulkAction('Colymba\\BulkManager\\BulkAction\\EditHandler');
            //$gridFieldConfig->getComponentByType('Colymba\\BulkManager\\BulkManager')
            //->removeBulkAction('Colymba\\BulkManager\\BulkAction\\DeleteHandler');
            // Remove bulk delete action from non Administrators
            if (!$this->canDeleteClubmember()) {
                $gridFieldConfig->getComponentByType('Colymba\\BulkManager\\BulkManager')
                    ->removeBulkAction('Colymba\\BulkManager\\BulkAction\\DeleteHandler');
            }

            // Add action activate/deactivateMember
            $gridFieldConfig->addComponent(new \SYBEHA\Clubmaster\Forms\Gridfields\Actions\ActivateClubMember());

            // Add BULK action activateMember
            $gridFieldConfig->getComponentByType('Colymba\\BulkManager\\BulkManager')->addBulkAction(
                'SYBEHA\\Clubmaster\\Forms\\Gridfields\\Bulkactions\\ActivateMemberHandler'
            );
            // Add BULK action deactivateMember
            $gridFieldConfig->getComponentByType('Colymba\\BulkManager\\BulkManager')->addBulkAction(
                'SYBEHA\\Clubmaster\\Forms\\Gridfields\\Bulkactions\\DeActivateMemberHandler'
            );

            // Add BULK action insureMember
            $gridFieldConfig->getComponentByType('Colymba\\BulkManager\\BulkManager')->addBulkAction(
                'SYBEHA\\Clubmaster\\Forms\\Gridfields\\Bulkactions\\InsuranceMemberHandler'
            );

            /* PRINT disabled */
            $gridFieldConfig->removeComponentsByType(GridFieldPrintButton::class);
            /*$printButton = $gridFieldConfig->getComponentByType('GridFieldPrintButton');
            $printButton->setPrintColumns($print_columns
                array(
                    'Salutation' => _t('ClubMember.SALUTATION', 'Salutation'),
                    'FirstName'  => _t('ClubMember.FIRSTNAME', 'FirstName'),
                    'LastName'   => _t('ClubMember.LASTNAME', 'LastName'),
                    //'Birthday' => _t('ClubMember.Birthday', 'Birthday'),
                    //'Nationality'  => _t('ClubMember.Nationality', 'Nationality'),
                    'Street'  => _t('ClubMember.STREET', 'Street'),
                    'StreetNumber'  => _t('ClubMember.STREETNUMBER', 'StreetNumber'),
                    'Zip'  => _t('ClubMember.ZIP', 'Zip'),
                    'City'  => _t('ClubMember.CITY', 'City'),
                    //'Email'  => _t('ClubMember.EMAIL', 'Email'),
                    //'Mobil'  => _t('ClubMember.MOBIL', 'Mobil'),
                    //'Phone'  => _t('ClubMember.PHONE', 'Phone'),
                    //'Type'  => _t('ClubMember.TYPE', 'Type'),
                    'Since'  => _t('ClubMember.SINCE', 'Since'),
                    //'AccountHolderFirstName'  => _t('ClubMember.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName'),
                    //'AccountHolderLastName'  => _t('ClubMember.AccountHolderLastName', 'AccountHolderLastName'),
                    //'AccountHolderStreet'  => _t('ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet'),
                    //'AccountHolderStreetNumber'  =>
                    //_t('ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetNumber'),
                    //'AccountHolderZip'  => _t('ClubMember.AccountHolderZip', 'AccountHolderZip'),
                    //'AccountHolderCity'  => _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity'),
                    //'Iban'  => _t('ClubMember.IBAN', 'Iban'),
                    //'Bic'  => _t('ClubMember.BIC', 'Bic'),
                    //'Active'  => _t('ClubMember.ACTIVE', 'Active'),
                    'Age'  => _t('ClubMember.AGE', 'Age')
                )
            );*/
            //} elseif ($gridFieldName == 'ClubMemberType') {
        } elseif ($gridFieldName === 'SYBEHA-Clubmaster-Models-ClubMemberType') {
            $gridFieldConfig->removeComponentsByType(GridFieldPrintButton::class);
            $gridFieldConfig->removeComponentsByType(GridFieldExportButton::class);
            $gridFieldConfig->removeComponentsByType(GridFieldImportButton::class);
            $gridFieldConfig->removeComponentsByType(GridFieldFilterHeader::class);
            //} elseif ($gridFieldName == 'ClubMemberPending') {
        } elseif ($gridFieldName === 'SYBEHA-Clubmaster-Models-ClubMemberPending') {
            $gridFieldConfig->removeComponentsByType(GridFieldPrintButton::class);
            $gridFieldConfig->removeComponentsByType(GridFieldExportButton::class);
            $gridFieldConfig->removeComponentsByType(GridFieldImportButton::class);
            $gridFieldConfig->removeComponentsByType(GridFieldFilterHeader::class);
            $gridFieldConfig->removeComponentsByType(GridFieldAddNewButton::class);
            $gridFieldConfig->removeComponentsByType(GridFieldDeleteAction::class);

            // Add ApproveClubMember action
            $gridFieldConfig->addComponent(new \SYBEHA\Clubmaster\Forms\Gridfields\Actions\ApproveClubMember());

            // Add GridFieldBulkManager
            $gridFieldConfig->addComponent(new \Colymba\BulkManager\BulkManager());
            // Add action
            $gridFieldConfig->getComponentByType('Colymba\BulkManager\BulkManager')->addBulkAction(
                'SYBEHA\\clubmaster\\forms\\gridfields\\Bulkactions\\ApproveMemberHandler'
            );
            // Remove action
            $gridFieldConfig->getComponentByType('Colymba\\BulkManager\\BulkManager')
                ->removeBulkAction('Colymba\\BulkManager\\BulkAction\\UnlinkHandler');
            $gridFieldConfig->getComponentByType('Colymba\\BulkManager\\BulkManager')
                ->removeBulkAction('Colymba\\BulkManager\\BulkAction\\EditHandler');
            $gridFieldConfig->getComponentByType('Colymba\\BulkManager\\BulkManager')
                ->removeBulkAction('Colymba\\BulkManager\\BulkAction\\DeleteHandler');
        }
        return $form;
    }

    /**
     * Customize exported columns
     *
     * @return Array of fields listed
     */
    public function getExportFields()
    {
        // field => title
        return [
            'Salutation',
            'NameTitle',
            'FirstName',
            'LastName',
            'CareOf',
            'Birthday',
            'Nationality',
            'Street',
            'StreetNumber',
            'Zip',
            'City',
            'Email',
            'Mobil',
            'Phone',
            'ExportType' => 'Type',
            'Since',
            'EqualAddress',
            'AccountHolderFirstName',
            'AccountHolderLastName',
            'AccountHolderStreet',
            'AccountHolderStreetNumber',
            'AccountHolderZip',
            'AccountHolderCity',
            'Iban',
            'Bic',
            //Special
            'Active',
            'Insurance',
            'Age',
            'Sex',
            'SerializedFileName',
            //'FormClaimDate',
            'CreationType' => 'CreationType',
            //'Pending',
            'MandateReference'
        ];
    }

    /**
     * Initialize ClubAdmin ini()
     * Fired on every managed model class (by clicking on model tab)
     */
    public function init()
    {
        parent::init();
        //Requirements::javascript('lhasselb/clubmaster:client/dist/js/main.js');
        //Requirements::css('lhasselb/clubmaster:client/dist/styles/main.css');
        //Requirements::add_i18n_javascript('sybeha/clubmaster:client/lang');

        // Create Pending members from serialized form data
        if ($this->sanitiseClassName($this->modelClass) === 'SYBEHA-Clubmaster-Models-ClubMemberPending') {
            // Get the SiteConfig
            $siteConfig = SiteConfig::current_site_config();
            $folder = $siteConfig->PendingFolder();

            //Injector::inst()->get(LoggerInterface::class)
            //->debug('ClubAdmin - Init() pending folder = ' . $folder->Title . '(ID=' . $folder->ID . ')');
            // Check if not configured (FolderID=0)
            if ($folder->ID == '0') {
                // Create a default within assets/antraege
                $folder = Folder::find_or_make('antraege');
                $siteConfig->PendingFolderID = $folder->ID;
                $siteConfig->write();
            }
            // Check for serialized request forms
            $files = File::get()->filter("ParentID", $folder->ID);
            if (!$files->exists()) {
                Injector::inst()->get(LoggerInterface::class)->info('ClubAdmin - Init() no files found');
            } else {
                Injector::inst()->get(LoggerInterface::class)->info('ClubAdmin - Init() found ' . $files->count() . ' files within ' . $folder->Title);
            }

            // Iterate the files found
            foreach ($files as $file) {
                // In order to ensure that assets are made public you should check the following:
                // $file->isPublished(); $file->exists();canView(); CanViewType; */
                // Injector::inst()->get(LoggerInterface::class)->debug('ClubAdmin - Init() found file ' . $file->Name . ', is published ? ' . $file->isPublished() . ' , exists ? ' . $file->exists() . ', can view ? ' . $file->canView() . ' and can view type ? ' . $file->CanViewType);
                $extension = $file->getExtension();
                //Injector::inst()->get(LoggerInterface::class)->debug('ClubAdmin - Init() found file title = ' . $file->Title . '(' . $file->Filename . ') extension = ' . $extension);
                if(!$file->isPublished() && $extension === 'antrag')
                {
                    $file->publishSingle();
                    $file->write();
                    //Injector::inst()->get(LoggerInterface::class)->debug('ClubAdmin - Init() file title = ' . $file->Filename . ' has been published ');
                }
                // Skip all files except those with extension antrag
                if (!$extension || $extension !== 'antrag') {
                    //Injector::inst()->get(LoggerInterface::class)->debug('ClubAdmin - Init() file with wrong extension = ' . $extension . ' title = ' . $file->Name);
                    continue;
                }

                $existingClubMember = null;
                // Do we have alreay members
                if (ClubMember::get()->count() > 0) {
                    // Find an existing member created with current file
                    $existingClubMember = ClubMember::get()->find('SerializedFileName', $file->Name);
                    if ($existingClubMember) {
                        // Injector::inst()->get(LoggerInterface::class)->debug('ClubAdmin - Init()  found member ' . $existingClubMember->Title . ' (' . $existingClubMember->ID .') for file = ' . $file->Name);
                    }
                }
                // No member found
                if (!$existingClubMember) {
                    Injector::inst()->get(LoggerInterface::class)
                        ->debug(
                            'ClubAdmin - Init()  - No matching member found for file title = ' . $file->Title
                            . ' ,name = ' . $file->Name . ' (' . $file->Filename . ') extension = ' . $extension
                        );

                    // Create an alias for "old" non-namespaced serialized objects
                    if (!class_exists('ClubMemberPending')) {
                        class_alias('SYBEHA\Clubmaster\Models\ClubMemberPending', 'ClubMemberPending');
                    }

                    // Get the serialized object content
                    $serialized = $file->getString();

                    // Create a new pending member
                    $pendingMember = unserialize(base64_decode($serialized));

                    /*
                     *  Attention: Problem with serialzied dataObject
                     *  Changed since 4.2 - Extension for versioning added:
                     *  'SilverStripe\Versioned\VersionedStateExtension' => object(SilverStripe\Versioned\VersionedStateExtension)
                     *  Older object result in "__PHP_Incomplete_Class" on write() :-(
                     */

                    if(!$pendingMember->getExtensionInstance('SilverStripe\Versioned\VersionedStateExtension')) {

                        Injector::inst()->get(LoggerInterface::class)->info('ClubAdmin - Init() - incomplete object found');
                        $incompleteMember = $pendingMember;

                        // Create a new ClubMemberPending
                        $pendingMember = ClubMemberPending::create();
                        Injector::inst()->get(LoggerInterface::class)->info('ClubAdmin - Init() - New ' . get_class($pendingMember) . ' created ');

                        $pendingMember->Since = $incompleteMember->Since;
                        $pendingMember->EqualAddress = $incompleteMember->EqualAddress;
                        $pendingMember->Salutation = $incompleteMember->Salutation;
                        $pendingMember->FirstName = $incompleteMember->FirstName;
                        $pendingMember->LastName = $incompleteMember->LastName;
                        $pendingMember->Birthday = $incompleteMember->Birthday;
                        $pendingMember->Nationality = $incompleteMember->Nationality;
                        $pendingMember->Street = $incompleteMember->Street;
                        $pendingMember->StreetNumber = $incompleteMember->StreetNumber;
                        $pendingMember->Zip = $incompleteMember->Zip;
                        $pendingMember->City = $incompleteMember->City;
                        $pendingMember->Email = $incompleteMember->Email;
                        $pendingMember->Mobil = $incompleteMember->Mobil;
                        $pendingMember->Phone = $incompleteMember->Phone;
                        $pendingMember->TypeID = $incompleteMember->TypeID;
                        $pendingMember->AccountHolderFirstName = $incompleteMember->AccountHolderFirstName;
                        $pendingMember->AccountHolderLastName = $incompleteMember->AccountHolderLastName;
                        $pendingMember->AccountHolderStreet = $incompleteMember->AccountHolderStreet;
                        $pendingMember->AccountHolderStreetNumber = $incompleteMember->AccountHolderStreetNumber;
                        $pendingMember->AccountHolderZip = $incompleteMember->AccountHolderZip;
                        $pendingMember->AccountHolderCity = $incompleteMember->AccountHolderCity;
                        $pendingMember->Iban = $incompleteMember->Iban;
                        $pendingMember->Bic = $incompleteMember->Bic;

                    } else {
                        Injector::inst()->get(LoggerInterface::class)->info('ClubAdmin - Init() - Serialized ' . get_class($pendingMember) . ' created ');
                    }


                    // Created by webform
                    $pendingMember->CreationType = 'Formular';
                    // Required to be displayed
                    $pendingMember->Pending = 1;

                    $pendingMember->SerializedFileName = $file->Name;
                    // Attention php DateTime needs to be ISO 8601 formatted date and time (Y-m-d H:i:s)
                    $pendingMember->FormClaimDate = $pendingMember->dateFromFilename($file->Name)
                        ->format('Y-m-d H:i:s');

                    // Store ClubMemberPending
                    $pendingMember->write();
                }
            }
        }
    }

    /**
     * Add a new permission
     */
    public function canDeleteClubmember()
    {
        $member = Member::currentUser();
        return Permission::check('CMS_ACCESS_LeftAndMain', 'any', $member);
    }
}
