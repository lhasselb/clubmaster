<?php

class ClubMember extends DataObject
{
    private static $db = array(
        'Salutation' => 'Enum("Frau,Herr,Schülerin,Schüler")',
        'FirstName' => 'Varchar(255)',
        'LastName' => 'Varchar(255)',
        'Birthday' => 'Date',
        'Nationality' => 'Varchar(255)', //CountryDropdownField
        'Street' => 'Varchar(255)',
        'Streetnumber' => 'Varchar(255)', // Nummer 34B?
        'Zip' => 'Int(5)',
        'City' => 'Varchar(255)',
        'Email' => 'Varchar(254)',// See RFC 5321, Section 4.5.3.1.3. (256 minus the < and > character)
        'Mobil' => 'Varchar(255)',
        'Phone' => 'Varchar(255)',
        'Since' => 'Date',
        'AccountHolderFirstName' => 'Varchar(255)',
        'AccountHolderLastName' => 'Varchar(255)',
        'AccountHolderStreet' => 'Varchar(255)',
        'AccountHolderStreetnumber' => 'Varchar(255)', // Nummer 34B?
        'AccountHolderZip' => 'Int(5)',
        'AccountHolderCity' => 'Varchar(255)',
        'Iban' => 'Varchar(255)',
        'Bic' => 'Varchar(255)',
        'Active' => 'Boolean',
        'Age' => 'Int'
    );

    private static $has_one = array(
        'Type' => 'ClubMemberType'
    );

    static $default_country = 'DE';

    // Check for duplicates
    static $duplicateCheck = array();

    private static $summary_fields = array(
        "Salutation",
        "FirstName",
        "LastName",
        "Age",
        "Active"
    );

    private static $searchable_fields = array(
        "FirstName",
        "LastName",
        "Age"
    );

    /**
     * Translate field labels
     * @param  boolean $includerelations [description]
     * @return [type]                    [description]
     */
    function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['Active'] = _t('ClubMember.ACTIVE', 'Active');
        $labels['Salutation'] = _t('ClubMember.SALUTATION', 'Salutation');
        $labels['FirstName'] = _t('ClubMember.FIRSTNAME', 'FirstName');
        $labels['LastName'] = _t('ClubMember.LASTNAME', 'LastName');
        $labels['Birthday'] = _t('ClubMember.BIRTHDAY', 'Birthday');
        $labels['Nationality'] = _t('ClubMember.NATIONALITY', 'Nationality');
        $labels['Street'] = _t('ClubMember.STREET', 'Street');
        $labels['Streetnumber'] = _t('ClubMember.STREETNUMBER', 'Streetnumber');
        $labels['Zip'] = _t('ClubMember.ZIP', 'Zip');
        $labels['City'] = _t('ClubMember.CITY', 'City');
        $labels['Email'] = _t('ClubMember.EMAIL', 'Email');
        $labels['Mobil'] = _t('ClubMember.MOBIL', 'Mobil');
        $labels['Phone'] = _t('ClubMember.PHONE', 'Phone');
        $labels['Type'] = _t('ClubMember.TYPE', 'Type');
        $labels['Since'] = _t('ClubMember.SINCE', 'Since');
        $labels['AccountHolderFirstName'] = _t('ClubMember.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName');
        $labels['AccountHolderLastName'] = _t('ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName');
        $labels['AccountHolderStreet'] = _t('ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet');
        $labels['AccountHolderStreetnumber'] = _t('ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetnumber');
        $labels['AccountHolderZip'] = _t('ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip');
        $labels['AccountHolderCity'] = _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity');
        $labels['Iban'] = _t('ClubMember.IBAN', 'Iban');
        $labels['Bic'] = _t('ClubMember.BIC', 'Bic');
        $labels['Age'] = _t('ClubMember.AGE', 'Age');
        return $labels;
    }

    function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main',
            CheckboxField::create('Active', _t('ClubMember.ACTIVE', 'Active')));
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
            TextField::create('Streetnumber', _t('ClubMember.STREETNUMBER', 'Streetnumber')));
        $fields->addFieldToTab('Root.Main',
            NumericField::create('Zip', _t('ClubMember.ZIP', 'Zip')));
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
            TextField::create('AccountHolderFirstName', _t('ClubMember.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderLastName', _t('ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderStreet', _t('ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderStreetnumber', _t('ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetnumber')));
        $fields->addFieldToTab('Root.Main',
            NumericField::create('AccountHolderZip', _t('ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip')));
        $fields->addFieldToTab('Root.Main',
            TextField::create('AccountHolderCity', _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity')));
        $fields->addFieldToTab('Root.Main',
            IbanField::create('Iban', _t('ClubMember.IBAN', 'Iban'))->addExtraClass("text"));
        $fields->addFieldToTab('Root.Main',
            BicField::create('Bic', _t('ClubMember.BIC', 'Bic'))->addExtraClass("text") );
        $fields->addFieldToTab('Root.Main',
            NumericField::create('Age', _t('ClubMember.AGE', 'Age'))->performReadonlyTransformation());
        return $fields;
    }

    /*public function getCMSValidator() {
        return new RequiredFields(array(
            'MyRequiredField'
        ));
    }*/

    public function getAge()
    {
        if(!$this->dbObject('Birthday')->value) return 0;
        $time = SS_Datetime::now()->Format('U');
        $ago = abs($time - strtotime($this->dbObject('Birthday')->value));
        return  round($ago/86400/365);
    }

    public function isActive()
    {
        return $this->Active;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->Age = $this->getAge();
    }

/*
    public function serialize() {
        return serialize(
            array(
                $this->Salutation,
                $this->FirstName,
                $this->LastName,
                $this->Birthday,
                $this->Nationality,
                $this->Street,
                $this->Streetnumber,
                $this->Zip,
                $this->City,
                $this->Email,
                $this->Mobil,
                $this->Phone,
                $this->TypeID,
                $this->Since,
                $this->AccountHolderFirstName,
                $this->AccountHolderLastName,
                $this->AccountHolderStreet,
                $this->AccountHolderStreetnumber,
                $this->AccountHolderZip,
                $this->AccountHolderCity,
                $this->Iban,
                $this->Bic
            )
        );
    }

    public function unserialize($data) {
        $data = unserialize($data);
        $this->Salutation = $data['Salutation'];
        $this->FirstName = $data['FirstName'];
        $this->LastName = $data['LastName'];
        $this->id = $data['Birthday'];
        $this->id = $data['Nationality'];
        $this->id = $data['Street'];
        $this->id = $data['Streetnumber'];
        $this->id = $data['Zip'];
        $this->id = $data['City'];
        $this->id = $data['Email'];
        $this->id = $data['Mobil'];
        $this->id = $data['Phone'];
        $this->id = $data['TypeID'];
        $this->id = $data['Since'];
        $this->id = $data['AccountHolderFirstName'];
        $this->id = $data['AccountHolderLastName'];
        $this->id = $data['AccountHolderStreet'];
        $this->id = $data['AccountHolderStreetnumber'];
        $this->id = $data['AccountHolderZip'];
        $this->id = $data['AccountHolderCity'];
        $this->Iban = $data['Iban'];
        $this->Bic = $data['Bic'];
    }
*/
    /**
     * Find an existing objects based on one or more uniqueness columns
     * specified via {@link self::$duplicateChecks}.
     *
     * @param array $record
     *
     * @return mixed
     */
    public function findExistingObject($record) {
        $SNG_objectClass = singleton($this->objectClass);
        // checking for existing records (only if not already found)
        foreach($this->duplicateChecks as $fieldName => $duplicateCheck) {
            if(is_string($duplicateCheck)) {
                // Skip current duplicate check if field value is empty
                if(empty($record[$duplicateCheck])) continue;
                // Check existing record with this value
                $dbFieldValue = $record[$duplicateCheck];
                $existingRecord = DataObject::get($this->objectClass)
                    ->filter($duplicateCheck, $dbFieldValue)
                    ->first();
                if($existingRecord) return $existingRecord;
            } elseif(is_array($duplicateCheck) && isset($duplicateCheck['callback'])) {
                if($this->hasMethod($duplicateCheck['callback'])) {
                    $existingRecord = $this->{$duplicateCheck['callback']}($record[$fieldName], $record);
                } elseif($SNG_objectClass->hasMethod($duplicateCheck['callback'])) {
                    $existingRecord = $SNG_objectClass->{$duplicateCheck['callback']}($record[$fieldName], $record);
                } else {
                    user_error("ClubMember::processRecord():"
                        . " {$duplicateCheck['callback']} not found on object class.", E_USER_ERROR);
                }
                if($existingRecord) {
                    return $existingRecord;
                }
            } else {
                user_error('ClubMember::processRecord(): Wrong format for $duplicateChecks', E_USER_ERROR);
            }
        }
        return false;
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
