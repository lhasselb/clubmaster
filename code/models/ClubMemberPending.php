<?php

/**
 * ClubMemberpending object
 *
 * @package clubmaster
 * @subpackage models
 */
class ClubMemberPending extends ClubMember
{

    private static $defaults = array('CreationType' => 'Formular', 'Active' => '0');

    private static $summary_fields = array(
        'Salutation',
        'FirstName',
        'LastName',
        'SerializedFileName',
        'FormClaimDate'
    );

    private static $searchable_fields = array();


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
        ));
    }

    function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main',
            DropdownField::create('Salutation', _t('ClubMember.SALUTATION', 'Salutation'), singleton('ClubMember')->dbObject('Salutation')->enumValues()));
        $fields->addFieldToTab('Root.Main',
            EUNameTextField::create('FirstName', _t('ClubMember.FIRSTNAME', 'FirstName'))->setAttribute('autofocus', 'autofocus')->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            EUNameTextField::create('LastName', _t('ClubMember.LASTNAME', 'LastName'))->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            DateField::create('Birthday', _t('ClubMember.BIRTHDAY', 'Birthday'))->setConfig('showcalendar', true));
        $fields->addFieldToTab('Root.Main',
            CountryDropdownField::create('Nationality', _t('ClubMember.NATIONALITY', 'Nationality')));
        $fields->addFieldToTab('Root.Main',
            EUNameTextField::create('Street', _t('ClubMember.STREET', 'Street'))->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            EUNameTextField::create('StreetNumber', _t('ClubMember.STREETNUMBER', 'StreetNumber'))->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            ZipField::create('Zip', _t('ClubMember.ZIP', 'Zip')));
        $fields->addFieldToTab('Root.Main',
            EUNameTextField::create('City', _t('ClubMember.CITY', 'City'))->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            EmailField::create('Email', _t('ClubMember.EMAIL', 'Email')));
        $fields->addFieldToTab('Root.Main',
            TelephoneNumberField::create('Mobil', _t('ClubMember.MOBIL', 'Mobil'))->addExtraClass('text')->setDescription(_t('ClubMember.PHONEHINT', '0-9+-')));//PhoneNumberField
        $fields->addFieldToTab('Root.Main',
            TelephoneNumberField::create('Phone', _t('ClubMember.PHONE', 'Phone'))->addExtraClass('text')->setDescription(_t('ClubMember.PHONEHINT', '0-9+-')));//PhoneNumberField
        $fields->addFieldToTab('Root.Main',
            DropdownField::create('TypeID', _t('ClubMember.TYPE', 'Type'))->setSource(ClubMemberType::get()->map('ID', 'TypeName')));
        $fields->addFieldToTab('Root.Main',
            DateField::create('Since', _t('ClubMember.FROM', 'Since'))->setConfig('showcalendar', true));
        $fields->addFieldToTab('Root.Main',
            CheckboxField::create('EqualAddress', _t('ClubMember.EQUALADDRESS', 'EqualAddress')));
        $fields->addFieldToTab('Root.Main',
            EUNameTextField::create('AccountHolderFirstName', _t('ClubMember.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName'))->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            EUNameTextField::create('AccountHolderLastName', _t('ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName'))->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            EUNameTextField::create('AccountHolderStreet', _t('ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet'))->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            EUNameTextField::create('AccountHolderStreetNumber', _t('ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetNumber'))->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            ZipField::create('AccountHolderZip', _t('ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip')));
        $fields->addFieldToTab('Root.Main',
            EUNameTextField::create('AccountHolderCity', _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity'))->addExtraClass('text'));
        $fields->addFieldToTab('Root.Main',
            IbanField::create('Iban', _t('ClubMember.IBAN', 'Iban'))->addExtraClass('text')->setDescription(_t('ClubMember.IBANHINT', 'IBAN hint')));
        $fields->addFieldToTab('Root.Main',
            BicField::create('Bic', _t('ClubMember.BIC', 'Bic'))->addExtraClass('text')->setDescription(_t('ClubMember.BICHINT', 'BIC hint')));
        //Special
        /*$fields->addFieldToTab('Root.Meta',
            CheckboxField::create('Active', _t('ClubMember.ACTIVE', 'Active')));
        $fields->addFieldToTab('Root.Meta',
            CheckboxField::create('Insurance', _t('ClubMember.INSURANCE', 'Insurance')));*/
        $fields->addFieldToTab('Root.Meta',
            NumericField::create('Age', _t('ClubMember.AGE', 'Age'))->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Meta',
            DropdownField::create('Sex', _t('ClubMember.SEX', 'Sex'), singleton('ClubMember')->dbObject('Sex')->enumValues())->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Meta',
            TextField::create('SerializedFileName', _t('ClubMember.SERIALIZEDFILENAME', 'SerializedFileName'))->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Meta',
            DateField::create('FormClaimDate', _t('ClubMember.FORMCLAIMDATE', 'FormClaimDate'))->setConfig('dateformat', 'dd.MM.yyyy')->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Meta',
            TextField::create('CreationType', _t('ClubMember.CREATIONTYPE', 'CreationType'))->performReadonlyTransformation());
        $fields->addFieldToTab('Root.Meta',
            CheckboxField::create('Pending', _t('ClubMember.PENDING', 'Pending'))->performReadonlyTransformation());

        $fields->removeByName(array('Active', 'Insurance'));

        return $fields;
    }


    public function fillWith($data)
    {
        if ($data === NULL) return false;
        $this->Salutation = $data->Salutation;
        $this->FirstName = $data->FirstName;
        $this->LastName = $data->LastName;
        $this->Birthday = $data->Birthday;
        $this->Nationality = $data->Nationality;
        $this->Street = $data->Street;
        $this->StreetNumber = $data->StreetNumber;
        $this->Zip = $data->Zip;
        $this->City = $data->City;
        $this->Email = $data->Email;
        $this->Mobil = $data->Mobil;
        $this->Phone = $data->Phone;
        $this->Since = date('d.m.Y');
        $this->AccountHolderFirstName = $data->AccountHolderFirstName;
        $this->AccountHolderLastName = $data->AccountHolderLastName;
        $this->AccountHolderStreet = $data->AccountHolderStreet;
        $this->AccountHolderStreetNumber = $data->AccountHolderStreetNumber;
        $this->AccountHolderZip = $data->AccountHolderZip;
        $this->AccountHolderCity = $data->AccountHolderCity;
        $this->Iban = $data->Iban;
        $this->Bic = $data->Bic;
        $this->AccountHolderZip = $data->AccountHolderZip;
        //Special
        $this->CreationType = 'Formular';
        $this->Pending = 1;

        if ($this->Zip == $this->AccountHolderZip && $this->City == $this->AccountHolderCity &&
            $this->Street == $this->AccountHolderStreet && $this->StreetNumber == $this->AccountHolderStreetNumber
        ) {
            $this->EqualAddress = 1;
        } else {
            $this->EqualAddress = 0;
        }
    }

    public function isPending()
    {
        return $this->Pending;
    }

    public function onBeforeDelete()
    {

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
        }

        return parent::onBeforeDelete();
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
