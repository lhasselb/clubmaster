<?php

class ClubMember extends DataObject
{
    private static $db = array(
        //Form-Fields
        'Salutation' => 'Enum("Frau,Herr,Schülerin,Schüler","Frau")',
        'FirstName' => 'Varchar(255)',
        'LastName' => 'Varchar(255)',
        'Birthday' => 'Date',
        'Nationality' => 'Varchar(255)', //CountryDropdownField
        'Street' => 'Varchar(255)',
        'StreetNumber' => 'Varchar(255)', // Nummer 34B?
        'Zip' => 'Int(5)',
        'City' => 'Varchar(255)',
        'Email' => 'Varchar(254)',// See RFC 5321, Section 4.5.3.1.3. (256 minus the < and > character)
        'Mobil' => 'Varchar(255)',
        'Phone' => 'Varchar(255)',
        'Since' => 'Date',
        'EqualAddress' => 'Boolean(0)',
        'AccountHolderFirstName' => 'Varchar(16)',
        'AccountHolderLastName' => 'Varchar(16)',
        'AccountHolderStreet' => 'Varchar(255)',
        'AccountHolderStreetNumber' => 'Varchar(10)', // Nummer 34B?
        'AccountHolderZip' => 'Int(5)',
        'AccountHolderCity' => 'Varchar(255)',
        'Iban' => 'Varchar(34)',
        'Bic' => 'Varchar(11)', //ISO_9362
        // Special Meaning
        'Active' => 'Boolean(1)', //Hide from print/export
        'Insurance' => 'Boolean',
        'Age' => 'Int', // Calculated
        'Sex' => 'Enum("w,m","w")', // Calculated
        'SerializedFileName' => 'Varchar(255)', // File created by Webform
        'CreationType' => 'Enum("Formular,Import,Händisch")', // Distinguish Formular,Import,Händisch
        'Pending' => 'Boolean(0)'
    );

    private static $has_one = array('Type' => 'ClubMemberType');

    private static $defaults = array(
        'CreationType' => 'Händisch',
        'Active' => '1'
    );

    private static $summary_fields = array(
        'Salutation',
        'FirstName',
        'LastName',
        'Age'
    );

    public function populateDefaults() {
        $this->Since = date('d.m.Y');
        SS_Log::log('ClubMember populateDefaults - Since='.$this->Since,SS_Log::WARN);
        parent::populateDefaults();
    }

    private static $searchable_fields = array('FirstName','LastName','Age');

    function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['Salutation'] = _t('ClubMember.SALUTATION', 'Salutation');
        $labels['FirstName'] = _t('ClubMember.FIRSTNAME', 'FirstName');
        $labels['LastName'] = _t('ClubMember.LASTNAME', 'LastName');
        $labels['Birthday'] = _t('ClubMember.BIRTHDAY', 'Birthday');
        $labels['Nationality'] = _t('ClubMember.NATIONALITY', 'Nationality');
        $labels['Street'] = _t('ClubMember.STREET', 'Street');
        $labels['StreetNumber'] = _t('ClubMember.STREETNUMBER', 'StreetNumber');
        $labels['Zip'] = _t('ClubMember.ZIP', 'Zip');
        $labels['City'] = _t('ClubMember.CITY', 'City');
        $labels['Email'] = _t('ClubMember.EMAIL', 'Email');
        $labels['Mobil'] = _t('ClubMember.MOBIL', 'Mobil');
        $labels['Phone'] = _t('ClubMember.PHONE', 'Phone');
        $labels['Type'] = _t('ClubMember.TYPE', 'Type');
        $labels['Since'] = _t('ClubMember.SINCE', 'Since');
        $labels['EqualAddress'] = _t('ClubMember.EQUALADDRESS', 'EqualAddress');
        $labels['AccountHolderFirstName'] = _t('ClubMember.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName');
        $labels['AccountHolderLastName'] = _t('ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName');
        $labels['AccountHolderStreet'] = _t('ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet');
        $labels['AccountHolderStreetNumber'] = _t('ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetNumber');
        $labels['AccountHolderZip'] = _t('ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip');
        $labels['AccountHolderCity'] = _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity');
        $labels['Iban'] = _t('ClubMember.IBAN', 'Iban');
        $labels['Bic'] = _t('ClubMember.BIC', 'Bic');
        //Special
        $labels['Active'] = _t('ClubMember.ACTIVE', 'Active');
        $labels['Insurance'] = _t('ClubMember.INSURANCE', 'Insurance');
        $labels['Age'] = _t('ClubMember.AGE', 'Age');
        $labels['Sex'] = _t('ClubMember.SEX', 'Sex');
        $labels['SerializedFileName'] = _t('ClubMember.SERIALIZEDFILENAME', 'SerializedFileName');
        $labels['FormClaimDate'] = _t('ClubMember.FORMCLAIMDATE', 'FormClaimDate');
        $labels['CreationType'] = _t('ClubMember.CREATIONTYPE', 'CreationType');
        $labels['Pending'] = _t('ClubMember.PENDING', 'Pending');
        return $labels;
    }

    public function getCMSValidator() {
        return new RequiredFields(array(
        'Salutation',
        'FirstName',
        'LastName',
        'Birthday',
        'Nationality',
        'Street',
        'StreetNumber',
        'Zip',
        'City',
        'Email',
        'Mobil',
        'Phone',
        'Since',
        'EqualAddress',
        'AccountHolderFirstName',
        'AccountHolderLastName',
        'AccountHolderStreet',
        'AccountHolderStreetNumber',
        'AccountHolderZip',
        'AccountHolderCity',
        'Iban',
        'Bic'
        ));
    }

    function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $main = $fields->findOrMakeTab('Root.Main')->setTitle(_t('ClubMember.MAINTITLE','Main'));
        $fields->addFieldToTab('Root', new Tab('Meta', _t('ClubMember.META', 'Meta')));

        // Add some editing features (show/hide account related address data)
        Requirements::javascript(CLUBMASTER_DIR . "/javascript/ClubAdmin.js");

        $fields->addFieldToTab('Root.Main',
            DropdownField::create('Salutation', _t('ClubMember.SALUTATION', 'Salutation'),singleton('ClubMember')->dbObject('Salutation')->enumValues()));
        $fields->addFieldToTab('Root.Main',
            TextField::create('FirstName', _t('ClubMember.FIRSTNAME', 'FirstName'))->setAttribute('autofocus','autofocus'));
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
            ZipField::create('Zip', _t('ClubMember.ZIP', 'Zip')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('City', _t('ClubMember.CITY', 'City')));
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
            CheckboxField::create('EqualAddress', _t('ClubMember.EQUALADDRESS', 'EqualAddress')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderFirstName', _t('ClubMember.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderLastName', _t('ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderStreet', _t('ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderStreetNumber', _t('ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetNumber')));
        $fields->addFieldToTab('Root.Main',
            ZipField::create('AccountHolderZip', _t('ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderCity', _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity')));
        $fields->addFieldToTab('Root.Main',
            IbanField::create('Iban', _t('ClubMember.IBAN', 'Iban'))->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            BicField::create('Bic', _t('ClubMember.BIC', 'Bic'))->addExtraClass('text') );
        //Special
        $fields->addFieldToTab('Root.Meta',
            CheckboxField::create('Active', _t('ClubMember.ACTIVE', 'Active')));
        $fields->addFieldToTab('Root.Meta',
            CheckboxField::create('Insurance', _t('ClubMember.INSURANCE', 'Insurance')));
        $fields->addFieldToTab('Root.Meta',
            NumericField::create('Age', _t('ClubMember.AGE', 'Age'))->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Meta',
            TextField::create('Sex', _t('ClubMember.SEX', 'Sex'))->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Meta',
            TextField::create('SerializedFileName', _t('ClubMember.SERIALIZEDFILENAME', 'SerializedFileName'))->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Meta',
            DateField::create('FormClaimDate', _t('ClubMember.FORMCLAIMDATE', 'FormClaimDate'))->setConfig('dateformat', 'dd.MM.yyyy')->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Meta',
            TextField::create('CreationType', _t('ClubMember.CREATIONTYPE', 'CreationType'))->performReadonlyTransformation());
        //$fields->addFieldToTab('Root.Main',
        //    CheckboxField::create('Pending', _t('ClubMember.PENDING', 'Pending'))->performReadonlyTransformation());
        //Remove the fields obsolete for ClubMember (added all for ClubMmeberPending)
        $fields->removeByName('Pending');
        if($this->CreationType !== 'Formular')
        {
            $fields->removeByName(array('SerializedFileName','FormClaimDate'));
        }
        return $fields;
    }

    public function getTitle()
    {
        return $this->FirstName.' '.$this->LastName;
    }

    public function getFormClaimDate() {
        $date = $this->dateFromFilename($this->SerializedFileName);
        return $date->FormatI18N('%d.%m.%Y %H:%M:%S');
    }

    public function getAge()
    {
        if(!$this->dbObject('Birthday')->value) return 0;
        $time = SS_Datetime::now()->Format('U');
        $ago = abs($time - strtotime($this->dbObject('Birthday')->value));
        return  round($ago/86400/365);
    }

    public function getSex()
    {
        return  ($this->Salutation == 'Frau' || $this->Salutation == 'Schülerin')? 'w' : 'm';
    }

    public function isActive()
    {
        return $this->Active;
    }

    public function dateFromFilename($filename)
    {
        $date = new SS_DateTime();
        // XX_dd.mm.yyyy_hh_mm_ss.antrag
        if (preg_match('/^[A-Z]{2}_\d{2}.\d{2}.\d{4}_(\d{2})\.(\d{2})\.(\d{4})_(\d{2})_(\d{2})_(\d{2}).antrag$/', $filename, $matches)) {
            $day   = intval($matches[1]);
            $month = intval($matches[2]);
            $year  = intval($matches[3]);
            $hour  = intval($matches[4]);
            $minute  = intval($matches[5]);
            $second  = intval($matches[6]);
            $date->setValue($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second);
            //SS_Log::log('date='.$date->format('d.m.Y H:i:s'),SS_Log::WARN);
        }
        return $date;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->Age = $this->getAge();
        SS_Log::log('Sex='.$this->getSex(),SS_Log::WARN);
        $this->Sex = $this->getSex();
        if($this->CreationType == 'Händisch')
        $this->Active = true;
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
