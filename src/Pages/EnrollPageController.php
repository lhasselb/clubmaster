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
/* Locale */
use SilverStripe\i18n\i18n;
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

/* Used for setting min and max values for Birthday (-Field) */
//use \DateTime;


/**
 * Enroll page template controller
 * Class EnrollPageController
 *
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

    /**
     * Create the enroll form
     *
     * @return Form form
     */
    public function EnrollForm()
    {
        //Injector::inst()->get(LoggerInterface::class)->info('EnrollPageController - doEnroll() locale = ' . i18n::get_locale() . ' today = ' . DBDatetime::now());
        // @todo: Clarify if we should add an additional flag to the backend to hide them from the list
        // Check for types before using
        if (ClubMemberType::get()->exists()) {
            $clubMemberTypesMap = ClubMemberType::get()->exclude('ShowInFrontEnd', '0')->map('ID', 'Title');
        } else {
            $clubMemberTypesMap = [
                'Vollverdiener'=>'Vollverdiener',
                'Student / Azubi / Schüler'=>'Student / Azubi / Schüler'
            ];
        }

        // List of form fields
        $fields = FieldList::create(
            DropdownField::create(
                'Salutation',
                _t('SYBEHA\Clubmaster\Models\ClubMember.SALUTATION', 'Salutation'),
                singleton(ClubMember::class)->dbObject('Salutation')->enumValues()
            )->setEmptyString(_t('SYBEHA\Clubmaster\Models\ClubMember.SELECTONE', '(Select one)')),
            EUNameTextField::create('FirstName', _t('SYBEHA\Clubmaster\Models\ClubMember.FIRSTNAME', 'FirstName'))
                ->setAttribute('placeholder', 'Vorname'),
            EUNameTextField::create('LastName', _t('SYBEHA\Clubmaster\Models\ClubMember.LASTNAME', 'LastName'))
                ->setAttribute('placeholder', 'Nachname'),
            DateField::create('Birthday', _t('SYBEHA\Clubmaster\Models\ClubMember.BIRTHDAY', 'Birthday'))
                ->setAttribute('placeholder', DBDatetime::now()->Date())
                ->setMinDate('-100 years')
                ->setMaxDate('+0 days'),

            CountryDropdownField::create(
                'Nationality',
                _t('SYBEHA\Clubmaster\Models\ClubMember.NATIONALITY', 'Nationality')
            )->setEmptyString(_t('SYBEHA\Clubmaster\Models\ClubMember.SELECTONE', '(Select one)')),
            EUNameTextField::create('Street', _t('SYBEHA\Clubmaster\Models\ClubMember.STREET', 'Street'))
                ->setAttribute('placeholder', 'Straße'),
            EUNameTextField::create(
                'StreetNumber',
                _t('SYBEHA\Clubmaster\Models\ClubMember.STREETNUMBER', 'StreetNumber')
            )
                ->setAttribute('placeholder', 'Hausnummer'),
            ZipField::create('Zip', _t('SYBEHA\Clubmaster\Models\ClubMember.ZIP', 'Zip'))
                ->setAttribute('placeholder', '12345'),
            EUNameTextField::create('City', _t('SYBEHA\Clubmaster\Models\ClubMember.CITY', 'City'))
                ->setAttribute('placeholder', 'Wohnort'),
            EmailField::create('Email', _t('SYBEHA\Clubmaster\Models\ClubMember.EMAIL', 'Email'))
                ->setAttribute('placeholder', 'name@domain.de'),
            TelephoneNumberField::create('Mobil', _t('SYBEHA\Clubmaster\Models\ClubMember.MOBIL', 'Mobil'))
                ->setAttribute('placeholder', 'Handynummer'),
            TelephoneNumberField::create('Phone', 'oder ' . _t('SYBEHA\Clubmaster\Models\ClubMember.PHONE', 'Phone'))
                ->setAttribute('placeholder', 'Telefonnummer'),
            DropdownField::create('TypeID', 'Mitgliedstyp', $clubMemberTypesMap)
                ->setEmptyString(_t('SYBEHA\Clubmaster\Models\ClubMember.SELECTONE', '(Select one)')),
            DateField::create('Since', 'Mitglied ab')
                ->setValue(DBDatetime::now())
                ->setMinDate('-2 years')
                ->setMaxDate('+0 days'),
            CheckboxField::create(
                'EqualAddress',
                _t('SYBEHA\Clubmaster\Models\ClubMember.EQUALADDRESS', 'EqualAddress')
            )->setValue(true),
            EUNameTextField::create(
                'AccountHolderFirstName',
                _t('SYBEHA\Clubmaster\Models\ClubMember.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName')
            ),
            EUNameTextField::create(
                'AccountHolderLastName',
                _t('SYBEHA\Clubmaster\Models\ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName')
            ),
            EUNameTextField::create(
                'AccountHolderStreet',
                _t('SYBEHA\Clubmaster\Models\ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet')
            ),
            EUNameTextField::create(
                'AccountHolderStreetNumber',
                _t(
                    'SYBEHA\Clubmaster\Models\ClubMember.ACCOUNTHOLDERSTREETNUMBER',
                    'AccountHolderStreetNumber'
                )
            ),
            ZipField::create(
                'AccountHolderZip',
                _t('SYBEHA\Clubmaster\Models\ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip')
            ),
            EUNameTextField::create(
                'AccountHolderCity',
                _t('SYBEHA\Clubmaster\Models\ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity')
            ),
            IbanField::create('Iban', _t('SYBEHA\Clubmaster\Models\ClubMember.IBAN', 'Iban'))
                ->setAttribute('placeholder', "DE12500105170648489890")->addExtraClass("text"),
            BicField::create('Bic', _t('SYBEHA\Clubmaster\Models\ClubMember.BIC', 'Bic'))
                ->setAttribute('placeholder', "VOBADEXX")->addExtraClass("text")
        );

        // List of action fields
        $actions = new FieldList(
            FormAction::create('doEnroll')
                ->setTitle(_t('SYBEHA\Clubmaster\Pages\EnrollPage.ENROLL', 'Enroll'))
                ->setUseButtonTag(true)
        );

        // List of required fields
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
        // controller, functionname(__FUNCTION__ = 'EnrollForm'), formfields, actionfields, requirdfields
        $form = new Form($this, __FUNCTION__, $fields, $actions, $required);
        $form->setTemplate('EnrollForm');
        $form->setFormMethod('POST', true);

        return $form;
    }

    /**
     * Handling submission specified within form action list
     *
     * @param  FieldList $data
     * @param  Form      $form
     * @return void
     */
    public function doEnroll($data, Form $form)
    {
        Injector::inst()->get(LoggerInterface::class)
            ->info('EnrollPageController - doEnroll() locale = ' . i18n::get_locale());

        Injector::inst()->get(LoggerInterface::class)
            ->info('EnrollPageController - doEnroll() data Birthday = ' . $data['Birthday']);

        // Create a ClubMember object
        $clubMemberPending = new ClubMemberPending();

        // Save data into object
        $form->saveInto($clubMemberPending);

        // Attention: given form birthday date (string) got the wrong format 1970-01-03

        // Create a DBDate object
        $dbDate = $clubMemberPending->dbObject('Birthday');
        // Use strftime to utilize locale
        $birthday = strftime('%d.%m.%Y', $dbDate->getTimestamp());

        // Get the path for the folder and add a filename
        // like LH_03.01.1970_dd.mm.YYYY_HH_MM_SS.antrag
        $name = $data['FirstName'][0] . $data['LastName'][0] . '_'
            . $birthday . '_' . date('d.m.Y_H_i_s') . '.antrag';

        // Get the desired folder to store the serialized object
        $folder = $this->Folder();

        // Files property Filename contains (optional) preceding folder
        $filename = $folder->Name . DIRECTORY_SEPARATOR . $name;

        Injector::inst()->get(LoggerInterface::class)
            ->debug('EnrolPageController - doEnroll()  path = ' . $filename);

        // Add path to object
        $clubMemberPending->SerializedFileName = $filename;

        // Serialize object safely
        $serialized = base64_encode(serialize($clubMemberPending));

        // Store the serialized file
        $file = new File();
        $file->setFromString($serialized, $filename);
        $id = $file->write();

        Injector::inst()->get(LoggerInterface::class)
            ->debug('EnrollPageController - doEnroll()  file id = ' . $id . ' filename = ' . $file->Filename);

        if ($id) {
            $typeName = ClubMemberType::get()->byID($clubMemberPending->TypeID)->TypeName;
            // Send an E-Mail
            // $email = new Email($from, $to, $subject, $body);
            $email = Email::create()
                //setFrom('JIM .e.V')
                ->setTo($data['Email'])
                ->setData($clubMemberPending)
                ->addData('TypeName', $typeName)
                ->setSubject('Anmeldung bei Jim e.V.')
                ->setHTMLTemplate('EMail\EnrollMail');

            // Get the session object
            $session = $this->getRequest()->getSession();
            // Add object
            $session->set('ClubMemberPending', $serialized);

            // @todo: Meaningful E-Mail validation
            if ($email->send()) {
                // Nothing
            } else {
                // there may have been 1 or more failures
                $session->set('Error', 'Fehler');
            }
        }
        //return $this->redirectBack();
        return $this->redirect(EnrollSuccessPage::get()->First()->Link());
    }

    public function init()
    {
        parent::init();
        $theme = $this->themeDir();
        // Same as Theme TODO: Make configurable
        Requirements::javascript('//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js');
        //Requirements::javascript('silverstripe/admin:thirdparty/jquery/jquery.js');
        // Same as Theme TODO: Make configurable
        //Front-End validation
        Requirements::javascript('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js');
        Requirements::javascript('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/localization/messages_de.min.js');
        Requirements::javascript('lhasselb/clubmaster:client/dist/javascript/enroll.js');
        Requirements::css('lhasselb/clubmaster:client/dist/styles/main.css');
    } //init
} //eof
