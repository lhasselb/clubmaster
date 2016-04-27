<?php

class ClubMemberPending extends ClubMember
{

    private static $defaults = array('CreationType' => 'Formular','Active' => '0');
    private static $summary_fields = array(
        'Salutation',
        'FirstName',
        'LastName',
        'SerializedFileName',
        'FormClaimDate'
    );

    private static $searchable_fields = array();


    function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main',
            DropdownField::create('Salutation', _t('ClubMember.SALUTATION', 'Salutation'),singleton('ClubMember')->dbObject('Salutation')->enumValues()));
        $fields->addFieldToTab('Root.Main',
            TextField::create('FirstName', _t('ClubMember.FIRSTNAME', 'FirstName')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('LastName', _t('ClubMember.LASTNAME', 'LastName')));
        $fields->addFieldToTab('Root.Main',
            DateField::create('Birthday', _t('ClubMember.BIRTHDAY', 'Birthday'))->setConfig('showcalendar', true) );
        $fields->addFieldToTab('Root.Main',
            CountryDropdownField::create('Nationality', _t('ClubMember.NATIONALITY', 'Nationality')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('Street', _t('ClubMember.STREET', 'Street')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('StreetNumber', _t('ClubMember.STREETNUMBER', 'StreetNumber')));
        $fields->addFieldToTab('Root.Main',
            NumericField::create('Zip', _t('ClubMember.ZIP', 'Zip')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('City', _t('ClubMember.CITY', 'City')));
        $fields->addFieldToTab('Root.Main',
            CheckboxField::create('EqualAddress', _t('ClubMember.EQUALADDRESS', 'EqualAddress'))->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Main',
            EmailField::create('Email', _t('ClubMember.EMAIL', 'Email')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('Mobil', _t('ClubMember.MOBIL', 'Mobil')));//PhoneNumberField
        $fields->addFieldToTab('Root.Main',
            TextField::create('Phone', _t('ClubMember.PHONE', 'Phone')));//PhoneNumberField
        $fields->addFieldToTab('Root.Main',
            DropdownField::create('TypeID', _t('ClubMember.TYPE', 'Type'))->setSource(ClubMemberType::get()->map('ID','TypeName')));
        $fields->addFieldToTab('Root.Main',
            DateField::create('Since', _t('ClubMember.SINCE', 'Since'))->setConfig('showcalendar', true) );
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderFirstName', _t('ClubMember.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderLastName', _t('ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderStreet', _t('ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderStreetNumber', _t('ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetNumber')));
        $fields->addFieldToTab('Root.Main',
            NumericField::create('AccountHolderZip', _t('ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderCity', _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity')));
        $fields->addFieldToTab('Root.Main',
            IbanField::create('Iban', _t('ClubMember.IBAN', 'Iban'))->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            BicField::create('Bic', _t('ClubMember.BIC', 'Bic'))->addExtraClass('text') );
        //Special
        //$fields->addFieldToTab('Root.Main',
        //    CheckboxField::create('Active', _t('ClubMember.ACTIVE', 'Active')));
        //$fields->addFieldToTab('Root.Main',
        //    CheckboxField::create('Insurance', _t('ClubMember.INSURANCE', 'Insurance')));
        $fields->addFieldToTab('Root.Main',
            NumericField::create('Age', _t('ClubMember.AGE', 'Age'))->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Main',
            TextField::create('Sex', _t('ClubMember.SEX', 'Sex'))->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Main',
            TextField::create('SerializedFileName', _t('ClubMember.SERIALIZEDFILENAME', 'SerializedFileName'))->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Main',
            DateField::create('FormClaimDate', _t('ClubMember.FORMCLAIMDATE', 'FormClaimDate'))->setConfig('dateformat', 'dd.MM.yyyy')->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Main',
            TextField::create('CreationType', _t('ClubMember.CREATIONTYPE', 'CreationType'))->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Main',
            CheckboxField::create('Pending', _t('ClubMember.PENDING', 'Pending'))->performReadonlyTransformation());
                $fields->removeByName(array('EqualAddress','Active','Insurance'));
        return $fields;
    }


    public function fillWith($data)
    {
        if($data === NULL) return false;
        $this->Salutation = $data->Salutation;
        $this->FirstName = $data->FirstName;
        $this->LastName = $data->LastName;
        $this->Birthday = $data->Birthday;
        $this->Nationality = $data->Nationality;
        $this->Street = $data->Street;
        $this->Streetnumber = $data->Streetnumber;
        $this->Zip = $data->Zip;
        $this->City = $data->City;
        $this->Email = $data->Email;
        $this->Mobil = $data->Mobil;
        $this->Phone = $data->Phone;
        $this->Since = $data->Since;
        $this->AccountHolderFirstName = $data->AccountHolderFirstName;
        $this->AccountHolderLastName = $data->AccountHolderLastName;
        $this->AccountHolderStreet = $data->AccountHolderStreet;
        $this->AccountHolderStreetnumber = $data->AccountHolderStreetnumber;
        $this->AccountHolderZip = $data->AccountHolderZip;
        $this->AccountHolderCity = $data->AccountHolderCity;
        $this->Iban = $data->Iban;
        $this->Bic = $data->Bic;
        $this->AccountHolderZip = $data->AccountHolderZip;
        //Special
        $this->CreationType = 'Formular';
        $this->Pending = 1;
    }

    public function isPending()
    {
        return $this->Pending;
    }

    public function canView($member = null) {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    public function canEdit($member = null) {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    public function canDelete($member = null) {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    public function canCreate($member = null) {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }
}