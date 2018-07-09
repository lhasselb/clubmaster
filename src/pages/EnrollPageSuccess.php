<?php

namespace Sybeha\clubmaster;

use Page;
use PageController;

class EnrollPageSuccess extends Page
{
    /*
     * Important: Please note: It is strongly recommended to define a table_name for all namespaced models.
     * Not defining a table_name may cause generated table names to be too long
     * and may not be supported by your current database engine.
     * The generated naming scheme will also change when upgrading to SilverStripe 5.0 and potentially break.
     */
    private static $table_name = 'EnrollPageSuccess';
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

    function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeFieldFromTab('Root.Main', 'Content');
        //$fields->addFieldToTab('Root.Main', HtmlEditorField::create('Content','Inhalt', $this->Content, 'cmsNoP'));
        $fields->addFieldToTab('Root.Main', TextAreaField::create('Content', 'Danke-Meldung', $this->Content), 'Metadata');
        return $fields;
    }

    function FormData()
    {
        if (Session::get('Data')) {
            return $list = new ArrayData(Session::get('Data'));
        }
    }
}

class EnrollPageSuccessController extends PageController
{
    private static $allowed_actions = array ();

    public function init()
    {
        parent::init();
    }//init()
}
