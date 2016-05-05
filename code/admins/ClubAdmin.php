<?php

class ClubAdmin extends ModelAdmin {

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
    //private static $allowed_actions = array('approvemember','activatemember','deactivatemember','ImportForm');

    /**
     * @config
     */
    private static $items_per_page = '20';

    /**
     *  Prepare search
     */
    public function getSearchContext() {
        $context = parent::getSearchContext();

        if($this->modelClass == 'ClubMember')
        {
            $startNumericField = new ZipField("q[StartPlz]",  _t('ClubAdmin.ZIPSTART','zipStart'));
            $endNumericField = new ZipField("q[EndPlz]", _t('ClubAdmin.ZIPEND','zipEnd'));

            $rangeDropDownField = DropdownField::create('q[AgeRange]', _t('ClubAdmin.AGERANGE','AgeRange'),
                array(
                    'U16' => _t('ClubAdmin.LESSTHAN16','LessThan 16'),
                    'U26' => _t('ClubAdmin.LESSTHAN26','LessThan 26'),
                    'U60' => _t('ClubAdmin.LESSTHAN60','LessThan 60'),
                )
            )->setEmptyString( _t('ClubAdmin.SELECTONE','Select one') );

            $showInactiveDropDownField = DropdownField::create('q[State]', _t('ClubAdmin.STATE','Midgliedsstatus'),
                array(
                    'A' => _t('ClubAdmin.SHOWACTIVE','Zeige Aktive'),
                    'I' => _t('ClubAdmin.SHOWINACTIVE','Zeige Inaktive'),
                    'AI' => _t('ClubAdmin.SHOWALL','Zeige Alle'),
                    'UV' => _t('ClubAdmin.SHOWNOINSURANCE','Zeige ohne Versicherung')
                )
            );//->setEmptyString( _t('ClubAdmin.SELECTONE','Select one') );

            $context->getFields()->push($rangeDropDownField);
            $context->getFields()->push($showInactiveDropDownField);
            $context->getFields()->push($startNumericField);
            $context->getFields()->push($endNumericField);
        }

        return $context;
    }

    /**
     * Get a result list
     * The results list are retrieved from SearchContext::getResults(), based on the parameters passed through the search
     * form. If no search parameters are given, the results will show every record. Results are a DataList instance, so can
     * be customized by additional SQL filters, joins.
     */
    public function getList() {
        // Get all including inactive
        $list = parent::getList();

        // Get parameters
        $params = $this->request->requestVar('q');

        if($params) {
            // Show or hide active / inactive
            if($this->modelClass == 'ClubMember' && isset($params['State']) && $params['State'] ) {
                if($params['State'] == 'A'){
                    $list = $list->filter('Active','1');
                } elseif($params['State'] == 'I'){
                    $list = $list->filter(array('Active'=>'0','Pending'=>'0'));
                } elseif($params['State'] == 'AI') {
                    $list = $list->filter(array('Pending'=>'0'));
                } elseif($params['State'] == 'UV') {
                    $list = $list->filter(array('Insurance'=>'0','Pending'=>'0'));
                }
            } else {
                $list = $list->filter('Active','1');
            }

            // Filter by Zip / PLZ
            if($this->modelClass == 'ClubMember' && isset($params['StartPlz']) && $params['StartPlz']) {
                $list = $list->exclude('Zip:LessThan', $params['StartPlz']);
            }
            if($this->modelClass == 'ClubMember' && isset($params['EndPlz']) && $params['EndPlz']) {
                $list = $list->exclude('Zip:GreaterThan', $params['EndPlz']);
            }

            // Filter by Age / Alter range
            if($this->modelClass == 'ClubMember' && isset($params['AgeRange']) && $params['AgeRange'] ) {
                if($params['AgeRange'] == 'U16'){
                    $list = $list->exclude('Age:GreaterThan','16');
                }
                elseif($params['AgeRange'] == 'U26'){
                    $list = $list->exclude('Age:GreaterThan','26');
                }
                elseif($params['AgeRange'] == 'U60'){
                    $list = $list->exclude('Age:GreaterThan','60');
                }
            }

        } else {

            // Show valid members
            if($this->modelClass == 'ClubMember') {
                $list = $list->filter('Active','1');//array('Active'=>'1','Pending'=>'0')
            }
            // Show pending members
            elseif($this->modelClass == 'ClubMemberPending'){
                $list = $list->filter('Pending','1');
            }

        }

        return $list;
    }

    /**
     * Alter look & feel for EditForm
     * To alter how the results are displayed (via GridField),
     * you can also overload the getEditForm() method.
     * For example, to add or remove a new component.
     */
    public function getEditForm($id = null, $fields = null) {

        $form = parent::getEditForm($id, $fields);

        /*foreach ($fields as $field) {
            SS_Log::log('field='.$field,SS_Log::WARN);
            //SS_Log::log('field='.$key.' value='.$value,SS_Log::WARN);
        }*/
        // $gridFieldName is generated from the ModelClass, eg if the Class 'ClubMember'
        // is managed by this ModelAdmin, the GridField for it will also be named 'ClubMember'
        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($gridFieldName);

        // Get gridfield config
        $config = $gridField->getConfig();

        if($gridFieldName =='ClubMember') {

            // Set rows displayed
            $itemsPerPage = Config::inst()->get('ClubAdmin', 'items_per_page');
            $config->getComponentByType('GridFieldPaginator')->setItemsPerPage($itemsPerPage);
            // Add Filter header
            $config->addComponent(new GridFieldFilterHeader());
            // Add GridFieldBulkManager
            $config->addComponent(new GridFieldBulkManager());
            // Remove bulk actions
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('unLink');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('bulkEdit');

            // Remove bulk delete action from non Administrators
            if(!$this->canDeleteClubmember()) {
                $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('delete');
            }

            // Add ACTION activate/deactivateMember
            $config->addComponent(new GridFieldActivateClubMemberAction());
            // Add BULK action activateMember
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('activateMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNACTIVATE','Activate'), 'GridFieldBulkActionActivateMemberHandler');
            // Add BULK action deactivateMember
            $frontEndConfig = array( 'isAjax' => true, 'icon' => 'decline', 'isDestructive' => false );
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('deactivateMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNDEACTIVATE','Deactivate'), 'GridFieldBulkActionActivateMemberHandler',$frontEndConfig);


            // Add BULK action insureMember
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('insureMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNINSURANCE','Insurance'), 'GridFieldBulkActionInsuranceMemberHandler');

            /* PRINT */
            $printButton = $config->getComponentByType('GridFieldPrintButton');
            $printButton->setPrintColumns(
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
            );

        } elseif($gridFieldName =='ClubMemberType') {
            $config->removeComponentsByType('GridFieldPrintButton');
            $config->removeComponentsByType('GridFieldExportButton');
        } elseif($gridFieldName =='ClubMemberPending') {
            /*$columns = $gridField->getColumns();
            foreach ($columns as $column) {
                SS_Log::log('column='.$column,SS_Log::WARN);
            }*/
            $config->removeComponentsByType('GridFieldPrintButton');
            $config->removeComponentsByType('GridFieldExportButton');
            $config->removeComponentsByType('GridFieldAddNewButton');
            $config->removeComponentsByType('GridFieldFilterHeader');
            //$config->removeComponentsByType('GridFieldDeleteAction');
            // Add GridFieldBulkManager
            $config->addComponent(new GridFieldBulkManager());
            // Add action
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('approveMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNAPPROVE','Approve'), 'GridFieldBulkActionApproveMemberHandler');
            // Remove action
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('unLink');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('bulkEdit');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('delete');
            $config->addComponent(new GridFieldApproveClubMemberAction());

        }

        return $form;
    }

    /**
     * Customize exported columns
     * Export is available as a CSV format through a button at the end of a results list.
     * You can also export search results. This is handled through the GridFieldExportButton component.
     * @return Array of fields listed
     */
    public function getExportFields() {
        // field => title
        return array(
            'Salutation',
            'FirstName',
            'LastName',
            'Birthday',
            'Nationality',
            'Street',
            'Streetnumber',
            'Zip',
            'City',
            'Email',
            'Mobil',
            'Phone',
            'Type',
            'Since',
            'AccountHolderFirstName',
            'AccountHolderLastName',
            'AccountHolderStreet',
            'AccountHolderStreetnumber',
            'AccountHolderZip',
            'AccountHolderCity',
            'Iban',
            'Bic',
            //'Active'
            //'Insurance'
            'Age',
            //'Sex'
            //'SerializedFileName'
            //'CreationType'
            //'Pending'
        );
    }

    /* Disable default import form */
    public function ImportForm() {
        $form = null;
        if (Permission::checkMember(Member::currentUser(), 'CMS_ACCESS_LeftAndMain')) {
                $form = parent::ImportForm();
        }
        return $form;
    }

    public function init() {

        parent::init();

        Requirements::css(CLUBMASTER_DIR . "/css/ClubAdmin.css");

        //$this->sanitiseClassName($this->modelClass) == 'ClubMember' ||
        if($this->sanitiseClassName($this->modelClass) == 'ClubMemberPending') {
            // Get the SiteConfig
            $siteConfig = SiteConfig::current_site_config();
            $folder = $siteConfig->PendingFolder();
            // Check if not configured (FolderID=0)
            if($folder->ID == '0') {
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
                if(!isset($file_parts['extension']) || $file_parts['extension']!== 'antrag') {
                    //SS_Log::log('current file extension='.$file->Title,SS_Log::WARN);
                    continue;
                }

                $existingClubMember = null;
                // Do we have alreay members
                if(ClubMember::get()->count() > 0) {
                    // Find an existing member created with current file
                    $existingClubMember = ClubMember::get()->find('SerializedFileName',$file->Title);
                }
                // No member found
                if(!$existingClubMember) {
                    $serialized = file_get_contents($file->getFullPath());
                    $data = unserialize(base64_decode($serialized));
                    // Create a new pending member
                    $pendingMember = new ClubMemberPending();
                    $pendingMember->SerializedFileName =$file->Title;
                    $pendingMember->FormClaimDate = $pendingMember->dateFromFilename($file->Title);
                    $pendingMember->fillWith($data);
                    $pendingMember->write();
                }
            }
        }

    }

    // Add a new permission
    function canDeleteClubmember() {
        $member = Member::currentUser();
        return Permission::check('CMS_ACCESS_LeftAndMain', 'any', $member);
    }
}
