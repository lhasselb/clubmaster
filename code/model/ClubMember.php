<?php

class ClubMember extends DataObject
{
    private static $db = array(
        'Salutation' => 'Varchar(255)',
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
        'Type' => 'Enum("Limited,Full","Full")',
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
        'Age' => 'Int' //Hidden
    );

    private static $has_one = array(
        'Group' => 'ClubCategory'
    );
/*
    private static $casting = array(
        'Age' => 'Int'
    );
*/
    private function getAge()
    {
        SS_Log::log("name=".$this->LastName,SS_Log::WARN);
        $ago = abs(time() - $this->dbObject('Birthday')->value);
        $span = round($ago/86400/365);
        SS_Log::log("span=".$span,SS_Log::WARN);
        return $span;
    }

    private static $summary_fields = array(
        'FirstName',
        'LastName',
        'Age'
    );

    private static $searchable_fields = array(
        'FirstName',
        'LastName',
        'Age'
    );

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->Age = $this->getAge();
    }

    function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Age');
        return $fields;
    }

}
