<?php

namespace SYBEHA\Clubmaster\Admins;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
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

use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\View\Requirements;
use SilverStripe\SiteConfig\SiteConfig;
/* Configuration */
use SilverStripe\Core\Config\Config;
/* Permissions */
use SilverStripe\Security\Permission;
use SilverStripe\Security\Member;
/* Locale */
use SilverStripe\i18n\i18n;
/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

use SYBEHA\Clubmaster\Models\ClubMember;
use SYBEHA\Clubmaster\Models\ClubMemberPending;
use SYBEHA\Clubmaster\Models\ClubMemberType;
use SYBEHA\Clubmaster\Forms\Fields\ZipField;
use SYBEHA\Clubmaster\Forms\Gridfield\GridFieldApproveClubMemberAction;
use SYBEHA\Clubmaster\Forms\Gridfield\GridFieldActivateClubMemberAction;
//use SYBEHA\Clubmaster\Forms\Gridfield\ 

use SYBEHA\Clubmaster\Loader\ClubMemberCsvBulkLoader;
use SilverStripe\i18n\i18nEntityProvider;
// TODO: Check for later usage GridFieldAddExistingSearchButton
// use Symbiote\GridFieldExtensions\GridFieldAddExistingSearchButton;

/**
 * ClubMember administration system within the CMS
 * Class ClubAdmin
 * @package SYBEHA\Clubmaster\Admins
 */
class ClubAdmin extends ModelAdmin
{
    private static $menu_title = 'Clubmanager';
    private static $url_segment = 'clubmanager';
    private static $menu_icon = 'clubmaster/images/clubmaster.png';

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
     * @config set a default for items_per_page
     */
    private static $items_per_page = '25';

    /**
     *  Prepare search
     */
    public function getSearchContext()
    {
        $context = parent::getSearchContext();

        if ($this->modelClass === ClubMember::class) {
            // Postleitzahlen
            $zipFieldGroup = FieldGroup::create(
                HeaderField::create('TitleHeader', _t('SYBEHA\Clubmaster\Admins\ClubAdmin.ZIPSEARCH', 'Zip'), 4),
                new ZipField("q[StartPlz]", _t('SYBEHA\Clubmaster\Admins\ClubAdmin.ZIPSTART', 'zipStart')),
                new ZipField("q[EndPlz]", _t('SYBEHA\Clubmaster\Admins\ClubAdmin.ZIPEND', 'zipEnd'))
            );
            // Alter
            $ageRangeDropDownField = DropdownField::create(
                'q[AgeRange]',
                _t(
                    'SYBEHA\Clubmaster\Admins\ClubAdmin.AGERANGE',
                    'AgeRange'
                ),
                array(
                    'U16' => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.LESSTHAN16', 'LessThan 16'),
                    'U26' => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.LESSTHAN26', 'LessThan 26'),
                    'U60' => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.LESSTHAN60', 'LessThan 60'),
                )
            )->setEmptyString(_t('SYBEHA\Clubmaster\Admins\ClubAdmin.SELECTONE', 'Select one'));
            // Active / Inactive
            $showInactiveDropDownField = DropdownField::create(
                'q[State]',
                _t(
                    'SYBEHA\Clubmaster\Admins\ClubAdmin.STATE',
                    'Member state'
                ),
                array(
                    'A' => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWACTIVE', 'Show active'),
                    'I' => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWINACTIVE', 'Show inactive')
                    //'AI' => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWALL','Show all')
                )
            )->setEmptyString(_t('SYBEHA\Clubmaster\Admins\ClubAdmin.SELECTONE', 'Select one'));
            // Versicherung
            $insuranceDropDownField = DropdownField::create(
                'q[Insurance]',
                _t(
                    'SYBEHA\Clubmaster\Admins\ClubAdmin.INSURANCE',
                    'Insurance'
                ),
                array(
                    'UV' => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWNOINSURANCE', 'Non insured'),
                    'V' => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWINSURANCE', 'Insured')
                )
            )->setEmptyString(_t('SYBEHA\Clubmaster\Admins\ClubAdmin.SELECTONE', 'Select one'));
            // Type
            //$typeList = ClubMemberType::get()->map()->toArray();
            $typeDropDownField = DropdownField::create('q[Type]', _t('ClubMember.TYPE', 'Type'))
                ->setSource(ClubMemberType::get()->map()->toArray())
                ->setEmptyString(_t('SYBEHA\Clubmaster\Admins\ClubAdmin.SELECTONE', 'Select one'));

            $context->getFields()->push($ageRangeDropDownField);
            $context->getFields()->push($insuranceDropDownField);
            $context->getFields()->push($typeDropDownField);
            $context->getFields()->push($showInactiveDropDownField);
            $context->getFields()->push($zipFieldGroup);
        }

        return $context;
    }

    /**
     * Get a result list
     * The results list are retrieved from SearchContext::getResults(),
     * based on the parameters passed through the search form.
     * If no search parameters are given, the results will show every record.
     * Results are a DataList instance, so can
     * be customized by additional SQL filters, joins.
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

        // Get parameters
        $params = $this->request->requestVar('q');

        if ($params && $this->modelClass === ClubMember::class) {
            // Limit to active or inactive
            if (isset($params['State']) && $params['State']) {
                //SS_Log::log('State='.$params['State'],SS_Log::WARN);
                if ($params['State'] == 'A') {
                    $list = $list->filter('Active', '1');
                } elseif ($params['State'] =='AI') {
                    //$list
                } elseif ($params['State'] == 'I') {
                    $list = $list->filter('Active', '0');
                }
            }
            // Limit to insurance
            if (isset($params['Insurance']) && $params['Insurance'] == 'V') {
                //SS_Log::log('Insurance='.$params['Insurance'],SS_Log::WARN);
                if ($params['Insurance'] == 'V') {
                    $list = $list->filter('Insurance', '1');
                } elseif ($params['Insurance'] == 'UV') {
                    $list = $list->filter('Insurance', '0');
                }
            }
            // Filter by Zip
            if (isset($params['StartPlz']) && $params['StartPlz']) {
                $list = $list->exclude('Zip:LessThan', $params['StartPlz']);
            }
            if (isset($params['EndPlz']) && $params['EndPlz']) {
                $list = $list->exclude('Zip:GreaterThan', $params['EndPlz']);
            }
            // Filter by Age range
            if (isset($params['AgeRange']) && $params['AgeRange']) {
                if ($params['AgeRange'] == 'U16') {
                    $list = $list->exclude('Age:GreaterThan', '16');
                } elseif ($params['AgeRange'] == 'U26') {
                    $list = $list->exclude('Age:GreaterThan', '26');
                } elseif ($params['AgeRange'] == 'U60') {
                    $list = $list->exclude('Age:GreaterThan', '60');
                }
            }
            // Filter by Type
            if (isset($params['Type']) && $params['Type']) {
                $list = $list->filter('TypeID', $params['Type']);
            }
        } else { /* Nothing */
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

        $form = parent::getEditForm($id, $fields);
        // $gridFieldName is generated from the ModelClass, eg if the Class 'ClubMember'
        // is managed by this ModelAdmin, the GridField for it will also be named 
        // 'ClubMember'  NEW :  SYBEHA-Clubmaster-Models-ClubMember
        // and SYBEHA-Clubmaster-Models-ClubMemberPending
        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        //Injector::inst()->get(LoggerInterface::class)
        //->debug('ClubAdmin - getEditForm() gridFieldName= ' . $gridFieldName);
        $gridField = $form->Fields()->fieldByName($gridFieldName);

        // Get gridfield config
        $config = $gridField->getConfig();

        /*
        Injector::inst()->get(LoggerInterface::class)
        ->debug('ClubAdmin - getEditForm() config = ' . $config->getComponents());
        $components = $config->getComponents();
        foreach ( $components as $key => $value ) {
            Injector::inst()->get(LoggerInterface::class)->debug('key =' . $key . ' value = ' . get_class($value));
        }
            0 = SilverStripe\Forms\GridField\GridFieldButtonRow
            1 = SilverStripe\Forms\GridField\GridFieldAddNewButton
            2 = SilverStripe\Forms\GridField\GridFieldToolbarHeader
            3 = SilverStripe\Forms\GridField\GridFieldSortableHeader
            4 = SilverStripe\Forms\GridField\GridFieldDataColumns
            5 = SilverStripe\Forms\GridField\GridFieldEditButton
            6 = SilverStripe\Forms\GridField\GridFieldDeleteAction
            7 = SilverStripe\Forms\GridField\GridFieldPageCount
            8 = SilverStripe\Forms\GridField\GridFieldPaginator
            9 = SilverStripe\Forms\GridField\GridFieldDetailForm
            10 = SilverStripe\Versioned\VersionedGridFieldState\VersionedGridFieldState
            11 = SilverStripe\Forms\GridField\GridFieldExportButton
            12 = SilverStripe\Forms\GridField\GridFieldPrintButton
            13 = SilverStripe\Forms\GridField\GridState_Component
            14 = SilverStripe\Forms\GridField\GridFieldImportButton
        */

        //if ($gridFieldName == 'ClubMember') {
        if ($gridFieldName === 'SYBEHA-Clubmaster-Models-ClubMember') {
            //$config->addComponent(new GridFieldShowHideButton('before'));
            // Get configuration
            $siteConfig = SiteConfig::current_site_config();
            // Set rows displayed
            $itemsPerPage = Config::inst()->get('ClubAdmin', 'items_per_page'); // default 50, _config/config.yml
            $itemsPerPage = $siteConfig->MembersDisplayed; // set in site config

            $config->getComponentByType(GridFieldPaginator::class)->setItemsPerPage($itemsPerPage);
            //Injector::inst()->get(LoggerInterface::class)->debug('Config: ' . implode(" +  ",$config));

            // Add Filter header
            $config->addComponent(new GridFieldFilterHeader());

            // Check out https://github.com/symbiote/silverstripe-gridfieldextensions
            //$config->addComponent(new GridFieldAddExistingSearchButton());

            // Add GridFieldBulkManager
            $config->addComponent(new \Colymba\BulkManager\BulkManager());
            // Remove bulk actions
            $config->getComponentByType('Colymba\\BulkManager\\BulkManager')
            ->removeBulkAction('Colymba\\BulkManager\\BulkAction\\UnlinkHandler');
            $config->getComponentByType('Colymba\\BulkManager\\BulkManager')
            ->removeBulkAction('Colymba\\BulkManager\\BulkAction\\EditHandler');
            //$config->getComponentByType('Colymba\\BulkManager\\BulkManager')
            //->removeBulkAction('Colymba\\BulkManager\\BulkAction\\DeleteHandler');
            // Remove bulk delete action from non Administrators
            if (!$this->canDeleteClubmember()) {
                $config->getComponentByType('Colymba\\BulkManager\\BulkManager')
                ->removeBulkAction('Colymba\\BulkManager\\BulkAction\\DeleteHandler');
            }

            // Add ACTION activate/deactivateMember
            $config->addComponent(new GridFieldActivateClubMemberAction());
            // Add BULK action activateMember
            $config->getComponentByType('Colymba\\BulkManager\\BulkManager')->addBulkAction(
                'SYBEHA\\clubmaster\\forms\\gridfield\\GridFieldBulkActionActivateMemberHandler'
            );
            // Add BULK action deactivateMember
            $config->getComponentByType('Colymba\\BulkManager\\BulkManager')->addBulkAction(
                'SYBEHA\\clubmaster\\forms\\gridfield\\GridFieldBulkActionDeActivateMemberHandler'
            );

            // Add BULK action insureMember
            $config->getComponentByType('Colymba\\BulkManager\\BulkManager')->addBulkAction(
                'SYBEHA\\clubmaster\\forms\\gridfield\\GridFieldBulkActionInsuranceMemberHandler'
            );

            /* PRINT disabled */
            $config->removeComponentsByType('GridFieldPrintButton');
            /*$printButton = $config->getComponentByType('GridFieldPrintButton');
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
            $config->removeComponentsByType(GridFieldPrintButton::class);
            $config->removeComponentsByType(GridFieldExportButton::class);
            $config->removeComponentsByType(GridFieldImportButton::class);
        //} elseif ($gridFieldName == 'ClubMemberPending') {
        } elseif ($gridFieldName === 'SYBEHA-Clubmaster-Models-ClubMemberPending') {
            /*$columns = $gridField->getColumns();
            foreach ($columns as $column) {
                SS_Log::log('column='.$column,SS_Log::WARN);
            }*/
            $config->removeComponentsByType(GridFieldImportButton::class);
            $config->removeComponentsByType(GridFieldPrintButton::class);
            $config->removeComponentsByType(GridFieldExportButton::class);
            $config->removeComponentsByType(GridFieldAddNewButton::class);
            $config->removeComponentsByType(GridFieldFilterHeader::class);
            $config->removeComponentsByType(GridFieldDeleteAction::class);

            $config->addComponent(new GridFieldApproveClubMemberAction());

            // Add GridFieldBulkManager
            $config->addComponent(new \Colymba\BulkManager\BulkManager());
            // Add action
            $config->getComponentByType('Colymba\BulkManager\BulkManager')->addBulkAction(
                'SYBEHA\\clubmaster\\forms\\gridfield\\GridFieldBulkActionApproveMemberHandler'
            );
            // Remove action
            $config->getComponentByType('Colymba\\BulkManager\\BulkManager')
            ->removeBulkAction('Colymba\\BulkManager\\BulkAction\\UnlinkHandler');
            $config->getComponentByType('Colymba\\BulkManager\\BulkManager')
            ->removeBulkAction('Colymba\\BulkManager\\BulkAction\\EditHandler');
            $config->getComponentByType('Colymba\\BulkManager\\BulkManager')
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
        return array(
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
        );
    }

    /**
     * Initialize ClubAdmin
     */
    public function init()
    {
        parent::init();
        // Disabled after moving account data to its own tab
        //Requirements::javascript(CLUBMASTER_DIR . '/javascript/ClubAdmin.js');
        Requirements::css(CLUBMASTER_DIR . "/css/ClubAdmin.css");

        /*
        $importers = $this->getModelImporters();
        foreach ( $importers as $key => $value ) {
            Injector::inst()->get(LoggerInterface::class)->debug('ClubAdmin - init() - getModelImporters() key =' . $key . ' value = ' . $value);
        }*/
        
        Injector::inst()->get(LoggerInterface::class)->info('ClubAdmin - Init() locale = ' . i18n::get_locale());
        //Injector::inst()->get(LoggerInterface::class)->info('ClubAdmin - Init() class = ' . $this->sanitiseClassName($this->modelClass) );
        // Create Pending members from serialized form data
        //if ($this->sanitiseClassName($this->modelClass) === 'ClubMemberPending') {
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

            $files = File::get()->filter("ParentID", $folder->ID);
            if (!$files->exists()) {
                Injector::inst()->get(LoggerInterface::class)
                ->debug('ClubAdmin - Init() no files found');
            } else {
                Injector::inst()->get(LoggerInterface::class)
                ->debug('ClubAdmin - Init() found ' . $files->count() . ' files');
            }

            // Iterate the files found
            foreach ($files as $file) {
                // In order to ensure that assets are made public you should check the following:
                // $file->isPublished(); $file->exists();canView(); CanViewType; */
                //Injector::inst()->get(LoggerInterface::class)
                //->debug('ClubAdmin - Init() found file ' . $file->Name . ', is published ? ' .
                // $file->isPublished() . ' , exists ? ' . $file->exists() . ', can view ? ' .
                // $file->canView() . ' and can view type ? ' . $file->CanViewType);
                $extension = $file->getExtension();
                //Injector::inst()->get(LoggerInterface::class)
                //->debug('ClubAdmin - Init() found file title = ' . $file->Title .
                //'(' . $file->Filename . ') extension = ' . $extension);
                
                // Skip all files except those with extension antrag
                if (!$extension || $extension !== 'antrag') {
                    //Injector::inst()->get(LoggerInterface::class)
                    //->debug('ClubAdmin - Init() file with wrong extension = ' . $extension . ' title = ' . $file->Name);
                    continue;
                }

                $existingClubMember = null;
                // Do we have alreay members
                if (ClubMember::get()->count() > 0) {
                    // Find an existing member created with current file
                    $existingClubMember = ClubMember::get()->find('SerializedFileName', $file->Name);
                    if ($existingClubMember) {
                        //Injector::inst()->get(LoggerInterface::class)
                        //->debug('ClubAdmin - Init()  found member ' . $existingClubMember->Title .
                        //' (' . $existingClubMember->ID .') for file = ' . $file->Name);
                    }
                }
                // No member found
                if (!$existingClubMember) {
                    Injector::inst()->get(LoggerInterface::class)
                    ->debug('ClubAdmin - Init() no matching member found for file title = ' . $file->Title
                    . ' ,name = ' . $file->Name . ' (' . $file->Filename . ') extension = ' . $extension);
                    //$serialized = file_get_contents($file->getFullPath());
                    $serialized = $file->getString();
                    $data = unserialize(base64_decode($serialized));
                    // Create a new pending member
                    $pendingMember = new ClubMemberPending();
                    Injector::inst()->get(LoggerInterface::class)
                    ->debug('ClubAdmin - Init()  new ClubMemberPending created');

                    $pendingMember->SerializedFileName = $file->Name;
                    //Injector::inst()->get(LoggerInterface::class)
                    //->debug('ClubAdmin - Init()  SerializedFileName = ' . $file->Name);

                    // Attention php DateTime needs to be ISO 8601 formatted date and time (Y-m-d H:i:s)
                    $pendingMember->FormClaimDate = $pendingMember->dateFromFilename($file->Name)
                        ->format('Y-m-d H:i:s');
                    //Injector::inst()->get(LoggerInterface::class)
                    //->debug('ClubAdmin - Init()  create date (FormClaimDate) from = '
                    //. $pendingMember->FormClaimDate );

                    $pendingMember->fillWith($data);
                    //Injector::inst()->get(LoggerInterface::class)
                    //->debug('ClubAdmin - Init()  Birthday = ' . $pendingMember->Birthday );
                    //Injector::inst()->get(LoggerInterface::class)
                    //->debug('ClubAdmin - Init()  Since = ' . $pendingMember->Since );

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
