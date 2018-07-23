<?php

namespace SYBEHA\Clubmaster\Pages;

use Page;

use SilverStripe\Forms\TextareaField;
use SilverStripe\Control\Session;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\ArrayList;

use SYBEHA\Clubmaster\Models\ClubMemberType;
use SYBEHA\Clubmaster\Models\ClubMemberPending;

/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

/**
 * Enroll success page template
 * Class EnrollSuccessPage
 * @package SYBEHA\Clubmaster\Pages
 */
class EnrollSuccessPage extends Page
{
    /*
     * Important: Please note: It is strongly recommended to define a table_name for all namespaced models.
     * Not defining a table_name may cause generated table names to be too long
     * and may not be supported by your current database engine.
     * The generated naming scheme will also change when upgrading to SilverStripe 5.0 and potentially break.
     */
    private static $table_name = 'EnrollSuccessPage';

    private static $singular_name = 'Erfolgreicher Mitgliedsantrag';
    private static $description = 'Seite für erfolgreichen Mitgliedsantrag';
    //private static $icon = 'mysite/images/treffen.png';
    private static $can_be_root = false;
    private static $allowed_children = 'none';
    private static $defaults = [
        'ShowInMenus' => false,
        'ShowInSearch' => false
    ];

    private static $db = [];


    public function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);
        return $labels;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeFieldFromTab('Root.Main', 'Content');
        //$fields->addFieldToTab('Root.Main', HtmlEditorField::create('Content','Inhalt', $this->Content, 'cmsNoP'));
        $fields->addFieldToTab(
            'Root.Main',
            //@todo: Add i18n
            TextAreaField::create(
                'Content',
                _t('SYBEHA\Clubmaster\Pages\EnrollSuccessPage.MESSAGE_LABEL','Thank you messag'),
                $this->Content
            ),
            'Metadata'
        );
        return $fields;
    }

    /**
     * Get form data
     *
     * @return ArrayData
     */
    public function FormData()
    {
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        //if ($session->get('Data')) {
        if($session->get('ClubMemberPending'))
        {
            //$list = new ArrayData($session->get('Data'));
            $list = new ArrayData();
            $serialized = $session->get('ClubMemberPending');
            $pendingMember = unserialize(base64_decode($serialized));
            $list = $pendingMember->data();
            // We need to replace the String TypeID from the form with a database entry for the appropriate TypeID
            Injector::inst()->get(LoggerInterface::class)
                ->debug('EnrolSuccessPage - FormData()  class = ' . get_class($pendingMember));
            Injector::inst()->get(LoggerInterface::class)
                //->debug('EnrolSuccessPage - FormData()  id = ' . $list->getField('TypeID'));
                ->debug('EnrolSuccessPage - FormData()  id = ' . $pendingMember->TypeID);
            
            //$typeName = ClubMemberType::get()->byID($list->getField('TypeID'))->TypeName;
            $typeName = ClubMemberType::get()->byID($pendingMember->TypeID)->TypeName;
            // Initially there are no ClubMemberType's - @todo: Ignore

            Injector::inst()->get(LoggerInterface::class)
                ->debug('EnrolSuccessPage - FormData()  type = ' . $typeName);

            $list->setField('TypeName',$typeName);
            return $list;

        } else new ArrayData();
    }

    /**
     * Utility to dump session data
     *
     * @param SilverStripe\Control\Session $session
     * @return void
     */
    private static function dumpSession($session) {
        $checkAll = $session->getAll();
        foreach($checkAll as $key => $value) {
            if (is_string($value)) {
                Injector::inst()->get(LoggerInterface::class)
                ->debug('EnrolSuccessPage - FormData()  session key = ' . $key . ' value = ' . $value);
            } elseif (is_array($value)) {
                Injector::inst()->get(LoggerInterface::class)
                    ->debug('EnrolSuccessPage - FormData()  session key = ' . $key . ' ----- array data: ');
                foreach($value as $key => $value) {
                    if (is_string($value)) {
                        Injector::inst()->get(LoggerInterface::class)
                            ->debug('EnrolSuccessPage - FormData()  ----- session key = ' . $key . ' value = ' . $value);
                    } elseif (is_array($value)) {
                        Injector::inst()->get(LoggerInterface::class)
                            ->debug('EnrolSuccessPage - FormData()  ----- session key = ' . $key . ' array data: ' );
                        foreach($value as $key => $value) {
                            Injector::inst()->get(LoggerInterface::class)
                            ->debug('EnrolSuccessPage - FormData()  ----- ----- session key = ' . $key . ' value = ' . $value);
                        }
                    }
                }
            }
        }
    }
}
