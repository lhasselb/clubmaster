<?php

class ClubAdmin extends ModelAdmin {

    private static $managed_models = array(
        //'ClubMemberPending' => array('title' => _t('ClubAdmin.CLUBMEMBERSPENDING','ClubMembersPending')),
        'ClubMemberPending' => array('title' => 'Anträge'),
        'ClubMember' => array('title' => 'Mitglieder'),
        'ClubMemberType' => array('title' => 'Mitgliedstypen')
    );

    private static $url_segment = 'clubmanager';
    private static $menu_icon = 'clubmaster/images/clubmaster.png';
    private static $menu_title = 'Clubmanager';
    // Specific importer implementation
    private static $model_importers = array('ClubMember' => 'ClubMemberCsvBulkLoader');
    public $showImportForm = array('ClubMember');
    //private static $url_rule = '/$Action';
    private static $allowed_actions = array('approvemember','activatemember','deactivatemember');

    /**
     * [getSearchContext description]
     * @return [type] [description]
     */
    public function getSearchContext() {
        $context = parent::getSearchContext();

        if($this->modelClass == 'ClubMember')
        {
            $rangeDropDownField = DropdownField::create('q[AgeRange]', _t('ClubAdmin.AGERANGE','AgeRange'),
                array(
                    'A' => _t('ClubAdmin.LESSTHAN18','LessThan 18'),
                    'B' => _t('ClubAdmin.MOREEQUAL18','GreaterThanOrEqual 18')
                )
            )->setEmptyString( _t('ClubAdmin.SELECTONE','Select one') );
            $context->getFields()->push($rangeDropDownField);
        }

        return $context;
    }

    /**
     * Get a result list
     * The results list are retrieved from SearchContext::getResults(), based on the parameters passed through the search
     * form. If no search parameters are given, the results will show every record. Results are a DataList instance, so can
     * be customized by additional SQL filters, joins.
     * @return [type] [description]
     */
    public function getList() {
        $list = parent::getList();

        // Show valid members
        if($this->modelClass == 'ClubMember')
        {
            $list = $list->filter('Active','1');//array('Active'=>'1','Pending'=>'0')
        }
        // Show pending members
        elseif($this->modelClass == 'ClubMemberPending')
        {
            $list = $list->filter('Pending','1');
        }

        /*
        $it = $list->getIterator();
        while ($it->valid()) {
            $member = $it->current();
            SS_Log::log('Before key='.$it->key().' lastname='.$member->LastName,SS_Log::WARN);
            $it->next();
        }*/

        $params = $this->request->requestVar('q'); // should be Array defined above
        //SS_Log::log('params='.$params,SS_Log::WARN);
        if($this->modelClass == 'ClubMember' && isset($params['AgeRange']) && $params['AgeRange'] ) {

            if($params['AgeRange'] == 'A')
            {
            //SS_Log::log('params='.$params['AgeRange'],SS_Log::WARN);
                //Attention: EXCLUDE
                $list = $list->exclude('Age:GreaterThanOrEqual','18');
            }
            elseif($params['AgeRange'] == 'B')
            {
            //SS_Log::log('params='.$params['AgeRange'],SS_Log::WARN);
                //Attention: EXCLUDE
                $list = $list->exclude('Age:LessThan','18');
            }
        }
        return $list;
    }

    /**
     * Alter look & feel for EditForm
     * To alter how the results are displayed (via GridField), you can also overload the getEditForm() method.
     * For example, to add or remove a new component.
     * @param  [type] $id     [description]
     * @param  [type] $fields [description]
     * @return [type]         [description]
     */
    public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);
        //$main = $fields->fieldByName('Root')->fieldByName('Main')->setTitle('TEST');


        // $gridFieldName is generated from the ModelClass, eg if the Class 'ClubMember'
        // is managed by this ModelAdmin, the GridField for it will also be named 'ClubMember'
        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($gridFieldName);

        //SS_Log::log('gridFieldName='.$gridFieldName,SS_Log::WARN);

        // Get gridfield config
        $config = $gridField->getConfig();

        if($gridFieldName =='ClubMember')
        {
            //$config->removeComponentsByType('GridFieldExportButton');
            // Add GridFieldBulkManager
            $config->addComponent(new GridFieldBulkManager());
            // Set editable fields
            //$config->getComponentByType('GridFieldBulkManager')->setConfig('editableFields', 'Active');
            // Add action
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('activateMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNACTIVATE','Activate'), 'GridFieldBulkActionActivateMemberHandler');
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('deactivateMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNDEACTIVATE','Deactivate'), 'GridFieldBulkActionActivateMemberHandler');
            // Remove action
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('unLink');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('bulkEdit');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('delete');
            $config->addComponent(new GridFieldActivateClubMemberAction());

            $printButton = $config->getComponentByType('GridFieldPrintButton');
            //SS_Log::log('printButton='.$printButton->getTitle($gridField),SS_Log::WARN);
            $printButton->setPrintColumns(
                array(
                    'Salutation' => _t('ClubMember.SALUTATION', 'Salutation'),
                    'FirstName'  => _t('ClubMember.FIRSTNAME', 'FirstName'),
                    'LastName'   => _t('ClubMember.LASTNAME', 'LastName'),
                    //'Birthday' => _t('ClubMember.Birthday', 'Birthday'),
                    //'Nationality'  => _t('ClubMember.Nationality', 'Nationality'),
                    'Street'  => _t('ClubMember.STREET', 'Street'),
                    'Streetnumber'  => _t('ClubMember.STREETNUMBER', 'Streetnumber'),
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
                    //'AccountHolderStreetnumber'  => _t('ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetnumber'),
                    //'AccountHolderZip'  => _t('ClubMember.AccountHolderZip', 'AccountHolderZip'),
                    //'AccountHolderCity'  => _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity'),
                    //'Iban'  => _t('ClubMember.IBAN', 'Iban'),
                    //'Bic'  => _t('ClubMember.BIC', 'Bic'),
                    //'Active'  => _t('ClubMember.ACTIVE', 'Active'),
                    'Age'  => _t('ClubMember.AGE', 'Age')
                )
            );
        }
        elseif($gridFieldName =='ClubMemberType')
        {
            $config->removeComponentsByType('GridFieldPrintButton');
            $config->removeComponentsByType('GridFieldExportButton');
        }
        elseif($gridFieldName =='ClubMemberPending')
        {
            $columns = $gridField->getColumns();
            foreach ($columns as $column) {
                //SS_Log::log('column='.$column,SS_Log::WARN);
            }

            $config->removeComponentsByType('GridFieldPrintButton');
            $config->removeComponentsByType('GridFieldExportButton');
            $config->removeComponentsByType('GridFieldAddNewButton');
            $config->removeComponentsByType('GridFieldFilterHeader');
            $config->removeComponentsByType('GridFieldDeleteAction');
            // Add GridFieldBulkManager
            $config->addComponent(new GridFieldBulkManager());
            // Add action
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('activateMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNAPPROVE','Activate'), 'GridFieldBulkActionActivateMemberHandler');
            // Remove action
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('unLink');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('bulkEdit');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('delete');
            //$config->addComponent(new GridFieldActivateClubMemberAction());
            $config->addComponent(new GridFieldApproveClubMemberAction());

        }
        // modify the list view.
//        $gridField->getConfig()->addComponent(new GridFieldFilterHeader());
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
            //'Age'
            //'Sex'
            //'SerializedFileName'
            //'CreationType'
            //'Pending'
        );
    }

    function init()
    {
        parent::init();

        $folder = Folder::find_or_make('antraege');
        $folder->syncChildren();
        $files = DataObject::get('File', "ParentID = '{$folder->ID}'");

        $clubMembers = ClubMember::get();
        $clubMembersCount = $clubMembers->count();
        SS_Log::log('count='.$clubMembers->count(),SS_Log::WARN);
        foreach ($files as $file) {
            $file_parts = pathinfo($file->Title);

            if(!isset($file_parts['extension']) || $file_parts['extension']!== 'antrag')
            {
                //SS_Log::log('current file extension='.$file->Title,SS_Log::WARN);
                continue;
            }
            //SS_Log::log('current file title='.$file->Title,SS_Log::WARN);
            $existingClubMember = null;
            // Do we have pending members?
            if($clubMembers->count() > 0)
            {
                // Find an existing member for the current file
                $existingClubMember = $clubMembers->find('SerializedFileName',$file->Title);
                SS_Log::log('pendingClubMember exists ID ='.$existingClubMember->ID,SS_Log::WARN);
            }
            // No member found
            if(!$this->pendingExists($existingClubMember))
            {
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

    // Function for basic field validation (present and neither empty nor only white space
    function pendingExists($member){
        return (isset($member) || trim($member)!=='');
    }
}
