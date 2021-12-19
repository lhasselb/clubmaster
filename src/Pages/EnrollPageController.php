<?php

namespace Sybeha\Clubmaster\Pages;

use PageController;

use SilverStripe\Control\Director;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Storage\AssetStore;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\Session;
use SilverStripe\Forms\LabelField;
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

use Sybeha\Clubmaster\Models\ClubMember;
use Sybeha\Clubmaster\Models\ClubMemberPending;
use Sybeha\Clubmaster\Models\ClubMemberSalutation;
use Sybeha\Clubmaster\Models\ClubMemberType;
use Sybeha\Clubmaster\Forms\Fields\EUNameTextField;
use Sybeha\Clubmaster\Forms\Fields\ZipField;
use Sybeha\Clubmaster\Forms\Fields\TelephoneNumberField;
use Sybeha\Clubmaster\Forms\Fields\IbanField;
use Sybeha\Clubmaster\Forms\Fields\BicField;

/* See  https://github.com/dynamic/silverstripe-country-dropdown-field */
use Dynamic\CountryDropdownField\Fields\CountryDropdownField;

/* Used for setting min and max values for Birthday (-Field) */
//use \DateTime;


/**
 * Enroll page template controller
 * Class EnrollPageController
 *
 * @package Sybeha\Clubmaster\Pages
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

    private static $url_segment = 'EnrollPageController';

    /**
     * Create the enroll form
     *
     * @return Form form
     */
    public function EnrollForm()
    {
        //Injector::inst()->get(LoggerInterface::class)->info('EnrollPageController - doEnroll() locale = ' . i18n::get_locale() . ' today = ' . DBDatetime::now());
        // Check for types before using
        if (ClubMemberType::get()->exists()) {
            $clubMemberTypesMap = ClubMemberType::get()->exclude('ShowInFrontEnd', '0')->map('ID', 'Title');
        } else {
            $clubMemberTypesMap = [
                'Vollzahlend' => 'Vollzahlend',
                'Ermäßigt' => 'Ermäßigt'
            ];
        }
        // Check for salutation before using
        if (ClubMemberSalutation::get()->exists()) {
            $clubMemberSalutationMap = ClubMemberSalutation::get()->exclude('ShowInFrontEnd', '0')->map('ID', 'Title');
        } else {
            $clubMemberSalutationMap = [
                'Divers' => 'Divers',
                'Frau' => 'Frau',
                'Herr' => 'Herr',
                'Schülerin' => 'Schülerin',
                'Schüler' => 'Schüler'
            ];
        }
        // Prepare todays date
        $today = DBDatetime::now()->Date();

        // List of form fields
        $fields = FieldList::create(
            // Use dot notation for Salutation
            DropdownField::create('MemberSalutationID', _t('Sybeha\Clubmaster\Models\ClubMember.SALUTATION', 'Salutation'), $clubMemberSalutationMap)
                ->setEmptyString(_t('Sybeha\Clubmaster\Models\ClubMember.SELECTONE', '(Select one)')),
            EUNameTextField::create('FirstName', _t('Sybeha\Clubmaster\Models\ClubMember.FIRSTNAME', 'FirstName'))
                ->setAttribute('placeholder', _t('Sybeha\Clubmaster\Models\ClubMember.FIRSTNAME', 'FirstName')),
            EUNameTextField::create('LastName', _t('Sybeha\Clubmaster\Models\ClubMember.LASTNAME', 'LastName'))
                ->setAttribute('placeholder', _t('Sybeha\Clubmaster\Models\ClubMember.LASTNAME', 'LastName')),
            DateField::create('Birthday', _t('Sybeha\Clubmaster\Models\ClubMember.BIRTHDAY', 'Birthday'))
                ->addExtraClass('width_100')->setAttribute('placeholder', $today )->setMinDate('-100 years')->setMaxDate('+0 days'),
            CountryDropdownField::create('Nationality', _t('Sybeha\Clubmaster\Models\ClubMember.NATIONALITY', 'Nationality')
            )->setEmptyString(_t('Sybeha\Clubmaster\Models\ClubMember.SELECTONE', '(Select one)')),
            EUNameTextField::create('Street', _t('Sybeha\Clubmaster\Models\ClubMember.STREET', 'Street'))
                ->setAttribute('placeholder', _t('Sybeha\Clubmaster\Models\ClubMember.STREET', 'Street')),
            EUNameTextField::create('StreetNumber', _t('Sybeha\Clubmaster\Models\ClubMember.STREETNUMBER', 'StreetNumber'))
                ->setAttribute('placeholder', _t('Sybeha\Clubmaster\Models\ClubMember.STREETNUMBER', 'StreetNumber')),
            ZipField::create('Zip', _t('Sybeha\Clubmaster\Models\ClubMember.ZIP', 'Zip'))
                ->setAttribute('placeholder', '12345'),
            EUNameTextField::create('City', _t('Sybeha\Clubmaster\Models\ClubMember.CITY', 'City'))
                ->setAttribute('placeholder', _t('Sybeha\Clubmaster\Models\ClubMember.CITY', 'City')),
            EmailField::create('Email', _t('Sybeha\Clubmaster\Models\ClubMember.EMAIL', 'Email'))
                ->setAttribute('placeholder', 'name@domain.de'),
            TelephoneNumberField::create('Mobil', _t('Sybeha\Clubmaster\Models\ClubMember.MOBIL', 'Mobile'))
                ->setAttribute('placeholder', _t('Sybeha\Clubmaster\Models\ClubMember.MOBIL', 'Mobile'))
                ->setAttribute('required',"required"),
            TelephoneNumberField::create('Phone', _t('Sybeha\Clubmaster\Models\ClubMember.OR', 'or') . ' ' . _t('Sybeha\Clubmaster\Models\ClubMember.PHONE', 'Phone'))
                ->setAttribute('placeholder', _t('Sybeha\Clubmaster\Models\ClubMember.PHONE', 'Phone'))
                ->setAttribute('required',"required"),
            DropdownField::create('MemberTypeID', _t('Sybeha\Clubmaster\Models\ClubMember.TYPE', 'Type'), $clubMemberTypesMap)
                ->setEmptyString(_t('Sybeha\Clubmaster\Models\ClubMember.SELECTONE', '(Select one)')),
            DateField::create('Since', _t('Sybeha\Clubmaster\Models\ClubMember.FROM', 'Member from'))
                ->addExtraClass('width_100')->setValue($today)->setMinDate('-2 years')->setMaxDate('+0 days'),
            // Check box is used to copy content of fields - DO NOT use ->setValue(true) here !
            CheckboxField::create('EqualAddress', _t('Sybeha\Clubmaster\Models\ClubMember.EQUALADDRESS', 'EqualAddress')),
            EUNameTextField::create('AccountHolderFirstName', _t('Sybeha\Clubmaster\Models\ClubMember.ACCOUNTHOLDERFIRSTNAME', 'AccountHolderFirstName')),
            EUNameTextField::create('AccountHolderLastName', _t('Sybeha\Clubmaster\Models\ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName')),
            EUNameTextField::create('AccountHolderStreet', _t('Sybeha\Clubmaster\Models\ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet')),
            EUNameTextField::create('AccountHolderStreetNumber', _t('Sybeha\Clubmaster\Models\ClubMember.ACCOUNTHOLDERSTREETNUMBER', 'AccountHolderStreetNumber')),
            ZipField::create('AccountHolderZip',_t('Sybeha\Clubmaster\Models\ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip')),
            EUNameTextField::create('AccountHolderCity',_t('Sybeha\Clubmaster\Models\ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity')),
            IbanField::create('Iban', _t('Sybeha\Clubmaster\Models\ClubMember.IBAN', 'Iban'))
                ->setAttribute('placeholder', 'DE12500105170648489890'),
                //->setAttribute('data-rule-iban','true')
                //->addExtraClass("text"),
            BicField::create('Bic', _t('Sybeha\Clubmaster\Models\ClubMember.BIC', 'Bic'))
                ->setAttribute('placeholder', "VOBADEXX")
                //->addExtraClass("text")
        );

        // List of action fields
        $actions = new FieldList(
            FormAction::create('doEnroll')
                ->setTitle(_t('Sybeha\Clubmaster\Pages\EnrollPage.ENROLL', 'Enroll'))
                ->setUseButtonTag(true)
        );

        // List of required fields for the form
        $required = new RequiredFields([
            //'MemberSalutationID',
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
            //'MemberTypeID',
            'Since',
            'AccountHolderFirstName',
            'AccountHolderLastName',
            'AccountHolderStreet',
            'AccountHolderStreetNumber',
            'AccountHolderZip',
            'AccountHolderCity',
            'Iban',
            'Bic'
            ]);

        // Be careful validation might fail if the frontend JavaScript does not copy all required fields
        $form = new Form($this, 'EnrollForm', $fields, $actions, $required);
        // Add a template
        $form->setTemplate('EnrollForm');
        $form->setFormMethod('POST', true);

        return $form;
    }

    /**
     * Handling submission specified within form action list
     *
     * @param FieldList $data
     * @param Form $form
     * @return void
     */
    public function doEnroll($data, $form)
    {
        Injector::inst()->get(LoggerInterface::class)->info('EnrollPageController - doEnroll() locale = ' . i18n::get_locale());
        Injector::inst()->get(LoggerInterface::class)->info('EnrollPageController - doEnroll() data Birthday = ' . $data['Birthday']);

        // Create a ClubMemberPending DataObject
        $clubMemberPending = new ClubMemberPending();

        // Init the ClubMemberPending with form data
        $form->saveInto($clubMemberPending);

        // Create a DBDate object to transform given form birthday date (string) got the wrong format 1970-01-03
        $dbDate = $clubMemberPending->dbObject('Birthday');
        // Use strftime to utilize locale
        $birthday = strftime('%d.%m.%Y', $dbDate->getTimestamp());

        // Replace special characters to avoid filename issues
        setlocale( LC_ALL, "de_DE.utf8");
        $fn = iconv('utf-8', 'ascii//TRANSLIT', $clubMemberPending->FirstName);
        $ln = iconv('utf-8', 'ascii//TRANSLIT', $clubMemberPending->LastName);
        Injector::inst()->get(LoggerInterface::class)->debug('EnrolPageController - doEnroll()  special characters first name = ' . $fn . ' last name ' . $ln);

        // Get the path for the folder and add a filename like LH_03.01.1970_dd.mm.YYYY_HH_MM_SS.antrag
        // uppercase first and last name (first 2 characters only - they should be but you never know)
        $name = strtoupper($data['FirstName'][0]) . strtoupper($data['LastName'][0]) . '_' . $birthday . '_' . date('d.m.Y_H_i_s') . '.antrag';

        // Get the desired folder to store the serialized object
        $folder = $this->Folder();

        // Files property Filename contains (optional) preceding folder
        $filename = $folder->Name . DIRECTORY_SEPARATOR . $name;

        Injector::inst()->get(LoggerInterface::class)->debug('EnrolPageController - doEnroll()  path = ' . $filename);

        // Add path to object
        $clubMemberPending->SerializedFileName = $filename;

        // Serialize object safely
        $serialized = base64_encode(serialize($clubMemberPending));

        $file = new File();
        // Store the serialized file
        $file->setFromString($serialized, $filename);
        $fileId = $file->write();
        // Unpublish to hide from outside world
        $file->doUnpublish();

        if ($fileId) {
            $typeName = ClubMemberType::get()->byID($clubMemberPending->MemberTypeID)->TypeName;
            $salutationName = ClubMemberSalutation::get()->byID($clubMemberPending->MemberSalutationID)->SalutationName;
            //Injector::inst()->get(LoggerInterface::class)->debug('EnrollPageController - doEnroll()  type name = ' . $typeName . ' salutation = ' . $salutationName);

            // Send an E-Mail
            $email = Email::create()
                //setFrom('JIM .e.V')
                ->setTo($data['Email'])
                ->setData($clubMemberPending)
                ->addData('TypeName', $typeName)
                ->addData('SalutationName',$salutationName)
                ->setSubject('Anmeldung bei Jim e.V.')
                ->setHTMLTemplate('EMail\EnrollMail');

            // Get the session object
            $session = $this->getRequest()->getSession();
            // Add object
            $session->set('ClubMemberPending', $serialized);

            // @todo: Meaningful E-Mail validation
            /*if ($email->send()) {
                // Nothing
            } else {
                // there may have been 1 or more failures
                $session->set('Error', 'Fehler');
            }*/
        }
        //return $this->redirectBack();
        Injector::inst()->get(LoggerInterface::class)
            ->debug('EnrollPageController - doEnroll()  redirect to = ' . EnrollSuccessPage::get()->First()->Link());

        return $this->redirect(EnrollSuccessPage::get()->First()->Link());
    }

    public function init()
    {
        parent::init();
        $theme = $this->themeDir();
        // Use Theme TODO: Make configurable
        Requirements::javascript('//ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
        //Requirements::javascript('silverstripe/admin:thirdparty/jquery/jquery.js');
        // Use Theme TODO: Make configurable
        //Front-End validation
        Requirements::javascript('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js');
        Requirements::javascript('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/additional-methods.min.js');
        Requirements::javascript('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/localization/messages_de.min.js');
        Requirements::javascript('lhasselb/clubmaster:javascript/enroll.js');
        Requirements::css('lhasselb/clubmaster:css/enroll.css');
    } //init
} //eof
