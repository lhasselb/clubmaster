<?php

class ClubAdmin extends ModelAdmin {

    private static $managed_models = array(
        'ClubMember',
        'ClubMemberType'
    );

    private static $url_segment = 'clubmanager';

    private static $menu_title = 'Clubmanager';

    private static $allowed_actions = array(
    );

    private static $menu_icon = 'clubmaster/images/clubmaster.png';

    public $showImportForm = array('ClubMember');

    public function getSearchContext() {
        $context = parent::getSearchContext();

        if($this->modelClass == 'ClubMember')
        {
            $rangeDropDownField = DropdownField::create('q[AgeRange]','Age Range',
                array(
                    'A' => 'Less than 18',
                    'B' => 'Equal or more than 18 '
                )
            )->setEmptyString( _t("ClubAdmin.SELECTONE","Select one") );
            $context->getFields()->push($rangeDropDownField);
        }

        return $context;
    }

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
                $list = $list->exclude("Age:LessThan","18");
            }
            elseif($params['AgeRange'] == "B")
            {
            SS_Log::log("params=".$params['AgeRange'],SS_Log::WARN);
                $list = $list->exclude("Age:GreaterThanOrEqualFilter","18");
            }
        }
        return $list;
    }

    public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);
        // $gridFieldName is generated from the ModelClass, eg if the Class 'ClubMember'
        // is managed by this ModelAdmin, the GridField for it will also be named 'ClubMember'
        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($gridFieldName);
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

        // modify the list view.
//        $gridField->getConfig()->addComponent(new GridFieldFilterHeader());
        $gridField->getConfig()->addComponent(new GridFieldActivateClubMemberAction());
        return $form;
    }

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
            'Age'
        );
    }
}
