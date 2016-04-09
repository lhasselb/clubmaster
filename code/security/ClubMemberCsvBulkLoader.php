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

   public $relationCallbacks = array(
      'Type.TypeName' => array(
         'relationname' => 'Type',
         'callback' => 'getTypeByTypeName'
      )
   );

   public static function getTypeByTypeName(&$obj, $val, $record) {
      return ClubMemberType::get()->filter('TypeName', $val)->First();
   }
    /**
     * @var array Array of {@link ClubMemberType} records. Import into a specific type.
     *  Is overruled by any "ClubMemberType" columns in the import.
     */
    protected $types = array();

/*
    public function __construct($objectClass = null) {
        if(!$objectClass) $objectClass = 'ClubMember';
        parent::__construct($objectClass);
    }
*/
    // Do we have valid Emails?
    public $duplicateChecks = array(
        'Email' => 'Email',
    );

/*
    public function processRecord($record, $columnMap, &$results, $preview = false) {      
        $objID = parent::processRecord($record, $columnMap, $results, $preview);
        $_cache_typeByCode = array();
        foreach ($record as $key => $value) {
            SS_Log::log("key=".$key." value".$value,SS_Log::WARN);
        }
        // Add to predefined types
        $clubmember = DataObject::get_by_id($this->objectClass, $objID);
        SS_Log::log("clubmember type=".$clubmember->TypeID,SS_Log::WARN);
        foreach($this->types as $type) {
            $clubmember->Type = $type->ID;
        }

        // Add to types defined in CSV
        if(isset($record['Type']) && $record['Type']) {         
            $typeCodes = explode(',', $record['Type']);
            foreach($typeCodes as $typeCode) {
                $typeCode = Convert::raw2url($typeCode);
                if(!isset($_cache_typeByCode[$typeCode])) {
                    $type = ClubMemberType::get()->filter('TypeName', $typeCode)->first();
                    //Create a new one 
                    if(!$type) {
                        $type = new ClubMemberType();
                        $type->TypeName = $typeCode;
                        $type->write();
                    }
                    $clubmember->TypeID = $type->ID;
                    $_cache_typeByCode[$typeCode] = $type->ID;
                }
            }
        }

        $clubmember->destroy();
        unset($clubmember);

        return $objID;
    }
*/
    /**
     * @param Array $types
     */
    public function setTypes($types) {
        $this->types = $types;
    }

    /**
     * @return Array
     */
    public function getTypes() {
        return $this->types;
    }
}
