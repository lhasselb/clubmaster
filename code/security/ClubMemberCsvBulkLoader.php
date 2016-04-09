<?php
/**
 * Imports clubmember records, and checks/updates duplicates based on their
 * 'Email' property.
 *
 * @package clubmaster
 * @subpackage security
 */
class ClubMemberCsvBulkLoader extends CsvBulkLoader {

    /**
     * Map CSV name => internal field / function name
     */
    public $columnMap = array(
        'Salutation' => 'Salutation',
        'FirstName' => 'FirstName',
        'LastName' => 'LastName',
        'Birthday' => 'Birthday',
        'Nationality' => 'Nationality',
        'Street' => 'Street',
        'Streetnumber' => 'Streetnumber',
        'Zip' => 'Zip',
        'City' => 'City',
        'Email' => 'Email',
        'Mobil' => 'Mobil',
        'Phone' => 'Phone',
        'Type' => 'Type.TypeName',
        'Since' => 'Since',
        'AccountHolderFirstName' => 'AccountHolderFirstName',
        'AccountHolderLastName' => 'AccountHolderLastName',
        'AccountHolderStreet' => 'AccountHolderStreet',
        'AccountHolderStreetnumber' => 'AccountHolderStreetnumber',
        'AccountHolderZip' => 'AccountHolderZip',
        'AccountHolderCity' => 'AccountHolderCity',
        'Iban' => 'Iban',
        'Bic' => 'Bic'
   );

    // Do we have valid Emails?
    public $duplicateChecks = array(
        'Email' => 'Email',
    );

    public $relationCallbacks = array(
        'Type.TypeName' => array(
         'relationname' => 'Type',
         'callback' => 'getTypeByTypeName'
        )
    );

   public static function getTypeByTypeName(&$obj, $val, $record) {
      return ClubMemberType::get()->filter('TypeName', $val)->First();
   }

}
