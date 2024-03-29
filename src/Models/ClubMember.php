<?php

namespace SYBEHA\Clubmaster\Models;

use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\File;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\EMailField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Versioned\Versioned;
/* NEW Search */
use SilverStripe\ORM\Search\SearchContext;
use SilverStripe\ORM\Filters\PartialMatchFilter;
use SilverStripe\ORM\Filters\LessThanFilter;
use SilverStripe\ORM\Filters\GreaterThanFilter;
use SilverStripe\ORM\Filters\ExactMatchFilter;
/* Use global namespace for PHP DateTime */
use \DateTime;
/* See  https://github.com/dynamic/silverstripe-country-dropdown-field */
use Dynamic\CountryDropdownField\Fields\CountryDropdownField;
/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

use SYBEHA\Clubmaster\Models\ClubMemberType;
use SYBEHA\Clubmaster\Forms\Fields\EUNameTextField;
use SYBEHA\Clubmaster\Forms\Fields\ZipField;
use SYBEHA\Clubmaster\Forms\Fields\TelephoneNumberField;
use SYBEHA\Clubmaster\Forms\Fields\IbanField;
use SYBEHA\Clubmaster\Forms\Fields\BicField;

/**
 * Class ClubMember
 *
 * @package SYBEHA\Clubmaster
 * @subpackage Model
 * @author Lars Hasselbach <lars.hasselbach@gmail.com>
 * @since 15.03.2016
 * @copyright 2016 [sybeha]
 * @license see license file in modules root directory
 * TODO: Replace classname with __CLASS__
 */
class ClubMember extends DataObject
{
    /**
      * Important: Please note: It is strongly recommended to define a table_name for all namespaced models.
      * Not defining a table_name may cause generated table names to be too long
      * and may not be supported by your current database engine.
      * The generated naming scheme will also change when upgrading to SilverStripe 5.0 and potentially break.
      */
    private static $table_name = 'ClubMember';

    /**
     * Form-Fields @var string[]
     */
    private static $db = [
        'Salutation' => 'Enum(array("Divers","Frau","Herr","Schülerin","Schüler"), "Frau")',
        'NameTitle' => 'Varchar(255)',
        'FirstName' => 'Varchar(255)',
        'LastName' => 'Varchar(255)',
        'CareOf' => 'Varchar(255)',
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
        'EqualAddress' => 'Boolean(1)',
        'AccountHolderTitle' => 'Varchar(255)',
        'AccountHolderFirstName' => 'Varchar(255)',
        'AccountHolderLastName' => 'Varchar(255)',
        'AccountHolderStreet' => 'Varchar(255)',
        'AccountHolderStreetNumber' => 'Varchar(255)', // Nummer 34B?
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
        'CreationType' => 'Enum("Formular,Import,Händisch","Händisch")', // Distinguish Formular,Import,Händisch
        'Pending' => 'Boolean(0)',  // Only for Formular
        'MandateReference' => 'Varchar(35)', // max 35 char. (A-z0-9) TODO: has_one? (Multiple members might share one)
		'Comment' => 'Text'
    ];

    /**
     * Relation to ClubMemberType
     */
    private static $has_one = [
        'Type' => ClubMemberType::class,
        'ApplicationFormFile' => File::class
    ];

    //private static $owns = ['ApplicationFormFile'];

    //private static $cascade_deletes = ['ApplicationFormFile'];

    /**
     * Set defaults
     *
     * @var array
     */
    private static $defaults = [
        'CreationType' => 'Händisch',
        'Active' => '1',
        'EqualAddress' => '1'
    ];

    /**
     * Dynamic defaults for object instance
     * @todo: Check Dates should be stored using ISO 8601 formatted date (y-MM-dd)
     */
    public function populateDefaults()
    {
        $this->Since = date('d.m.Y');
        parent::populateDefaults();
    }

    /**
     * Fields to be displayed in table head of GridField
     *
     * @var array
     */
    private static $summary_fields = [
        //'ID'=> 'ID',
        'LastName' => 'LastName',
        'FirstName' => 'FirstName',
        'Zip' => 'Zip',
        'Age' => 'Age',
        'Sex' => 'Sex',
        'Since' => 'Since',
        'Comment' => 'Comment'
    ];

    /**
    * Defines a default sorting (e.g. within gridfield)
    * @var string
    */
    private static $default_sort=''; //e.g. Since ASC or LastName ASC

    /**
     * Generates a SearchContext to be used for building and processing
     * a generic search form for properties on this object.
     * Use {@link SilverStripe\ORM\Search\SearchContext::getQuery()} for debugging;
     *
     * @return SearchContext
     */
    public function getDefaultSearchContext()
    {
        // Postleitzahlen - Group the ZipFields
        $zipFieldGroup = FieldGroup::create(
            _t('SYBEHA\Clubmaster\Admins\ClubAdmin.ZIPRANGE', 'Zip range'),
            [ZipField::create('Search__ZipFrom',_t('SYBEHA\Clubmaster\Admins\ClubAdmin.ZIPSTART', 'From')),
            ZipField::create('Search__ZipTo',_t('SYBEHA\Clubmaster\Admins\ClubAdmin.ZIPEND', 'To'))]
        )->setName('Search__ZipRange')
        ->addExtraClass('fieldgroup--fill-width');

        //Active
        $active = DropdownField::create(
            'Active', _t('SYBEHA\Clubmaster\Admins\ClubAdmin.STATE', 'Member state'),
            [
                '' => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWALL', '(all)'),
                1 => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWACTIVE', 'Show active'),
                0 => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWINACTIVE', 'Show inactive')
            ]
        );

        //Age
        $ageFieldGroup = FieldGroup::create(
            _t('SYBEHA\Clubmaster\Admins\ClubAdmin.AGERANGE', 'Age range'),
            [NumericField::create('Search__AgeFrom',_t('SYBEHA\Clubmaster\Admins\ClubAdmin.AGESTART', 'From')),
            NumericField::create('Search__AgeTo',_t('SYBEHA\Clubmaster\Admins\ClubAdmin.AGEEND', 'To'))]
        )->setName('Search__AgeRange')
        ->addExtraClass('fieldgroup--fill-width');

        //Insurance
        $insurance = DropdownField::create(
            'Insurance', _t('SYBEHA\Clubmaster\Admins\ClubAdmin.INSURANCE', 'Insurance'),
            [
                '' => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWALL', '(all)'),
                1 => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWINSURANCE', 'Insured'),
                0 => _t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWNOINSURANCE', 'Non insured')
            ]
        );

        //Type
        $typename = DropdownField::create('Type', _t('SYBEHA\Clubmaster\Admins\ClubAdmin.TYPE', 'Type'))
            ->setSource(ClubMemberType::get()->map()->toArray())
            ->setEmptyString(_t('SYBEHA\Clubmaster\Admins\ClubAdmin.SHOWALL', 'all'));

        // Attention: This is also used for the order of fields
        $fields = $this->scaffoldSearchFields([
            'restrictFields' => ['LastName','FirstName'/*,'Zip','Age'*/,'Sex','Since'/*,'Insurance'*/,'Type.TypeName','Email',/*'Active'*/]
        ]);

        $fields->push($typename);
        $fields->push($active);
        $fields->push($insurance);
        $fields->push($zipFieldGroup);
        $fields->push($ageFieldGroup);

        /*foreach ($fields as $key => $value) {
            Injector::inst()
            ->get(LoggerInterface::class)
            ->debug('ClubMember - getDefaultSearchContext() key = ' . $key . ' value = ' . $value );
        }*/

        $filters = [
            'LastName' => new PartialMatchFilter('LastName'),
            'FirstName' => new PartialMatchFilter('FirstName'),
            //Zip -> see $fields->push($zipFieldGroup),
            'ZipFrom' => new GreaterThanFilter('Zip'),
            'ZipTo' => new LessThanFilter('Zip'),
            //Age -> see $fields->push($ageFieldGroup),
            'AgeFrom' => new GreaterThanFilter('Age'),
            'AgeTo' => new LessThanFilter('Age'),
            'Sex' => new ExactMatchFilter('Sex'),
            'Since'  => new GreaterThanFilter('Since'),
            'Insurance' => new ExactMatchFilter('Insurance'),
            'Type' => new ExactMatchFilter('TypeID'),
            'Email' => new PartialMatchFilter('Email'),
            'Active' => new ExactMatchFilter('Active')
        ];

        return new SearchContext(
            $this->ClassName, //$this->class
            $fields,
            $filters
        );
    }

    /**
     * Add i18n feature to labels
     * @return array labels
     */
    public function fieldLabels($includerelations = true)
    {
        /*
        $translation = _t(__CLASS__ . '.TYPE', 'Type');
        $translation = _t(self::class . '.TYPE', 'Type');
        */
        $labels = parent::fieldLabels($includerelations);
        // Relation has_one
        $labels['Type.ID'] = _t(__CLASS__ . '.TYPE', 'Type');
        $labels['Type.TypeName'] = _t(__CLASS__ . '.TYPE', 'Type');
        $labels['ApplicationFormFile.Title'] = _t(__CLASS__ . '.SERIALIZEDFILENAME', 'SerializedFileName');
        // Properties
        $labels['Salutation'] = _t(__CLASS__ . '.SALUTATION', 'Salutation');
        $labels['NameTitle'] = _t(__CLASS__ . '.NAMETITLE', 'Title');
        $labels['FirstName'] = _t(__CLASS__ . '.FIRSTNAME', 'FirstName');
        $labels['LastName'] = _t(__CLASS__ . '.LASTNAME', 'LastName');
        $labels['CareOf'] = _t(__CLASS__ . '.CAREOF', 'c/o');
        $labels['Birthday'] = _t(__CLASS__ . '.BIRTHDAY', 'Birthday');
        $labels['Nationality'] = _t(__CLASS__ . '.NATIONALITY', 'Nationality');
        $labels['Street'] = _t(__CLASS__ . '.STREET', 'Street');
        $labels['StreetNumber'] = _t(__CLASS__ . '.STREETNUMBER', 'StreetNumber');
        $labels['Zip'] = _t(__CLASS__ . '.ZIP', 'Zip');
        $labels['City'] = _t(__CLASS__ . '.CITY', 'City');
        $labels['Email'] = _t(__CLASS__ . '.EMAIL', 'Email');
        $labels['Mobil'] = _t(__CLASS__ . '.MOBIL', 'Mobil');
        $labels['Phone'] = _t(__CLASS__ . '.PHONE', 'Phone');
        $labels['Type'] = _t(__CLASS__ . '.TYPE', 'Type');
        $labels['Since'] = _t(__CLASS__ . '.SINCE', 'Since');
        $labels['Comment'] = _t(__CLASS__ . '.COMMENT', 'Comment');
        $labels['EqualAddress'] = _t(__CLASS__ . '.EQUALADDRESS', 'EqualAddress');
        $labels['AccountHolderTitle'] = _t(__CLASS__ . '.NAMETITLE', 'Title');
        $labels['AccountHolderFirstName'] = _t(__CLASS__ . '.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName');
        $labels['AccountHolderLastName'] = _t(__CLASS__ . '.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName');
        $labels['AccountHolderStreet'] = _t(__CLASS__ . '.ACCOUNTHOLDERSTREET', 'AccountHolderStreet');
        $labels['AccountHolderStreetNumber'] = _t(__CLASS__ . '.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetNumber');
        $labels['AccountHolderZip'] = _t(__CLASS__ . '.ACCOUNTHOLDERZIP', 'AccountHolderZip');
        $labels['AccountHolderCity'] = _t(__CLASS__ . '.ACCOUNTHOLDERCITY', 'AccountHolderCity');
        $labels['Iban'] = _t(__CLASS__ . '.IBAN', 'Iban');
        $labels['Bic'] = _t(__CLASS__ . '.BIC', 'Bic');
        //Special
        $labels['Active'] = _t(__CLASS__ . '.ACTIVE', 'Active');
        $labels['Insurance'] = _t(__CLASS__ . '.INSURANCE', 'Insurance');
        $labels['Age'] = _t(__CLASS__ . '.AGE', 'Age');
        $labels['Sex'] = _t(__CLASS__ . '.SEX', 'Sex');
        $labels['SerializedFileName'] = _t(__CLASS__ . '.SERIALIZEDFILENAME', 'SerializedFileName');
        $labels['FormClaimDate'] = _t(__CLASS__ . '.FORMCLAIMDATE', 'FormClaimDate');
        $labels['CreationType'] = _t(__CLASS__ . '.CREATIONTYPE', 'CreationType');
        $labels['Pending'] = _t(__CLASS__ . '.PENDING', 'Pending');
        $labels['MandateReference'] = _t(__CLASS__ . '.MANDATEREFERENCE', 'MandateReference');
        return $labels;
    }

    /**
     * List all required fields
     */
    public function getCMSValidator()
    {
        return new RequiredFields(
            [
            'Salutation', // for calculating Sex
            'FirstName',  // unique #1
            'LastName',   // unique #2
            'Birthday',   // unique #3
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
            ]
        );
    }

    /**
     * @see Good example of complex FormField building: SiteTree::getCMSFields()
     *
     * @return FieldList Returns a TabSet for usage within the CMS - don't use for frontend forms.
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        // The Main tab
        $main = $fields->findOrMakeTab('Root.Main')
            ->setTitle(_t(__CLASS__ . '.MAINTITLE', 'Main'));
        // Account tab
        $fields->addFieldToTab(
            'Root',
            new Tab(
                'Account',
                _t(__CLASS__ . '.BANKINGACCOUNT', 'Account data')
            )
        );
        // The Meta tab
        $fields->addFieldToTab('Root', new Tab('Meta', _t(__CLASS__ . '.META', 'Meta')));

        // Main tab
        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create(
                'Salutation',
                _t(__CLASS__ . '.SALUTATION', 'Salutation'),
                singleton(ClubMember::class)->dbObject('Salutation')->enumValues()
            )->setEmptyString('(Select one)')
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create(
                'NameTitle',
                _t(__CLASS__ . '.NAMETITLE', 'Title')
            )->addExtraClass('text')
                ->setDescription(_t(__CLASS__ . '.NAMETITLEHINT', 'e.g. Ph.D'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create(
                'FirstName',
                _t(__CLASS__ . '.FIRSTNAME', 'FirstName')
            )->setAttribute('autofocus', 'autofocus')->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create(
                'LastName',
                _t(__CLASS__ . '.LASTNAME', 'LastName')
            )->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create(
                'CareOf',
                _t(__CLASS__ . '.CAREOF', 'c/o')
            )->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            DateField::create('Birthday', _t(__CLASS__ . '.BIRTHDAY', 'Birthday'))
            //->setMinDate('-100 years')
            //->setMaxDate('+0 days')
        );
        $fields->addFieldToTab(
            'Root.Main',
            CountryDropdownField::create(
                'Nationality',
                _t(__CLASS__ . '.NATIONALITY', 'Nationality')
            )
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create('Street', _t(__CLASS__ . '.STREET', 'Street'))
                ->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create(
                'StreetNumber',
                _t(__CLASS__ . '.STREETNUMBER', 'StreetNumber')
            )->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            ZipField::create('Zip', _t(__CLASS__ . '.ZIP', 'Zip'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create('City', _t(__CLASS__ . '.CITY', 'City'))
                ->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Main',
            CheckboxField::create(
                'EqualAddress',
                _t(__CLASS__ . '.EQUALADDRESS', 'EqualAddress')
            )//->performReadonlyTransformation()
        );

        $fields->addFieldToTab(
            'Root.Main',
            EmailField::create('Email', _t(__CLASS__ . '.EMAIL', 'Email'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            TelephoneNumberField::create(
                'Mobil',
                _t(__CLASS__ . '.MOBIL', 'Mobil')
            )->addExtraClass('text')->setDescription(_t(__CLASS__ . '.PHONEHINT', '0-9+-'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            TelephoneNumberField::create(
                'Phone',
                _t(__CLASS__ . '.PHONE', 'Phone')
            )->addExtraClass('text')->setDescription(_t(__CLASS__ . '.PHONEHINT', '0-9+-'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create(
                'TypeID',
                _t(__CLASS__ . '.TYPE', 'Type')
            )->setSource(ClubMemberType::get()->map('ID', 'TypeName'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            DateField::create('Since', _t(__CLASS__ . '.SINCE', 'Since'))
        );
        //EN:Comment - DE:Kommentar
        $fields->addFieldToTab(
            'Root.Main',
            TextField::create('Comment', _t(__CLASS__ . '.COMMENT', 'Comment'))
        );

        // Account tab
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create(
                'AccountHolderTitle',
                _t(__CLASS__ . '.ACCOUNTHOLDERTITLE', 'AccountHolderTitle')
            )->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create(
                'AccountHolderFirstName',
                _t(__CLASS__ . '.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName')
            )->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create(
                'AccountHolderLastName',
                _t(__CLASS__ . '.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName')
            )->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create(
                'AccountHolderStreet',
                _t(__CLASS__ . '.ACCOUNTHOLDERSTREET', 'AccountHolderStreet')
            )->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create(
                'AccountHolderStreetNumber',
                _t(__CLASS__ . '.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetNumber')
            )->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            ZipField::create(
                'AccountHolderZip',
                _t(__CLASS__ . '.ACCOUNTHOLDERZIP', 'AccountHolderZip')
            )
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create(
                'AccountHolderCity',
                _t(__CLASS__ . '.ACCOUNTHOLDERCITY', 'AccountHolderCity')
            )->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            IbanField::create(
                'Iban',
                _t(__CLASS__ . '.IBAN', 'Iban')
            )->addExtraClass('text')->setDescription(_t(__CLASS__ . '.IBANHINT', 'IBAN hint'))
        );
        $fields->addFieldToTab(
            'Root.Account',
            BicField::create(
                'Bic',
                _t(__CLASS__ . '.BIC', 'Bic')
            )->addExtraClass('text')->setDescription(_t(__CLASS__ . '.BICHINT', 'BIC hint'))
        );
        $fields->addFieldToTab(
            'Root.Account',
            TextField::create(
                'MandateReference',
                _t(__CLASS__ . '.MANDATEREFERENCE', 'Mandate')
            )->performReadonlyTransformation()
        );

        // Meta tab
        $fields->addFieldToTab(
            'Root.Meta',
            CheckboxSetField::create(
                'Active',
                _t(__CLASS__ . '.ACTIVE', 'Active'),
                ['1' => 'Mitglied ist aktiv?']
            )
        );
        $fields->addFieldToTab(
            'Root.Meta',
            CheckboxSetField::create(
                'Insurance',
                _t(__CLASS__ . '.INSURANCE', 'Insurance'),
                ['1' => 'BLSV gemeldet?']
            )
        );
        $fields->addFieldToTab(
            'Root.Meta',
            NumericField::create('Age', _t(__CLASS__ . '.AGE', 'Age'))
                ->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            DropdownField::create(
                'Sex',
                _t(
                    'ClubMember.SEX',
                    'Sex'
                ),
                singleton(ClubMember::class)->dbObject('Sex')->enumValues()
            )//->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            TextField::create(
                'SerializedFileName',
                _t(__CLASS__ . '.SERIALIZEDFILENAME', 'SerializedFileName')
            )->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            DateField::create('FormClaimDate', _t(__CLASS__ . '.FORMCLAIMDATE', 'FormClaimDate'))
            ->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            TextField::create(
                'CreationType',
                _t(__CLASS__ . '.CREATIONTYPE', 'CreationType')
            )->performReadonlyTransformation()
        );

        // Remove the fields obsolete for ClubMember
        $fields->removeByName('Pending');
        if ($this->CreationType !== 'Formular') {
            $fields->removeByName(['SerializedFileName', 'FormClaimDate']);
        }

        return $fields;
    }

    /**
     * The field DBDatetime currently supports New Zealand date format (DD/MM/YYYY),
     * or an ISO 8601 formatted date and time (Y-m-d H:i:s).
     * Alternatively you can set a timestamp that is evaluated through PHP's built-in date()
     *  and strtotime() function according to your system locale.
     */
    private static $casting = [
        'FormClaimDate' => 'Datetime'
    ];

    /**
     * Used within the ModelAdmin to display
     * Firstname and Lastname for ClubMember objects
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->FirstName . ' ' . $this->LastName;
    }

    /**
     * Format FormClaimDate (date)
     *
     * @return string
     */
    public function getFormClaimDate()
    {
        // Create a DateTime object
        $dateTime = $this->dateFromFilename($this->SerializedFileName);
        // Use strftime to utilize locale
        return strftime('%d.%m.%Y %H:%M:%S', $dateTime->getTimestamp());
    }

    /**
     * Format Since (date) for ModelAdmin gridfield
     *
     * @return string
     */
    public function getSince(){
        // Create a DBDate object
        $dbDate = $this->dbObject('Since');
        // Use strftime to utilize locale
        return strftime('%d.%m.%Y', $dbDate->getTimestamp());
    }

    /**
     * Info: used by SilverStripe\Reports\Report
     *
     * @return string
     */
    public function ExportType(){
        $type = ClubMemberType::get()->byID($this->TypeID);
        if ($type) {
            $title = $type->Title;
            //Injector::inst()->get(LoggerInterface::class)->debug('ClubMember - ExportType()' . ' id='.$id.' type='.$type.' title='.$title);
            return ClubMemberType::get()->byID($this->TypeID)->Title;
        } else {
            //TODO: Translate
            return "Unbekannt";
        }
    }

    /**
     * Calculate the current age
     * @return int
     */
    public function getCalculatedAge()
    {
        //if (!$this->dbObject('Birthday')->value) {
        if (!$this->dbObject('Birthday')->value) {
            return 0;
        } else {
            $today = new DateTime('now');
        }
        $birthday = new DateTime($this->dbObject('Birthday')->value);
        $age = $birthday->diff($today)->format('%y');
        //Injector::inst()->get(LoggerInterface::class)
        //->debug('ClubMember - getCalculatedAge()' . ' today = '.$today->format(DateTime::RFC1123));
        //Injector::inst()->get(LoggerInterface::class)
        //->debug('ClubMember - getCalculatedAge()' . ' birthday = '.$birthday->format(DateTime::RFC1123));
        //Injector::inst()->get(LoggerInterface::class)
        //->debug('ClubMember - getCalculatedAge()' . ' age = '.$age);
        return $age;
    }

    /**
     * Calculate gender
     * @return string
     */
    public function getSex()
    {
        if ($this->Salutation == 'Frau' || $this->Salutation == 'Schülerin') {
            return 'w';
        }
        else if ($this->Salutation == 'Herr' || $this->Salutation == 'Schüler') {
            return 'm';
        } else return '';
    }

    /**
     * Check if member is active
     * @return bool
     */
    public function isActive()
    {
        return $this->Active;
    }

    /**
     * Get a DateTime from the given filename
     *
     * @param  string filename
     * @return DateTime
     */
    public function dateFromFilename($filename)
    {
        //Injector::inst()->get(LoggerInterface::class)
        //    ->debug('ClubMember - dateFromFilename('.$filename.') for ' . $this->getTitle());

        $date = new DateTime();
        // LA_12.08.2000_22.06.2018_13_08_09.antrag
        if (preg_match(
            '/^[A-Za-z]{2}_\d{2}.\d{2}.\d{4}_(\d{2})\.(\d{2})\.(\d{4})_(\d{2})_(\d{2})_(\d{2}).antrag$/',
            $filename,
            $matches
        )
        ) {
            // Get the appropriate matches XX_dd.mm.yyyy_hh_mm_ss.antrag
            $day = intval($matches[1]);
            $month = intval($matches[2]);
            $year = intval($matches[3]);
            $hour = intval($matches[4]);
            $minute = intval($matches[5]);
            $second = intval($matches[6]);
            // Set the date
            $date->setDate($year, $month, $day);
            // Set the time
            $date->setTime($hour, $minute, $second);
            /*
            Injector::inst()->get(LoggerInterface::class)
                ->debug(
                    'ClubMember(' . $this->getTitle() . ') - dateFromFilename('.$filename.')' .
                    ' calculated date = '. $date->format('d.m.Y H:i:s')
                );
            */
        } else {
            Injector::inst()->get(LoggerInterface::class)
                ->debug(
                    'ClubMember(' . $this->getTitle() . ') - dateFromFilename('.$filename.')' .
                    ' got the wrong format (date?) in filename'
                );
        }
        return $date;
    }

    /**
     * Event handler called before writing to database
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        //Injector::inst()->get(LoggerInterface::class)->debug('ClubMember - onBeforeWrite()' . ' "" ');
        // Set Age
        $this->Age = $this->getCalculatedAge();
        // Set Sex
        $this->Sex = $this->getSex();
        //TODO: Verify/complete address for imported records
        $siteConfig = SiteConfig::current_site_config();
        // Set MandateReference for newly added members
        $addMandate = $siteConfig->AddMandate; // see site config
        if ($addMandate) {
            if ($this->class =="ClubMember" && !$this->dbObject('MandateReference')->value) {
                $currentMax = DB::query("SELECT MAX(\"MandateReference\") FROM \"ClubMember\"")->value();
                // Regex for matching 3 and more numbers
                $mref = preg_replace_callback(
                    "|([0-9]{3,})|",
                    function ($matches) {
                        // Add 1 to match e.g. M0649-01 will match 0649
                        // but the leading 0 will be removed by the following operation
                        $matchPlusOne = $matches[0] + 1;
                        // Add leading 0 again
                        $newMandate = sprintf("%'.04d", $matchPlusOne);
                        return $newMandate;
                    },
                    $currentMax
                );
                $this->MandateReference = $mref;
            };
        }
    }

    /**
     * Event handler called before deleting from the database.
     * You can overload this to clean up or otherwise process data before delete this
     * record.  Don't forget to call parent::onBeforeDelete(), though!
     *
     * @uses DataExtension->onBeforeDelete()
     */
    /*public function onBeforeDelete()
    {
        return parent::onBeforeDelete();
    }*/

    /**
     * Only clubadmins are allowed
     *
     * @param  Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    /**
     * Only clubadmins are allowed
     *
     * @param  Member $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    /**
     * Only admins (Group Administrators) are allowed
     *
     * @param  Member $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    /**
     * Only admins (Group Administrators) are allowed
     *
     * @param  Member $member
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }
}
