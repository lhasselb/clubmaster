<?php

class ClubAdmin extends ModelAdmin {

    private static $managed_models = array(
        'ClubMember',
        'ClubMemberType'
    );

    private static $url_segment = 'clubmanager';

    private static $menu_title = 'Clubmanager';

    private static $allowed_actions = array();

    //To override with a more specific importer implementation,
    //use the ModelAdmin::$model_importers static.
    private static $model_importers = array("ClubMember" => "ClubMemberCsvBulkLoader");

    private static $menu_icon = 'clubmaster/images/clubmaster.png';

    public $showImportForm = array('ClubMember');

    public function getSearchContext() {
        $context = parent::getSearchContext();

        if($this->modelClass == 'ClubMember')
        {
            $rangeDropDownField = DropdownField::create('q[AgeRange]', _t("ClubAdmin.AGERANGE","AgeRange"),
                array(
                    'A' => _t("ClubAdmin.LESSTHAN18","LessThan 18"),
                    'B' => _t("ClubAdmin.MOREEQUAL18","GreaterThanOrEqual 18")
                )
            )->setEmptyString( _t("ClubAdmin.SELECTONE","Select one") );
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
        /*
        $it = $list->getIterator();
        while ($it->valid()) {
            $member = $it->current();
            SS_Log::log("Before key=".$it->key()." lastname=".$member->LastName,SS_Log::WARN);
            $it->next();
        }*/

        $params = $this->request->requestVar('q'); // should be Array defined above
        //SS_Log::log("params=".$params,SS_Log::WARN);
        if($this->modelClass == 'ClubMember' && isset($params['AgeRange']) && $params['AgeRange'] ) {

            if($params['AgeRange'] == "A")
            {
            SS_Log::log("params=".$params['AgeRange'],SS_Log::WARN);
                //Attention: EXCLUDE
                $list = $list->exclude("Age:GreaterThanOrEqual","18");
            }
            elseif($params['AgeRange'] == "B")
            {
            SS_Log::log("params=".$params['AgeRange'],SS_Log::WARN);
                //Attention: EXCLUDE
                $list = $list->exclude("Age:LessThan","18");
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
        // $gridFieldName is generated from the ModelClass, eg if the Class 'ClubMember'
        // is managed by this ModelAdmin, the GridField for it will also be named 'ClubMember'
        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($gridFieldName);

        // Get gridfield config
        $config = $gridField->getConfig();

        if($gridFieldName =="ClubMember")
        {
            // Add GridFieldBulkManager
            $config->addComponent(new GridFieldBulkManager());
            // Set editable fields
            //$config->getComponentByType('GridFieldBulkManager')->setConfig("editableFields", "Active");
            // Add action
            $config->getComponentByType('GridFieldBulkManager')->addBulkAction('activateMember',
                _t("ClubAdmin.GRIDFIELDBULKDROPDOWNACTIVATE","Activate"), 'GridFieldBulkActionActivateMemberHandler');
            // Remove action
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('unLink');
            $config->getComponentByType('GridFieldBulkManager')->removeBulkAction('bulkEdit');
            $config->addComponent(new GridFieldActivateClubMemberAction());

            $printButton = $gridField->getConfig()->getComponentByType("GridFieldPrintButton");
            $printButton->setPrintColumns(
                array(
                'Salutation',
                'FirstName',
                'LastName',
                //'Birthday',
                //'Nationality',
                'Street',
                'Streetnumber',
                'Zip',
                'City',
                //'Email',
                //'Mobil',
                //'Phone',
                //'Type',
                'Since',
                //'AccountHolderFirstName',
                //'AccountHolderLastName',
                //'AccountHolderStreet',
                //'AccountHolderStreetnumber',
                //'AccountHolderZip',
                //'AccountHolderCity',
                //'Iban',
                //'Bic',
                //'Active',
                'Age'
                )
            );
        }
        if($gridFieldName =="ClubMemberType")
        {
            $config->removeComponentsByType("GridFieldPrintButton");
            $config->removeComponentsByType("GridFieldExportButton");
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

}
