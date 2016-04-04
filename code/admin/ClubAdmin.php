<?php

class ClubAdmin extends ModelAdmin {

    private static $managed_models = array(
        'ClubMember',
        'ClubCategory'
    );

    private static $url_segment = 'clubmanager';

    private static $menu_title = 'Clubmanager';

    private static $allowed_actions = array(
    );

    private static $menu_icon = 'clubmaster/images/clubmaster.png';

    public $showImportForm = array('ClubMember');

    public function getSearchContext() {
        $context = parent::getSearchContext();

        $rangeDropDownField = DropdownField::create('AgeRange','Age Range',array(
            'A' => 'From 0 to 10',
            'B' => 'From 11 to 18',
            'C'=> 'Over 18'
        )
);

        $dateField = new DateField("q[FromDate]", "From Date");
        // Get the DateField portion of the DatetimeField and
        // Explicitly set the desired date format and show a date picker
        $dateField->setConfig('dateformat', 'dd/MM/yyyy')->setConfig('showcalendar', true);
        $context->getFields()->push($dateField);
        $dateField = new DateField("q[ToDate]", "To Date");
        // Get the DateField portion of the DatetimeField and
        // Explicitly set the desired date format and show a date picker
        $dateField->setConfig('dateformat', 'dd/MM/yyyy')->setConfig('showcalendar', true);

        $context->getFields()->push($rangeDropDownField);
        return $context;
    }

    public function getList() {
        $list = parent::getList();
        SS_Log::log("Class=".$this->modelClass,SS_Log::WARN);
        if($this->modelClass == 'ClubMember') {
            //$list = $list->add($age);
        }

        $params = $this->request->requestVar('q'); // use this to access search parameters
        if(isset($params['FromDate']) && $params['FromDate']) {
            $list = $list->exclude('Created:LessThan', $params['FromDate']);
        }
        if(isset($params['ToDate']) && $params['ToDate']) {
            //split UK date into day month year variables
            list($day,$month,$year) = sscanf($params['ToDate'], "%d/%d/%d");
            //date functions expect US date format, create new date object
            $date = new Datetime("$month/$day/$year");
            //create interval of Plus 1 Day (P1D)
            $interval = new DateInterval('P1D');
            //add interval to the date
            $date->add($interval);
            //use the new date value as the GreaterThan exclusion filter
            $list = $list->filter('Created:LessThan', date_format($date, 'd/m/Y'));
        }

        return $list;
    }

    public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);
        // $gridFieldName is generated from the ModelClass, eg if the Class 'ClubMember'
        // is managed by this ModelAdmin, the GridField for it will also be named 'ClubMember'
        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($gridFieldName);
        // modify the list view.
//        $gridField->getConfig()->addComponent(new GridFieldFilterHeader());
        return $form;
    }
}
