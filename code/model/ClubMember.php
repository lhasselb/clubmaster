<?php

class ClubMember extends DataObject
{
    private static $db = array(
        //Anrede, Vorname, Nachname, Geburtsdatum, Staatsangehörigkeit
        //Strasse, Hausnummer,PLZ,Ort, EMAIL, Handy, Telefon
        //Type:Vollverdiener,Schüler/Azubi/Student
        //Eintrittsdatum
        //Kontoinhaber - Vorname,Nachname,Strasse, Hausnummer,PLZ,Ort
        //IBAN,BIC
        'Title' => 'Varchar(255)',
        'FirstName' => 'Varchar(255)',
        'LastName' => 'Text',
        'Birthday' => 'Date',
        'Nationality' => 'Varchar(255)', //CountryDropdownField
        'Street'=>'Varchar(255)',
        'Streetnumber'=>'Int',
        'Since' => 'Date',
        'Active' => 'Boolean',
    );

    private static $has_one = array(
        'ClubCategory' => 'ClubCategory'
    );
}
