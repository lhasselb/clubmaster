<?php

namespace SYBEHA\Clubmaster\Pages;

use PageController;

use SilverStripe\Control\Director;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\Session;
use SilverStripe\View\Requirements;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\EMailField;
use SilverStripe\Forms\TextField;
use SilverStripe\View\ArrayData;
/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

use SYBEHA\Clubmaster\Models\ClubMember;
use SYBEHA\Clubmaster\Models\ClubMemberPending;
use SYBEHA\Clubmaster\Models\ClubMemberType;
use SYBEHA\Clubmaster\Forms\Fields\EUNameTextField;
use SYBEHA\Clubmaster\Forms\Fields\ZipField;
use SYBEHA\Clubmaster\Forms\Fields\TelephoneNumberField;
use SYBEHA\Clubmaster\Forms\Fields\IbanField;
use SYBEHA\Clubmaster\Forms\Fields\BicField;
/* See  https://github.com/dynamic/silverstripe-country-dropdown-field */
use Dynamic\CountryDropdownField\Fields\CountryDropdownField;
//use \DateTime;


/**
 * Enroll page template controller
 * Class EnrollPageController
 * @package SYBEHA\Clubmaster\Pages
 */
class EnrollPageController extends PageController
{
    /**
     * An array of actions that can be accessed via a request. Each array element should be an action name, and the
     * permissions or conditions required to allow the user to access it.
     *
     * <code>
     * [
     *     'action', // anyone can access this action
     *     'action' => true, // same as above
     *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
     *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
     * ];
     * </code>
     *
     * @var array
     */
    private static $allowed_actions = ['EnrollForm'];

    public function EnrollForm()
    {
        $today = DBDatetime::now();//->FormatI18N("%d.%m.%Y");

        // @todo: Clarify if we should add an additional flag to the backend to hide them from the list 
        // Check for types before using
        if (ClubMemberType::get()->exists()) {
            $clubMemberTypesMap = ClubMemberType::get()->exclude('ShowInFrontEnd', '0')->map('ID', 'Title');
        } else {
            $clubMemberTypesMap = ['Vollverdiener'=>'Vollverdiener','Student / Azubi / Schüler'=>'Student / Azubi / Schüler'];
        }


        $fields = FieldList::create(
            DropdownField::create(
                'Salutation',
                _t('SYBEHA\Clubmaster\Models\ClubMember.SALUTATION', 'Salutation'),
                singleton(ClubMember::class)->dbObject('Salutation')->enumValues()
            )->setEmptyString(_t('SYBEHA\Clubmaster\Models\ClubMember.SELECTONE', '(Select one)')),
            TextField::create('FirstName', _t('SYBEHA\Clubmaster\Models\ClubMember.FIRSTNAME', 'FirstName'))
                ->setAttribute('placeholder', 'Vorname'),
            TextField::create('LastName', _t('SYBEHA\Clubmaster\Models\ClubMember.LASTNAME', 'LastName'))
                ->setAttribute('placeholder', 'Nachname'),
            DateField::create('Birthday', _t('SYBEHA\Clubmaster\Models\ClubMember.BIRTHDAY', 'Birthday'))
                ->setAttribute('placeholder', $today),
            CountryDropdownField::create('Nationality', _t('SYBEHA\Clubmaster\Models\ClubMember.NATIONALITY', 'Nationality'))->setEmptyString(_t('SYBEHA\Clubmaster\Models\ClubMember.SELECTONE', '(Select one)')),
            TextField::create('Street', _t('SYBEHA\Clubmaster\Models\ClubMember.STREET', 'Street'))
                ->setAttribute('placeholder', 'Straße'),
            TextField::create('StreetNumber', _t('SYBEHA\Clubmaster\Models\ClubMember.STREETNUMBER', 'StreetNumber'))
                ->setAttribute('placeholder', 'Hausnummer'),
            ZipField::create('Zip', _t('SYBEHA\Clubmaster\Models\ClubMember.ZIP', 'Zip'))
                ->setAttribute('placeholder', '12345'),
            TextField::create('City', _t('SYBEHA\Clubmaster\Models\ClubMember.CITY', 'City'))
                ->setAttribute('placeholder', 'Wohnort'),
            EmailField::create('Email', _t('SYBEHA\Clubmaster\Models\ClubMember.EMAIL', 'Email'))
                ->setAttribute('placeholder', 'name@domain.de'),
            TextField::create('Mobil', _t('SYBEHA\Clubmaster\Models\ClubMember.MOBIL', 'Mobil'))
                ->setAttribute('placeholder', 'Handynummer'), //PhoneNumberField
            TextField::create('Phone', _t('SYBEHA\Clubmaster\Models\ClubMember.PHONE', 'Phone'))
                ->setAttribute('placeholder', 'Telefonnummer'), //PhoneNumberField
            DropdownField::create('TypeID', 'Mitgliedstyp', $clubMemberTypesMap)
                ->setEmptyString(_t('SYBEHA\Clubmaster\Models\ClubMember.SELECTONE', '(Select one)')),
            //->setSource(ClubMemberType::get()->map('ID', 'TypeName')),
        DateField::create('Since', 'Mitglied ab')->setValue(DBDatetime::now()/*->FormatI18N('%d.%m.%Y')*/),
            CheckboxField::create('EqualAddress', _t('SYBEHA\Clubmaster\Models\ClubMember.EQUALADDRESS', 'EqualAddress'))->setValue(true),
            TextField::create(
                'AccountHolderFirstName',
                _t(
                    'ClubMember.ACCOUNTHOLDERFIRSTNAME',
                    'AccountHolderFirstName'
                )
            ),
            TextField::create('AccountHolderLastName', _t('SYBEHA\Clubmaster\Models\ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName')),
            TextField::create('AccountHolderStreet', _t('SYBEHA\Clubmaster\Models\ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet')),
            TextField::create(
                'AccountHolderStreetNumber',
                _t(
                    'ClubMember.ACCOUNTHOLDERSTREETNUMBER',
                    'AccountHolderStreetNumber'
                )
            ),
            ZipField::create('AccountHolderZip', _t('SYBEHA\Clubmaster\Models\ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip')),
            TextField::create('AccountHolderCity', _t('SYBEHA\Clubmaster\Models\ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity')),
            IbanField::create('Iban', _t('SYBEHA\Clubmaster\Models\ClubMember.IBAN', 'Iban'))
                ->setAttribute('placeholder', "DE12500105170648489890")->addExtraClass("text"),
            BicField::create('Bic', _t('SYBEHA\Clubmaster\Models\ClubMember.BIC', 'Bic'))
                ->setAttribute('placeholder', "VOBADEXX")->addExtraClass("text")
        );

        $actions = new FieldList(
            FormAction::create('doEnroll')->setTitle(_t('EnrollPage.ENROLL', 'Enroll'))->setUseButtonTag(true)
        );

        $required = new RequiredFields(
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
            'TypeID',
            'Since',
            'AccountHolderFirstName',
            'AccountHolderLastName',
            'AccountHolderStreet',
            'AccountHolderStreetNumber',
            'AccountHolderZip',
            'AccountHolderCity',
            'Iban',
            'Bic'
        );
        $form = new Form($this, 'EnrollForm', $fields, $actions, $required);
        $form->setTemplate('EnrollForm');
        $form->setFormMethod('POST', true);

        return $form;
    }

    public function doEnroll($data, Form $form)
    {
        // Add a success message
        //$form->sessionMessage(
        //    'Vielen Dank für die Anmeldung ' .$data['FirstName']. ' ' .$data['LastName'],
        //    'success'
        //);
        /*foreach ($data as $key => $value) {
            SS_Log::log("key=".$key." value=".$value,SS_Log::WARN);
        }*/
        // Create a ClubMember object
        $clubMemberPending = new ClubMemberPending();
        // Save data into object
        $form->saveInto($clubMemberPending);
        // Serialize object safely
        $serialized = base64_encode(serialize($clubMemberPending));
        // Get the desired folder to store the serialized object
        $folder = $this->Folder();
        // Get the path for the folder and add a filename
        /*
        $path = $folder->getFullPath() . $data['FirstName'][0] . $data['LastName'][0] . '_'
            . $data['Birthday'] . '_' . date('d.m.Y_H_i_s') . '.antrag';
        */
        $path = $folder->Filename . $data['FirstName'][0] . $data['LastName'][0] . '_'
        . $data['Birthday'] . '_' . date('d.m.Y_H_i_s') . '.antrag';
        Injector::inst()->get(LoggerInterface::class)
            ->debug('EnrolPageController - doEnroll()  path = ' . $path);
        
        /* Store the object at calculated path
         * If filename does not exist, the file is created. Otherwise,
         * the existing file is overwritten, unless the FILE_APPEND flag is set.
         */
        //file_put_contents($path, $serialized);
        $file = new File();
        $info = $file->setFromString($serialized, $path);
        foreach ( $info as $key => $value ) {
            Injector::inst()->get(LoggerInterface::class)
            ->debug('key =' . $key . ' value = ' . $value);
        }        

        //Send an E-Mail
        $email = Email::create()
        ->setTo($data['Email'])
        ->setSubject('Anmeldung bei Jim e.V.')
        ->setHTMLTemplate('EMail\EnrollMail');
        //->populateTemplate(new ArrayData($data));
        if ($email->send()) {
            //email sent successfully
        } else {
            // there may have been 1 or more failures
        }
        //return $this->redirectBack();
        //SS_Log::log(EnrollSuccessPage::get()->First()->Link(),SS_Log::WARN);
        $session = $this->getRequest()->getSession();
        $session->set('Data', $data);
        return $this->redirect(EnrollSuccessPage::get()->First()->Link());
    }

    public function init()
    {
        parent::init();
        $theme = $this->themeDir();
        //Add javascript here
        Requirements::block(THIRDPARTY_DIR . '/jquery/jquery.js');
        Requirements::block('framework/javascript/DateField.js');
        Requirements::block('framework/thirdparty/jquery-ui/jquery-ui.js');
        Requirements::block('framework/thirdparty/jquery-ui/datepicker/i18n/jquery.ui.datepicker-de.js');
        Requirements::block(THIRDPARTY_DIR . '/jquery-ui-themes/smoothness/jquery-ui.css');

        //Front-End validation
        //Requirements::javascript('mysite/javascript/jquery-validate/jquery.validate.js');
        //Requirements::javascript('mysite/javascript/jquery-validate/additional-methods.js');
        //Requirements::javascript('mysite/javascript/jquery-validate/localization/messages_de.js');

        // eonasdan Datetimepicker
        Requirements::css(
            $theme.'/javascript/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css'
        );
        Requirements::javascript($theme.'/javascript/moment/min/moment-with-locales.js');
        Requirements::javascript(
            $theme.'/javascript/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js'
        );
        /*
        if (method_exists(Requirements::backend(), 'add_dependency')) {
            Requirements::backend()
                ->add_dependency(
                    'mysite/javascript/Enroll.js',
                    $theme.'/javascript/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js'
                );
        }
        */
    } //init
} //eof
