<?php

namespace Sybeha\Clubmaster\Pages;

use PageController;

class EnrollPageTemplateController extends PageController
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
        $today = SS_Datetime::now()->FormatI18N("%d.%m.%Y");

        $fields = FieldList::create(
            DropdownField::create(
                'Salutation',
                'Anrede',
                //singleton('ClubMember')->dbObject('Salutation')->enumValues()
                array('Frau'=>'Frau','Herr'=>'Herr','Schülerin'=>'Schülerin','Schüler'=>'Schüler')
            ),
            TextField::create('FirstName', _t('ClubMember.FIRSTNAME', 'FirstName'))
                ->setAttribute('placeholder', 'Vorname'),
            TextField::create('LastName', _t('ClubMember.LASTNAME', 'LastName'))
                ->setAttribute('placeholder', 'Nachname'),
            DateField::create('Birthday', _t('ClubMember.BIRTHDAY', 'Birthday'))
                ->setAttribute('placeholder', $today)
                ->setAttribute('data-date-format', 'DD.MM.YYYY'),
            CountryDropdownField::create('Nationality', _t('ClubMember.NATIONALITY', 'Nationality')),
            TextField::create('Street', _t('ClubMember.STREET', 'Street'))
                ->setAttribute('placeholder', 'Straße'),
            TextField::create('StreetNumber', _t('ClubMember.STREETNUMBER', 'StreetNumber'))
                ->setAttribute('placeholder', 'Hausnummer'),
            ZipField::create('Zip', _t('ClubMember.ZIP', 'Zip'))
                ->setAttribute('placeholder', '12345'),
            TextField::create('City', _t('ClubMember.CITY', 'City'))
                ->setAttribute('placeholder', 'Wohnort'),
            EmailField::create('Email', _t('ClubMember.EMAIL', 'Email'))
                ->setAttribute('placeholder', 'name@domain.de'),
            TextField::create('Mobil', _t('ClubMember.MOBIL', 'Mobil'))
                ->setAttribute('placeholder', 'Handynummer'), //PhoneNumberField
            TextField::create('Phone', _t('ClubMember.PHONE', 'Phone'))
                ->setAttribute('placeholder', 'Telefonnummer'), //PhoneNumberField
            DropdownField::create(
                'TypeID',
                'Mitgliedstyp',
                ['Vollverdiener'=>'Vollverdiener','Student / Azubi / Schüler'=>'Student / Azubi / Schüler']
            ),
            //->setSource(ClubMemberType::get()->map('ID', 'TypeName')),
            DateField::create('Since', 'Mitglied ab')->setValue(SS_Datetime::now()->FormatI18N('%d.%m.%Y')),
            CheckboxField::create('EqualAddress', _t('ClubMember.EQUALADDRESS', 'EqualAddress'))->setValue(true),
            TextField::create(
                'AccountHolderFirstName',
                _t(
                    'ClubMember.ACCOUNTHOLDERFIRSTNAME',
                    'AccountHolderFirstName'
                )
            ),
            TextField::create('AccountHolderLastName', _t('ClubMember.ACCOUNTHOLDERLASTNAME', 'AccountHolderLastName')),
            TextField::create('AccountHolderStreet', _t('ClubMember.ACCOUNTHOLDERSTREET', 'AccountHolderStreet')),
            TextField::create(
                'AccountHolderStreetNumber',
                _t(
                    'ClubMember.ACCOUNTHOLDERSTREETNUMBER',
                    'AccountHolderStreetNumber'
                )
            ),
            ZipField::create('AccountHolderZip', _t('ClubMember.ACCOUNTHOLDERZIP', 'AccountHolderZip')),
            TextField::create('AccountHolderCity', _t('ClubMember.ACCOUNTHOLDERCITY', 'AccountHolderCity')),
            IbanField::create('Iban', _t('ClubMember.IBAN', 'Iban'))
                ->setAttribute('placeholder', "DE12500105170648489890")->addExtraClass("text"),
            BicField::create('Bic', _t('ClubMember.BIC', 'Bic'))
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
        $clubMember = new ClubMemberPending();
        // Save data into object
        $form->saveInto($clubMember);
        // Serialize object safely
        $serialized = base64_encode(serialize($clubMember));
        // Get the desired folder to store the serialized object
        $folder = $this->Folder();
        // Get the path for the folder and add a filename
        $path = $folder->getFullPath() . $data['FirstName'][0] . $data['LastName'][0] . '_'
            . $data['Birthday'] . '_' . date('d.m.Y_H_i_s') . '.antrag';
        //SS_Log::log("path=".$path,SS_Log::WARN);
        /* Store the object at calculated path
         * If filename does not exist, the file is created. Otherwise,
         * the existing file is overwritten, unless the FILE_APPEND flag is set.
         */
        file_put_contents($path, $serialized);

        //Send an E-Mail
        $email = new Email();
        $email->setTo($data['Email'])->setSubject('Anmeldung bei Jim e.V.')
            ->setTemplate('EnrollMail')->populateTemplate(new ArrayData($data));
        $email->send();

        //return $this->redirectBack();
        //SS_Log::log(EnrollSuccessPage::get()->First()->Link(),SS_Log::WARN);
        Session::set('Data', $data);
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
        Requirements::javascript('mysite/javascript/jquery-validate/jquery.validate.js');
        Requirements::javascript('mysite/javascript/jquery-validate/additional-methods.js');
        Requirements::javascript('mysite/javascript/jquery-validate/localization/messages_de.js');

        // eonasdan Datetimepicker
        Requirements::css(
            $theme.'/javascript/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css'
        );
        Requirements::javascript($theme.'/javascript/moment/min/moment-with-locales.js');
        Requirements::javascript(
            $theme.'/javascript/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js'
        );
        if (method_exists(Requirements::backend(), 'add_dependency')) {
            Requirements::backend()
                ->add_dependency(
                    'mysite/javascript/Enroll.js',
                    $theme.'/javascript/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js'
                );
        }
    } //init
} //eof
