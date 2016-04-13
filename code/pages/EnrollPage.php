<?php

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

        $fields = new FieldList(
            TextField::create('Salutation', _t('ClubMember.SALUTATION', 'Salutation')),
            TextField::create('FirstName', _t('ClubMember.FIRSTNAME', 'FirstName')),
            TextField::create('LastName', _t('ClubMember.LASTNAME', 'LastName')),
            DateField::create('Birthday', _t('ClubMember.BIRTHDAY', 'Birthday'))->setConfig('showcalendar', true),
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
            TextField::create('Iban', _t('ClubMember.IBAN', 'Iban')),
            TextField::create('Bic', _t('ClubMember.BIC', 'Bic'))
        );

        $actions = new FieldList(
            FormAction::create("doEnroll")->setTitle("Enroll")
        );

        $required = new RequiredFields('Salutation','FirstName','LastName','Birthday','Nationality','Street','Streetnumber','Zip','City','Email','Mobil','Phone','TypeID','Since','AccountHolderFirstName','AccountHolderLastName','AccountHolderStreet','AccountHolderStreetnumber','AccountHolderZip','AccountHolderCity','Iban','Bic');

        $form = new Form($this, 'EnrollForm', $fields, $actions, $required);

        return $form;
    }

    public function doEnroll($data, Form $form) {
        $form->sessionMessage('Hello '. $data['FirstName'], 'success');

        return $this->redirectBack();
    }
}
