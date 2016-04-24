<?php

/**
 * Enoll page folding the form
 *
 * @package clubmaster
 * @subpackage pages
 *
 */
class EnrollPage extends Page
{
    private static $singular_name = 'Enroll';
    private static $description = 'Enroll page using a form';
    private static $icon = 'pageimages/images/enrollform.png';
    private static $db = array();
    private static $has_one = array();

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        return $fields;
    }
}

class EnrollPage_Controller extends Page_Controller {

    private static $allowed_actions = array(
        'EnrollForm'
    );

    public function EnrollForm() {
        $today = SS_Datetime::now()->FormatI18N("%d.%m.%Y");

        $fields = new FieldList(
            DropdownField::create('Salutation', _t('ClubMember.SALUTATION', 'Salutation'),
                singleton('ClubMember')->dbObject('Salutation')->enumValues())
            ->setAttribute('placeholder', 'Herr'),
            TextField::create('FirstName', _t('ClubMember.FIRSTNAME', 'FirstName'))
            ->setAttribute('placeholder', _t('ClubMember.FIRSTNAME', 'Firstname')),
            TextField::create('LastName', _t('ClubMember.LASTNAME', 'LastName'))
            ->setAttribute('placeholder', _t('ClubMember.LASTNAME', 'Lastname')),
            DateField::create('Birthday', _t('ClubMember.BIRTHDAY', 'Birthday'))
            ->setConfig('showcalendar', true)
            ->setAttribute('placeholder', $today),
/*
            CountryDropdownField::create('Nationality', _t('ClubMember.NATIONALITY', 'Nationality')),
            TextField::create('Street', _t('ClubMember.STREET', 'Street')),
            TextField::create('Streetnumber', _t('ClubMember.STREETNUMBER', 'Streetnumber')),
            NumericField::create('Zip', _t('ClubMember.ZIP', 'Zip')),
            TextField::create('City', _t('ClubMember.CITY', 'City')),
            EmailField::create('Email', _t('ClubMember.EMAIL', 'Email')),
            TextField::create('Mobil', _t('ClubMember.MOBIL', 'Mobil')),//PhoneNumberField
            TextField::create('Phone', _t('ClubMember.PHONE', 'Phone')),//PhoneNumberField
            DropdownField::create('TypeID', _t('ClubMember.TYPE', 'Type'))->setSource(ClubMemberType::get()->map('ID','TypeName')),
            DateField::create('Since', _t('ClubMember.SINCE', 'Since'))->setConfig('showcalendar', true)->setValue(SS_Datetime::now()->FormatI18N('%e %b %Y')),
            TextField::create('AccountHolderFirstName', _t('ClubMember.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName')),
            TextField::create('AccountHolderLastName', _t('ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName')),
            TextField::create('AccountHolderStreet', _t('ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet')),
            TextField::create('AccountHolderStreetnumber', _t('ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetnumber')),
            NumericField::create('AccountHolderZip', _t('ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip')),
            TextField::create('AccountHolderCity', _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity')),
*/
            IbanField::create('Iban', _t('ClubMember.IBAN', 'Iban'))
            ->setAttribute('placeholder', "DE12500105170648489890")->addExtraClass("text"),
            BicField::create('Bic', _t('ClubMember.BIC', 'Bic'))
            ->setAttribute('placeholder', "VOBADEXX")->addExtraClass("text")
        );

        $actions = new FieldList(
            FormAction::create("doEnroll")->setTitle("Enroll")
        );

        $required = new RequiredFields('Salutation','FirstName','LastName','Birthday','Nationality','Street','Streetnumber','Zip','City','Email','Mobil','Phone','TypeID','Since','AccountHolderFirstName','AccountHolderLastName','AccountHolderStreet','AccountHolderStreetnumber','AccountHolderZip','AccountHolderCity','Iban','Bic');

        $form = new Form($this, 'EnrollForm', $fields, $actions, $required);
        $form->setFormMethod('POST', true);

        return $form;
    }

    public function doEnroll($data, Form $form) {
        $form->sessionMessage('Hello '. $data['FirstName'], 'success');

        foreach ($data as $key => $value) {
            //SS_Log::log("key=".$key." value=".$value,SS_Log::WARN);
        }
        // Create a ClubMember object
        $clubMember = new ClubMember();
        // Save data into object
        $form->saveInto($clubMember);
        // Serialize object
        $serialized = serialize($clubMember);
        // Get the desired folder to store the serialized object
        $folder = Folder::find_or_make('antraege');
        // Get the path for the folder and add a filename
        $path = $folder->getFullPath().$data['FirstName'][0].$data['LastName'][0].'_'.date('d.m.Y_H_i_s').'.antrag';
        //SS_Log::log("path=".$path,SS_Log::WARN);
        // Store the object at calculated the path
        file_put_contents($path, $serialized);

        return $this->redirectBack();
    }

    function init()
    {
        parent::init();
        //Add javascript here
        Requirements::javascript("framework/thirdparty/jquery/jquery.js");
        Requirements::javascript("clubmaster/javascript/jquery-validate/jquery.validate.js");
        Requirements::javascript("clubmaster/javascript/jquery-validate/additional-methods.js");
        Requirements::javascript("clubmaster/javascript/jquery-validate/localization/messages_de.js");
        Requirements::customScript('
                jQuery(document).ready(function() {
                    jQuery("#Form_EnrollForm").validate({
                        //lang: "de",
                        rules: {
                            Salutation: {required: true},
                            FirstName: {required: true, minlength: 3},
                            LastName:  {required: true, minlength: 3},
                            Iban: {required: true, iban: true},
                            Bic: {required: true, bic: true},
                        }
                    });
                });
            ');
    }
}
