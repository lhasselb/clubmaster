<?php

namespace SYBEHA\Clubmaster\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Assets\File;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\EMailField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\NumericField;
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
 * Class ClubMemberPending
 *
 * @package SYBEHA\Clubmaster\Models
 */
class ClubMemberPending extends ClubMember
{



    private static $defaults = ['CreationType' => 'Formular', 'Active' => '0'];

    /**
     * Fields to be displayed in Table head (gridfield)
     *
     * @var array
     */
    private static $summary_fields = [
        'Salutation',
        'FirstName',
        'LastName',
        'SerializedFileName',
        'FormClaimDate'
        ];

    private static $searchable_fields = [];

    /**
     * Add custom validation to the form
     * List all required fields
     *
     * @access public
     * @return RequiredFields
     */
    public function getCMSValidator()
    {
        return new RequiredFields(
            [
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
     * Centerpiece of every data administration interface in Silverstripe,
     * which returns a {@link FieldList} suitable for a {@link Form} object.
     * If not overloaded, we're using {@link scaffoldFormFields()} to automatically
     * generate this set. To customize, overload this method in a subclass
     * or extended onto it by using {@link DataExtension->updateCMSFields()}.
     *
     * <code>
     * class MyCustomClass extends DataObject {
     *  static $db = array('CustomProperty'=>'Boolean');
     *
     *  function getCMSFields() {
     *    $fields = parent::getCMSFields();
     *    $fields->addFieldToTab('Root.Content',new CheckboxField('CustomProperty'));
     *    return $fields;
     *  }
     * }
     * </code>
     *
     * @see Good example of complex FormField building: SiteTree::getCMSFields()
     *
     * @return FieldList Returns a TabSet for usage within the CMS - don't use for frontend forms.
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create(
                'Salutation',
                _t(
                    'ClubMember.SALUTATION',
                    'Salutation'
                ),
                singleton(ClubMember::class)->dbObject('Salutation')->enumValues()
            )
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create('NameTitle', _t('ClubMember.NAMETITLE', 'Title'))->addExtraClass('text')
            ->setDescription(_t('ClubMember.NAMETITLEHINT', 'e.g. Ph.D'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            EUNameTextField::create('FirstName', _t('ClubMember.FIRSTNAME', 'FirstName'))
            ->setAttribute('autofocus', 'autofocus')->addExtraClass('text')
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
            DateField::create('Birthday', _t('ClubMember.BIRTHDAY', 'Birthday'))
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
            EUNameTextField::create('StreetNumber', _t('ClubMember.STREETNUMBER', 'StreetNumber'))
            ->addExtraClass('text')
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
            TelephoneNumberField::create('Mobil', _t('ClubMember.MOBIL', 'Mobil'))
            ->addExtraClass('text')->setDescription(_t('ClubMember.PHONEHINT', '0-9+-'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            TelephoneNumberField::create('Phone', _t('ClubMember.PHONE', 'Phone'))
            ->addExtraClass('text')->setDescription(_t('ClubMember.PHONEHINT', '0-9+-'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create('TypeID', _t('ClubMember.TYPE', 'Type'))
            ->setSource(ClubMemberType::get()->map('ID', 'TypeName'))
        );
        $fields->addFieldToTab(
            'Root.Main',
            DateField::create('Since', _t('ClubMember.FROM', 'From'))
        );
        // Create Account tab
        $fields->addFieldToTab(
            'Root.Account',
            CheckboxField::create('EqualAddress', _t('ClubMember.EQUALADDRESS', 'EqualAddress'))
            ->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create('AccountHolderTitle', _t('ClubMember.ACCOUNTHOLDERTITLE', 'AccountHolderTitle'))
            ->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create(
                'AccountHolderFirstName',
                _t(
                    'ClubMember.ACCOUNTHOLDERFIRSTNAME',
                    'AccountHolderFirstName'
                )
            )
            ->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create(
                'AccountHolderLastName',
                _t(
                    'ClubMember.ACCOUNTHOLDERLASTNAME',
                    'AccountHolderLastName'
                )
            )
            ->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create('AccountHolderStreet', _t('ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet'))
            ->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create(
                'AccountHolderStreetNumber',
                _t(
                    'ClubMember.ACCOUNTHOLDERSTREETNUMBER',
                    'AccountHolderStreetNumber'
                )
            )
            ->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            ZipField::create('AccountHolderZip', _t('ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip'))
        );
        $fields->addFieldToTab(
            'Root.Account',
            EUNameTextField::create('AccountHolderCity', _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity'))
            ->addExtraClass('text')
        );
        $fields->addFieldToTab(
            'Root.Account',
            IbanField::create('Iban', _t('ClubMember.IBAN', 'Iban'))->addExtraClass('text')
            ->setDescription(_t('ClubMember.IBANHINT', 'IBAN hint'))
        );
        $fields->addFieldToTab(
            'Root.Account',
            BicField::create('Bic', _t('ClubMember.BIC', 'Bic'))->addExtraClass('text')
            ->setDescription(_t('ClubMember.BICHINT', 'BIC hint'))
        );
        $fields->addFieldToTab(
            'Root.Account',
            TextField::create('MandateReference', _t('ClubMember.MANDATEREFERENCE', 'Mandate'))
            ->addExtraClass('text')->setDescription(_t('ClubMember.MANDATEREFERENCEHINT', 'Mandate hint'))
            ->performReadonlyTransformation()
        );
        // Create Meta tab
        $fields->addFieldToTab(
            'Root.Meta',
            CheckboxSetField::create('Active', _t('ClubMember.ACTIVE', 'Active'), array('1' => 'Mitglied ist aktiv?'))
        );
        $fields->addFieldToTab(
            'Root.Meta',
            CheckboxSetField::create(
                'Insurance',
                _t(
                    'ClubMember.INSURANCE',
                    'Insurance'
                ),
                ['1' => 'BLSV gemeldet?']
            )
        );
        $fields->addFieldToTab(
            'Root.Meta',
            NumericField::create('Age', _t('ClubMember.AGE', 'Age'))->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            DropdownField::create('Sex', _t('ClubMember.SEX', 'Sex'), singleton(ClubMember::class)
            ->dbObject('Sex')->enumValues())
            //->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            TextField::create('SerializedFileName', _t('ClubMember.SERIALIZEDFILENAME', 'SerializedFileName'))
            ->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            DateField::create('FormClaimDate', _t('ClubMember.FORMCLAIMDATE', 'FormClaimDate'))
            //->setConfig('dateformat', 'dd.MM.yyyy')->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            TextField::create('CreationType', _t('ClubMember.CREATIONTYPE', 'CreationType'))
            ->performReadonlyTransformation()
        );
        $fields->addFieldToTab(
            'Root.Meta',
            CheckboxField::create('Pending', _t('ClubMember.PENDING', 'Pending'))
            ->performReadonlyTransformation()
        );
        //Remove the fields obsolete for ClubMmeberPending
        $fields->removeByName(['Active', 'Insurance']);

        return $fields;
    }

    /*
    * Used to "clean" a new ClubmemberPending
    */
    private function cleanNewClubMember()
    {
        // @todo: Assure correct dates in frontend form (better validation!),
        //       e.g. a user managed to create a birthday date in the future - using year 2096
        $year = new DateTime('now');
        $current_year = $year->format('Y');
        // Only required for 32bit version
        if (2147483647 == PHP_INT_MAX) {
            $birthday_year = strtok($this->Birthday, '-');
            if ($this->Birthday > $current_year.'-12-31') {
                $this->Birthday = strval((int)$birthday_year - 100) . '-' .strtok("-") . '-' . strtok("-");
                Injector::inst()->get(LoggerInterface::class)
                    ->info('ClubMemberPending - fillWith()' . ' replace birthday ' . $this->Birthday . ' to ' .
                    $this->Birthday . ' current = ' . $current_year);
            }
        } else {
            Injector::inst()->get(LoggerInterface::class)
                ->info('ClubMemberPending - fillWith()' . ' regular birthday given ' . $this->Birthday .
                ' current year = ' . $current_year);
            $this->Birthday = $this->Birthday;
        }
        // Lowercase required
        $this->Nationality = strtolower($this->Nationality);
        // Uppercase first
        $this->FirstName = ucfirst($this->FirstName);
        // Uppercase first
        $this->LastName = ucfirst($this->LastName);
        // Uppercase first
        $this->Street = ucfirst($this->Street);
        // Removes special chars.
        $this->StreetNumber = preg_replace('/[^A-Za-z0-9\- ]/', ' ', $this->StreetNumber);
        // Uppercase first
        $this->City = ucfirst($this->City);
        // Removes special chars.
        $this->Mobil = preg_replace('/[^A-Za-z0-9\- ]/', ' ', $this->Mobil);
        // Removes special chars.
        $this->Phone = preg_replace('/[^A-Za-z0-9\- ]/', ' ', $this->Phone);
        // Uppercase first
        $this->AccountHolderFirstName = ucfirst($this->AccountHolderFirstName);
        // Uppercase first
        $this->AccountHolderLastName = ucfirst($this->AccountHolderLastName);
        // Uppercase first
        $this->AccountHolderStreet = ucfirst($this->AccountHolderStreet);
        // Removes special chars.
        $this->AccountHolderStreetNumber = preg_replace(
            '/[^A-Za-z0-9\- ]/',
            ' ',
            $this->AccountHolderStreetNumber
        );
        // Uppercase first
        $this->AccountHolderCity = ucfirst($this->AccountHolderCity);

        // We need to replace the String TypeID from the form with a database entry for the appropriate TypeID
        $type = ClubMemberType::get()->filter('TypeName', $typeString = $this->TypeID)->first();
        // Initially there are no ClubMemberType's - TODO : Warning ?
        if ($type) {
            $this->TypeID = $type->ID;
        }

        if ($this->Zip == $this->AccountHolderZip && $this->City == $this->AccountHolderCity
            && $this->Street == $this->AccountHolderStreet && $this->StreetNumber == $this->AccountHolderStreetNumber
        ) {
            $this->EqualAddress = 1;
        } else {
            $this->EqualAddress = 0;
        }
    }

    /**
     * getter for Pending
     *
     * @return boolean
     */
    public function isPending()
    {
        return $this->Pending;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->cleanNewClubMember();
    }

    /**
     * Event handler called before deleting from the database.
     * You can overload this to clean up or otherwise process data before delete this
     * record.  Don't forget to call parent::onBeforeDelete(), though!
     *
     * @uses DataExtension->onBeforeDelete()
     */
    public function onBeforeDelete()
    {
        /* @todo: Should we delete the file ?
        $siteConfig = SiteConfig::current_site_config();
        $folder = $siteConfig->PendingFolder();
        $fileName = $this->SerializedFileName;
        $file = File::get()->filter(array(
            'Name' => $fileName,
            'ParentID' => $folder->ID
        ))->first();
        if ($file && $file->exists()) {
            $file->delete();
            $file->destroy();
        } */
        return parent::onBeforeDelete();
    }

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
        return Permission::check('CMS_ACCESS_LeftAndMain', 'any', $member);
    }

    /**
     * Only admins (Group Administrators) are allowed
     *
     * @param  Member $member
     * @return boolean
     */
    public function canCreate($member = null, $context = array())
    {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }
}
