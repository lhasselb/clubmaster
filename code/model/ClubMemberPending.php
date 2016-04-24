<?php

class ClubMemberPending extends ClubMember
{

    private static $summary_fields = array(
        'Salutation',
        'FirstName',
        'LastName',
        'SerializedFileName',
        'FormClaimDate'
    );

    private static $searchable_fields = array();

    public function getFormClaimDate() {
        $date = $this->dateFromFilename($this->owner->SerializedFileName);
        return $date->FormatI18N('%d.%m.%Y %H:%M:%S');
    }

    function getCMSFields()
    {
        //SS_Log::log('getCMSFields() called',SS_Log::WARN);
        $fields = parent::getCMSFields();
        return $fields;
    }

    public function dateFromFilename($filename)
    {
        $date = new SS_DateTime();
        // XX_dd.mm.yyyy_hh_mm_ss.antrag
        if (preg_match('/^([A-Z]{2})_(\d{2})\.(\d{2})\.(\d{4})_(\d{2})_(\d{2})_(\d{2}).antrag$/', $filename, $matches)) {
            $day   = intval($matches[2]);
            $month = intval($matches[3]);
            $year  = intval($matches[4]);
            $hour  = intval($matches[5]);
            $minute  = intval($matches[6]);
            $second  = intval($matches[7]);
            $date->setValue($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second);
            //SS_Log::log('date='.$date->format('d.m.Y H:i:s'),SS_Log::WARN);
            return $date;
        } else {
            return false;
        }
    }

    public function fillPendingMember($data)
    {
        if($data === NULL) return false;
        $this->Salutation = $data->Salutation;
        $this->FirstName = $data->FirstName;
        $this->LastName = $data->LastName;
        $this->Birthday = $data->Birthday;
        $this->Nationality = $data->Nationality;
        $this->Street = $data->Street;
        $this->Streetnumber = $data->Streetnumber;
        $this->Zip = $data->Zip;
        $this->City = $data->City;
        $this->Email = $data->Email;
        $this->Mobil = $data->Mobil;
        $this->Phone = $data->Phone;
        $this->Since = $data->Since;
        $this->AccountHolderFirstName = $data->AccountHolderFirstName;
        $this->AccountHolderLastName = $data->AccountHolderLastName;
        $this->AccountHolderStreet = $data->AccountHolderStreet;
        $this->AccountHolderStreetnumber = $data->AccountHolderStreetnumber;
        $this->AccountHolderZip = $data->AccountHolderZip;
        $this->AccountHolderCity = $data->AccountHolderCity;
        $this->Iban = $data->Iban;
        $this->Bic = $data->Bic;
        $this->AccountHolderZip = $data->AccountHolderZip;
        $this->CreationType = 'Formular (Internet)';
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
