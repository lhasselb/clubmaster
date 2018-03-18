<?php

/**
 * ClubMember administration system within the CMS
 *
 * @package clubmaster
 * @subpackage admins
 */
class ClubAdmin extends ModelAdmin
{

    private static $managed_models = array(
        'ClubMemberPending' => array('title' => 'Anträge'),
        'ClubMember' => array('title' => 'Mitglieder'),
        'ClubMemberType' => array('title' => 'Mitgliedstypen')
    );

    private static $url_segment = 'clubmanager';

    private static $menu_icon = 'clubmaster/images/clubmaster.png';
    private static $menu_title = 'Clubmanager';
    // Specific importer implementation
    private static $model_importers = array('ClubMember' => 'ClubMemberCsvBulkLoader');
    // Show importer for ClubMember only
    public $showImportForm = array('ClubMember');
    //private static $url_rule = '/$Action';
    private static $allowed_actions = array('approvemember', 'activatemember', 'deactivatemember');

    /**
     * @config 
     */
    private static $items_per_page = '25';

    /**
     *  Prepare search
     */
    public function getSearchContext()
    {
        $context = parent::getSearchContext();

        if ($this->modelClass == 'ClubMember') {
            //$infoField = HeaderField::create(_t('ClubAdmin.ZIPSEARCH','Postleitzahlen'), 3);
            // Postleitzahlen
            $zipFieldGroup = FieldGroup::create(
                HeaderField::create(_t('ClubAdmin.ZIPSEARCH', 'Zip'), 4),
                new ZipField("q[StartPlz]", _t('ClubAdmin.ZIPSTART', 'zipStart')),
                new ZipField("q[EndPlz]", _t('ClubAdmin.ZIPEND', 'zipEnd'))
            );
            // Alter
            $ageRangeDropDownField = DropdownField::create('q[AgeRange]', _t('ClubAdmin.AGERANGE', 'AgeRange'),
                array(
                    'U16' => _t('ClubAdmin.LESSTHAN16', 'LessThan 16'),
                    'U26' => _t('ClubAdmin.LESSTHAN26', 'LessThan 26'),
                    'U60' => _t('ClubAdmin.LESSTHAN60', 'LessThan 60'),
                )
            )->setEmptyString(_t('ClubAdmin.SELECTONE', 'Select one'));
            // Active / Inactive
            $showInactiveDropDownField = DropdownField::create('q[State]', _t('ClubAdmin.STATE','Member state'),
                array(
                    'A' => _t('ClubAdmin.SHOWACTIVE','Show active'),
                    'I' => _t('ClubAdmin.SHOWINACTIVE','Show inactive')
                    //'AI' => _t('ClubAdmin.SHOWALL','Show all')
                )
            )->setEmptyString(_t('ClubAdmin.SELECTONE','Select one'));
            // Versicherung
            $insuranceDropDownField = DropdownField::create('q[Insurance]', _t('ClubAdmin.INSURANCE', 'Insurance'),
                array(
                    'UV' => _t('ClubAdmin.SHOWNOINSURANCE', 'Non insured'),
                    'V' => _t('ClubAdmin.SHOWINSURANCE', 'Insured')
                )
            )->setEmptyString(_t('ClubAdmin.SELECTONE', 'Select one'));
            // Type
            //$typeList = ClubMemberType::get()->map()->toArray();
            $typeDropDownField = DropdownField::create('q[Type]', _t('ClubMember.TYPE', 'Type'))
                ->setSource(ClubMemberType::get()->map()->toArray())
                ->setEmptyString(_t('ClubAdmin.SELECTONE', 'Select one'));

            $context->getFields()->push($ageRangeDropDownField);
            $context->getFields()->push($insuranceDropDownField);
            $context->getFields()->push($typeDropDownField);
            $context->getFields()->push($zipFieldGroup);
            $context->getFields()->push($showInactiveDropDownField);
        }

        return $context;
    }

    /**
     * Get a result list
     * The results list are retrieved from SearchContext::getResults(), based on the parameters passed through the search
     * form. If no search parameters are given, the results will show every record. Results are a DataList instance, so can
     * be customized by additional SQL filters, joins.
     */
    public function getList()
    {
        // Get all including inactive
        $list = parent::getList();

        // Limit list to valid members
        if ($this->modelClass == 'ClubMember') {
            $list = $list->filter('Pending', '0');
        } // Limit list to pending members
        elseif ($this->modelClass == 'ClubMemberPending') {
            $list = $list->filter('Pending', '1')->sort('Since','ASC');
        }

        // Get parameters
        $params = $this->request->requestVar('q');

        if ($params && $this->modelClass == 'ClubMember') {

            // Limit to active or inactive
            if (isset($params['State']) && $params['State']) {
		//SS_Log::log('State='.$params['State'],SS_Log::WARN);
		if($params['State'] == 'A') {
                    $list = $list->filter('Active','1');
                } elseif($params['State'] =='AI') {
                    //$list
                } elseif($params['State'] == 'I') {
                    $list = $list->filter('Active','0');
                }
            }
            // Limit to insurance
            if (isset($params['Insurance']) && $params['Insurance'] == 'V') {
		//SS_Log::log('Insurance='.$params['Insurance'],SS_Log::WARN);
		if($params['Insurance'] == 'V') {
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
        // is managed by this ModelAdmin, the GridField for it will also be named 'ClubMember'
        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($gridFieldName);

        // Get gridfield config
        $config = $gridField->getConfig();

        if ($gridFieldName == 'ClubMember') {

            //$config->addComponent(new GridFieldShowHideButton('before'));
			// Get configuration
            $siteConfig = SiteConfig::current_site_config();
            // Set rows displayed
            $itemsPerPage = Config::inst()->get('ClubAdmin', 'items_per_page'); // default 50, _config/config.yml
			$itemsPerPage = $siteConfig->MembersDisplayed; // set in site config

            $config->getComponentByType('GridFieldPaginator')->setItemsPerPage($itemsPerPage);
            // Add GridFieldBulkManager
            $config->addComponent(new GridFieldBulkManager());
            // Add Filter header
            $config->addComponent(new GridFieldFilterHeader());
            // Remove bulk actions
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('unLink');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('bulkEdit');

            // Remove bulk delete action from non Administrators
            if (!$this->canDeleteClubmember()) {
                $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('delete');
            }

            // Add ACTION activate/deactivateMember
            $config->addComponent(new GridFieldActivateClubMemberAction());
            // Add BULK action activateMember
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('activateMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNACTIVATE', 'Activate'), 'GridFieldBulkActionActivateMemberHandler');
            // Add BULK action deactivateMember
            $frontEndConfig = array('isAjax' => true, 'icon' => 'decline', 'isDestructive' => false);
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('deactivateMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNDEACTIVATE', 'Deactivate'), 'GridFieldBulkActionActivateMemberHandler', $frontEndConfig);

            // Add BULK action insureMember
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('insureMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNINSURANCE', 'Insurance'), 'GridFieldBulkActionInsuranceMemberHandler');

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
                    //'AccountHolderStreetNumber'  => _t('ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetNumber'),
                    //'AccountHolderZip'  => _t('ClubMember.AccountHolderZip', 'AccountHolderZip'),
                    //'AccountHolderCity'  => _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity'),
                    //'Iban'  => _t('ClubMember.IBAN', 'Iban'),
                    //'Bic'  => _t('ClubMember.BIC', 'Bic'),
                    //'Active'  => _t('ClubMember.ACTIVE', 'Active'),
                    'Age'  => _t('ClubMember.AGE', 'Age')
                )
            );*/

        } elseif ($gridFieldName == 'ClubMemberType') {
            $config->removeComponentsByType('GridFieldPrintButton');
            $config->removeComponentsByType('GridFieldExportButton');
        } elseif ($gridFieldName == 'ClubMemberPending') {
            /*$columns = $gridField->getColumns();
            foreach ($columns as $column) {
                SS_Log::log('column='.$column,SS_Log::WARN);
            }*/
            $config->removeComponentsByType('GridFieldPrintButton');
            $config->removeComponentsByType('GridFieldExportButton');
            $config->removeComponentsByType('GridFieldAddNewButton');
            $config->removeComponentsByType('GridFieldFilterHeader');
            //$config->removeComponentsByType('GridFieldDeleteAction');

            $config->addComponent(new GridFieldApproveClubMemberAction());			

            // Add GridFieldBulkManager
            $config->addComponent(new GridFieldBulkManager());
            // Add action
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('approveMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNAPPROVE', 'Approve'), 'GridFieldBulkActionApproveMemberHandler');
            // Remove action
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('unLink');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('bulkEdit');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('delete');


        }

        return $form;
    }

    /**
     * Customize exported columns
     * @return Array of fields listed
     */
    public function getExportFields()
    {
        // field => title
        return array(
            'Salutation',
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
            'Active',
            //'Insurance',
            'Age',
            //'Sex',
            //'SerializedFileName',
            //'CreationType',
            //'Pending',
            'MandateReference'
        );
    }

    /**
     * [init description]
     * @return [type] [description]
     */
    public function init()
    {

        parent::init();
        // Disabled after moving account data to its own tab
        //Requirements::javascript(CLUBMASTER_DIR . '/javascript/ClubAdmin.js');
        Requirements::css(CLUBMASTER_DIR . "/css/ClubAdmin.css");

        /* Create Pending members from serialized form data */
        if ($this->sanitiseClassName($this->modelClass) == 'ClubMemberPending') {
            // Get the SiteConfig
            $siteConfig = SiteConfig::current_site_config();
            $folder = $siteConfig->PendingFolder();
            // Check if not configured (FolderID=0)
            if ($folder->ID == '0') {
                // Create a default within assets/antraege
                $folder = Folder::find_or_make('antraege');
                $siteConfig->PendingFolderID = $folder->ID;
                $siteConfig->write();
            }

            // Synchronize files with database
            $folder->syncChildren();
            // Get all files
            $files = DataObject::get('File', "ParentID = '{$folder->ID}'");
            // Iterate the files found
            foreach ($files as $file) {
                $file_parts = pathinfo($file->Title);
                // Skip all files except those with extension antrag
                if (!isset($file_parts['extension']) || $file_parts['extension'] !== 'antrag') {
                    //SS_Log::log('current file extension='.$file->Title,SS_Log::WARN);
                    continue;
                }

                $existingClubMember = null;
                // Do we have alreay members
                if (ClubMember::get()->count() > 0) {
                    // Find an existing member created with current file
					//SS_Log::log('file title='.$file->Title,SS_Log::WARN);
                    $existingClubMember = ClubMember::get()->find('SerializedFileName', $file->Title);
					//SS_Log::log('member?='.$existingClubMember->ID,SS_Log::WARN);
                }
                // No member found
                if (!$existingClubMember) {
					//SS_Log::log('non existing member = ',SS_Log::WARN);
                    $serialized = file_get_contents($file->getFullPath());
                    $data = unserialize(base64_decode($serialized));
                    // Create a new pending member
                    $pendingMember = new ClubMemberPending();
                    $pendingMember->SerializedFileName = $file->Title;
                    $pendingMember->FormClaimDate = $pendingMember->dateFromFilename($file->Title);
                    $pendingMember->fillWith($data);
                    $pendingMember->write();
                }
            }
        }

    }

    // Add a new permission
    public function canDeleteClubmember()
    {
        $member = Member::currentUser();
        return Permission::check('CMS_ACCESS_LeftAndMain', 'any', $member);
    }

}
