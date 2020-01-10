<?php

/**
 * ClubMember object
 *
 * @package clubmaster
 * @subpackage models
 */
class ClubMember extends DataObject
{
    private static $db = array(
        //Form-Fields
        'Salutation' => 'Enum("Frau,Herr,Schülerin,Schüler","Frau")',
        'NameTitle' => 'Varchar(255)',
        'FirstName' => 'Varchar(255)',
        'LastName' => 'Varchar(255)',
        'CareOf' => 'Varchar(255)',
        'Birthday' => 'Date',
        'Nationality' => 'Varchar(255)', //CountryDropdownField
        'Street' => 'Varchar(255)',
        'StreetNumber' => 'Varchar(255)', // Nummer 34B?
        'Zip' => 'Int',
        'City' => 'Varchar(255)',
        'Email' => 'Varchar(254)',// See RFC 5321, Section 4.5.3.1.3. (256 minus the < and > character)
        'Mobil' => 'Varchar(255)',
        'Phone' => 'Varchar(255)',
        'Since' => 'Date',
        'EqualAddress' => 'Boolean(1)',
        'AccountHolderTitle' => 'Varchar(255)',
        'AccountHolderFirstName' => 'Varchar(255)',
        'AccountHolderLastName' => 'Varchar(255)',
        'AccountHolderStreet' => 'Varchar(255)',
        'AccountHolderStreetNumber' => 'Varchar(255)', // Nummer 34B?
        'AccountHolderZip' => 'Int',
        'AccountHolderCity' => 'Varchar(255)',
        'Iban' => 'Varchar(34)',
        'Bic' => 'Varchar(11)', //ISO_9362
        // Special Meaning
        'Active' => 'Boolean(1)', //Hide from print/export
        'Insurance' => 'Boolean',
        'Age' => 'Int', // Calculated
        'Sex' => 'Enum("w,m","w")', // Calculated
        'SerializedFileName' => 'Varchar(255)', // File created by Webform
        'CreationType' => 'Enum("Formular,Import,Händisch","Händisch")', // Distinguish Formular,Import,Händisch
        'Pending' => 'Boolean(0)',
        'MandateReference' => 'Varchar(35)', // max 35 char. (A-z0-9) TODO: has_one? (Multiple members might share one)
		'Comment' => 'Text'
    );

    /* Relation to ClubMemberType */
    private static $has_one = array(
        'Type' => 'ClubMemberType'
    );

    /* Defaults for object instance */
    private static $defaults = array(
        'CreationType' => 'Händisch',
        'Active' => '1',
        'EqualAddress' => '1'
    );

    /* Dynamic defaults for object instance */
    public function populateDefaults()
    {
        $this->Since = date('d.m.Y');
        parent::populateDefaults();
    }

    /**
     * Fields to be displayed in Table head
     * @var array
     */
    private static $summary_fields = array(
        'FirstName' => 'FirstName',
        'LastName' => 'LastName',
        'Zip' => 'Zip',
        'Age' => 'Age',
        'Sex' => 'Sex',
        'Since' => 'Since', //Since.FormatFromSettings
        //'Insurance' => 'Insurance',
        //'Type.TypeName' => 'Type.TypeName'
        'Email' => 'Email',
        'Comment' => 'Comment'
    );

    /**
     * Fields Searchable within top Filter
     * @var array
     */
    private static $searchable_fields = array(//'Type.ID'
    );

    function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);
        // Relation has_one
        $labels['Type.ID'] = _t('ClubMember.TYPE', 'Type');
        $labels['Type.TypeName'] = _t('ClubMember.TYPE', 'Type');
        // Properties
        $labels['Salutation'] = _t('ClubMember.SALUTATION', 'Salutation');
        $labels['NameTitle'] = _t('ClubMember.NAMETITLE', 'Title');
        $labels['FirstName'] = _t('ClubMember.FIRSTNAME', 'FirstName');
        $labels['LastName'] = _t('ClubMember.LASTNAME', 'LastName');
        $labels['CareOf'] = _t('ClubMember.CAREOF', 'c/o');
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
        $labels['Comment'] = _t('ClubMember.COMMENT', 'Comment');
        $labels['EqualAddress'] = _t('ClubMember.EQUALADDRESS', 'EqualAddress');
        $labels['AccountHolderTitle'] = _t('ClubMember.NAMETITLE', 'Title');
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
        $labels['MandateReference'] = _t('ClubMember.MANDATEREFERENCE', 'MandateReference');
        return $labels;
    }

    /* List all required fields */
    public function getCMSValidator()
    {
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
            //'Mobil',
            //'Phone',
            'Since',
            //'EqualAddress',
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
        // The Main tab
        $main = $fields->findOrMakeTab('Root.Main')->setTitle(_t('ClubMember.MAINTITLE', 'Main'));
        // Account tab
        $fields->addFieldToTab('Root', new Tab('Account', _t('ClubMember.BANKINGACCOUNT', 'Account data')));
        // The Meta tab
        $fields->addFieldToTab('Root', new Tab('Meta', _t('ClubMember.META', 'Meta')));

        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create('Salutation', _t('ClubMember.SALUTATION', 'Salutation'), singleton('ClubMember')->dbObject('Salutation')->enumValues())
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create('NameTitle', _t('ClubMember.NAMETITLE', 'Title'))->addExtraClass('text')->setDescription(_t('ClubMember.NAMETITLEHINT', 'e.g. Ph.D'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create('FirstName', _t('ClubMember.FIRSTNAME', 'FirstName'))->setAttribute('autofocus', 'autofocus')->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create('LastName', _t('ClubMember.LASTNAME', 'LastName'))->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create('CareOf', _t('ClubMember.CAREOF', 'c/o'))->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            DateField::create('Birthday', _t('ClubMember.BIRTHDAY', 'Birthday'))->setConfig('showcalendar', true)
        );
        $fields->addFieldToTab(
            'Root.Main',
            CountryDropdownField::create('Nationality', _t('ClubMember.NATIONALITY', 'Nationality'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create('Street', _t('ClubMember.STREET', 'Street'))->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create('StreetNumber', _t('ClubMember.STREETNUMBER', 'StreetNumber'))->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            ZipField::create('Zip', _t('ClubMember.ZIP', 'Zip'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create('City', _t('ClubMember.CITY', 'City'))->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            CheckboxField::create('EqualAddress', _t('ClubMember.EQUALADDRESS', 'EqualAddress'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            EmailField::create('Email', _t('ClubMember.EMAIL', 'Email'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            TelephoneNumberField::create('Mobil', _t('ClubMember.MOBIL', 'Mobil'))->addExtraClass('text')->setDescription(_t('ClubMember.PHONEHINT', '0-9+-'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            TelephoneNumberField::create('Phone', _t('ClubMember.PHONE', 'Phone'))->addExtraClass('text')->setDescription(_t('ClubMember.PHONEHINT', '0-9+-'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create('TypeID', _t('ClubMember.TYPE', 'Type'))->setSource(ClubMemberType::get()->map('ID', 'TypeName'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            DateField::create('Since', _t('ClubMember.SINCE', 'Since'))->setConfig('showcalendar', true)
        );
        //EN:Comment - DE:Kommentar
        $fields->addFieldToTab(
            'Root.Main',
            TextField::create('Comment', _t('ClubMember.COMMENT', 'Comment'))
        );
        //Account tab
        //$fields->addFieldToTab('Root.Account',
        //    CheckboxField::create('EqualAddress', _t('ClubMember.EQUALADDRESS', 'EqualAddress')));
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create('AccountHolderTitle', _t('ClubMember.ACCOUNTHOLDERTITLE', 'AccountHolderTitle'))->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create('AccountHolderFirstName', _t('ClubMember.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName'))->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create('AccountHolderLastName', _t('ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName'))->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create('AccountHolderStreet', _t('ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet'))->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create('AccountHolderStreetNumber', _t('ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetNumber'))->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            ZipField::create('AccountHolderZip', _t('ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip'))
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create('AccountHolderCity', _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity'))->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            IbanField::create('Iban', _t('ClubMember.IBAN', 'Iban'))->addExtraClass('text')->setDescription(_t('ClubMember.IBANHINT', 'IBAN hint'))
        );
        $fields->addFieldToTab(
            'Root.Account',
            BicField::create('Bic', _t('ClubMember.BIC', 'Bic'))->addExtraClass('text')->setDescription(_t('ClubMember.BICHINT', 'BIC hint'))
        );
        $fields->addFieldToTab(
            'Root.Account',
            TextField::create('MandateReference', _t('ClubMember.MANDATEREFERENCE', 'Mandate'))->performReadonlyTransformation()
        );
        //Meta tab
        $fields->addFieldToTab(
            'Root.Meta',
            CheckboxSetField::create('Active', _t('ClubMember.ACTIVE', 'Active'), array('1' => 'Mitglied ist aktiv?'))
        );
        $fields->addFieldToTab(
            'Root.Meta',
            CheckboxSetField::create('Insurance', _t('ClubMember.INSURANCE', 'Insurance'), array('1' => 'BLSV gemeldet?'))
        );
        $fields->addFieldToTab(
            'Root.Meta',
            NumericField::create('Age', _t('ClubMember.AGE', 'Age'))->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            DropdownField::create('Sex', _t('ClubMember.SEX', 'Sex'), singleton('ClubMember')->dbObject('Sex')->enumValues())->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            TextField::create('SerializedFileName', _t('ClubMember.SERIALIZEDFILENAME', 'SerializedFileName'))->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            DateField::create('FormClaimDate', _t('ClubMember.FORMCLAIMDATE', 'FormClaimDate'))->setConfig('dateformat', 'dd.MM.yyyy')->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            TextField::create('CreationType', _t('ClubMember.CREATIONTYPE', 'CreationType'))->performReadonlyTransformation()
        );
        //$fields->addFieldToTab('Root.Meta',
        //    CheckboxField::create('Pending', _t('ClubMember.PENDING', 'Pending'))->performReadonlyTransformation());
        //Remove the fields obsolete for ClubMember (added all for ClubMmeberPending)
        $fields->removeByName('Pending');
        if ($this->CreationType !== 'Formular') {
            $fields->removeByName(array('SerializedFileName', 'FormClaimDate'));//, 'MandateReference'
        }

        return $fields;
    }

    private static $casting = array(
        'FormClaimDate' => 'SS_Datetime'
    );

    public function getTitle()
    {
        return $this->FirstName . ' ' . $this->LastName;
    }

    public function ExportType()
    {
        $id = $this->TypeID;
        if ($id > 0) {
            $type = ClubMemberType::get()->byID($id);
            $title = $type->Title;
            SS_Log::log('id='.$id.' type='.$type.' title='.$title, SS_Log::WARN);
            return ClubMemberType::get()->byID($this->TypeID)->Title;
        } else {
            return "Unbekannt";
        }
    }

    public function getSinceDate()
    {
        $since = $this->dbObject('Since')->value;
        $date = new DateTime($since);
        return $date->format('d.m.Y');
    }

    public function getFormClaimDate()
    {
        $date = $this->dateFromFilename($this->SerializedFileName);
        return $date->FormatI18N('%d.%m.%Y %H:%M:%S');
    }

    public function getCalculatedAge()
    {
        if (!$this->dbObject('Birthday')->value) {
            return 0;
        } else {
            $today = new DateTime(date('Y-m-d'));
            //SS_Log::log('today = '.$today->format('d.m.Y'), SS_Log::WARN);
            $birthday = new DateTime($this->dbObject('Birthday')->value);
            //SS_Log::log('birthday = '.$birthday->format('d.m.Y'), SS_Log::WARN);
            $diff = $birthday->diff($today)->format('%y');
            //SS_Log::log('diff = '.$diff, SS_Log::WARN);
            return $diff;
        }
    }

    public function getSex()
    {
        return ($this->Salutation == 'Frau' || $this->Salutation == 'Schülerin') ? 'w' : 'm';
    }

    public function isActive()
    {
        return $this->Active;
    }

    /*public function getToday() {
        return SS_Datetime::now()->FormatI18N('%d.%m.%Y');
    }*/

    public function dateFromFilename($filename)
    {
        $date = new SS_DateTime();
        // XX_dd.mm.yyyy_hh_mm_ss.antrag
        if (preg_match('/^[A-Za-z]{2}_\d{2}.\d{2}.\d{4}_(\d{2})\.(\d{2})\.(\d{4})_(\d{2})_(\d{2})_(\d{2}).antrag$/', $filename, $matches)) {
            $day = intval($matches[1]);
            $month = intval($matches[2]);
            $year = intval($matches[3]);
            $hour = intval($matches[4]);
            $minute = intval($matches[5]);
            $second = intval($matches[6]);
            $date->setValue($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second);
            //SS_Log::log('date='.$date->format('d.m.Y H:i:s'),SS_Log::WARN);
        }
        return $date;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // Set Age
        $this->Age = $this->getCalculatedAge();
        // Set Sex
        $this->Sex = $this->getSex();
        /*if($this->CreationType == 'Händisch'){
            $this->Active = true;
        }*/
        $siteConfig = SiteConfig::current_site_config();
        // Set MandateReference for newly added members
        $addMandate = $siteConfig->AddMandate; // set in site config
        //SS_Log::log('addMandate='.$addMandate,SS_Log::WARN);
        if ($addMandate) {
            /*
            SS_Log::log('class='.$this->class,SS_Log::WARN);
            SS_Log::log('name='.$this->title,SS_Log::WARN);
            SS_Log::log('MandateReference='.$this->dbObject('MandateReference')->value,SS_Log::WARN);
            */
            if ($this->class =="ClubMember" && !$this->dbObject('MandateReference')->value) {
                $currentMax = DB::query("SELECT MAX(\"MandateReference\") FROM \"ClubMember\"")->value();
                // Regex for matching 3 and more numbers
                $mref = preg_replace_callback(
                    "|([0-9]{3,})|",
                    function ($matches) {
                    //SS_Log::log('0='.$matches[0],SS_Log::WARN);
                    // Add 1 to match e.g. M0649-01 will match 0649 but the leading 0 will be removed by the following operation
                        $matchPlusOne = $matches[0] + 1;
                    // Add leading 0 again
                        $newMandate = sprintf("%'.04d", $matchPlusOne);
                        return $newMandate;
                    },
                    $currentMax
                );

                //SS_Log::log('MandateReference=empty, '.$currentMax.' new='.$mref,SS_Log::WARN);
                $this->MandateReference = $mref;
            };
        }
    }

    /* Only clubadmins are allowed */
    public function canView($member = null)
    {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    /* Only clubadmins are allowed */
    public function canEdit($member = null)
    {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    /* Only admins (Group Administrators) are allowed */
    public function canDelete($member = null)
    {
        return Permission::check('CMS_ACCESS_LeftAndMain', 'any', $member);
    }

    /* Only clubadmins are allowed */
    public function canCreate($member = null)
    {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }
}
