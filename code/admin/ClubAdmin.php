<?php

class ClubAdmin extends ModelAdmin {

    private static $managed_models = array(
        'ClubMemberPending',
        'ClubMember',
        'ClubMemberType'
    );

    private static $url_segment = 'clubmanager';

    private static $menu_title = 'Clubmanager';

    private static $allowed_actions = array();

    //Override with a more specific importer implementation,
    private static $model_importers = array('ClubMember' => 'ClubMemberCsvBulkLoader');

    private static $menu_icon = 'clubmaster/images/clubmaster.png';

    public $showImportForm = array('ClubMember');

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

        // Filter only active
        if($this->modelClass == 'ClubMember')
        {
            $list = $list->filter('Active','1');
        }
        elseif($this->modelClass == 'ClubMemberPending')
        {
            $list = $list->filter('Active','0');
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
            $config->removeComponentsByType('GridFieldExportButton');
            // Add GridFieldBulkManager
            //$config->addComponent(new GridFieldBulkManager());
            // Set editable fields
            //$config->getComponentByType('GridFieldBulkManager')->setConfig('editableFields', 'Active');
            // Add action
            //$config->getComponentByType('GridFieldBulkManager')->addBulkAction('activateMember',
            //    _t('ClubAdmin.GRIDFIELDBULKDROPDOWNACTIVATE','Activate'), 'GridFieldBulkActionActivateMemberHandler');
            // Remove action
            //$config->getComponentByType('GridFieldBulkManager')->removeBulkAction('unLink');
            //$config->getComponentByType('GridFieldBulkManager')->removeBulkAction('bulkEdit');
            //$config->addComponent(new GridFieldActivateClubMemberAction());

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
            // Add GridFieldBulkManager
            $config->addComponent(new GridFieldBulkManager());
            // Add action
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('activateMember',
                _t('ClubAdmin.GRIDFIELDBULKDROPDOWNACTIVATE','Activate'), 'GridFieldBulkActionActivateMemberHandler');
            // Remove action
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('unLink');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('bulkEdit');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('delete');
            $config->addComponent(new GridFieldActivateClubMemberAction());

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
            //'Active',
            //'Age'
        );
    }

    function init()
    {
        parent::init();

        //SS_Log::log('ClubAdmin init()',SS_Log::WARN);
        //TODO: Make it configurable
        $folder = Folder::find_or_make('antraege');
        $folder->syncChildren();
        $files = DataObject::get('File', "ParentID = '{$folder->ID}'");
        $clubMembers = ClubMember::get();
        foreach ($files as $file) {
            //SS_Log::log('ID='.$file->ID.' type='.$file->ClassName.' Title='.$file->Title,SS_Log::WARN);
            $clubMember = $clubMembers->find('SerializedFileName',$file->Title);
            //if($clubMember) SS_Log::log('found='.$clubMember->ID,SS_Log::WARN);
            $serialized = file_get_contents($file->getFullPath());
            $data = unserialize($serialized);
            $pendingMember = new ClubMemberPending();
            $pendingMember->SerializedFileName = $file->Title;
            $pendingMember->FormClaimDate = $pendingMember->dateFromFilename($file->Title);
            $pendingMember->fillPendingMember($data);
            if(!$clubMember) $pendingMember->write();
        }
    }
/*
    private function dateFromFilename($filename)
    {
        $date = new SS_DateTime();
        // XX_dd.mm.yyyy_hh_mm_ss.antrag
        if (preg_match('/^([A-Z]{2})_(\d{2})\.(\d{2})\.(\d{4})_(\d{2})_(\d{2})_(\d{2}).antrag$/', $filename, $matches)) {
            $day   = intval($matches[2]);
            $month = intval($matches[3]);
            $year  = intval($matches[4]);
            $hour  = intval($matches[5]);
            $minute  = intval($matches[6]);
            $second  = intval($matches[7]);
            $date->setValue($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second);
            //SS_Log::log('date='.$date->format('d.m.Y H:i:s'),SS_Log::WARN);
            return $date;
        } else {
            return false;
        }
    }
*/
}
