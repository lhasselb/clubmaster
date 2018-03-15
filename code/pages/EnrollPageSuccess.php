<?php
class EnrollPageSuccess extends Page
{
    private static $singular_name = 'Erfolgreicher Mitgliedsantrag';
    private static $description = 'Seite für erfolgreichen Mitgliedsantrag';
    //private static $icon = 'mysite/images/treffen.png';
    private static $can_be_root = false;
    private static $allowed_children = 'none';
    private static $defaults = array (
        'ShowInMenus' => false,
        'ShowInSearch' => false
    );

    private static $db = array();


    public function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);
        return $labels;
    }

    function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->removeFieldFromTab('Root.Main','Content');
        //$fields->addFieldToTab('Root.Main', HtmlEditorField::create('Content','Inhalt', $this->Content, 'cmsNoP'));
        $fields->addFieldToTab('Root.Main', TextAreaField::create('Content','Danke-Meldung', $this->Content),'Metadata');
        return $fields;
    }

    function FormData() {
        if(Session::get('Data'))
        return $list = new ArrayData(Session::get('Data'));
    }

}

class EnrollPageSuccess_Controller extends Page_Controller
{
    private static $allowed_actions = array ();

    public function init() {
        parent::init();
    }//init()

}
