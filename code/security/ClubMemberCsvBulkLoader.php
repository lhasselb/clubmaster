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
     * @var array Array of {@link ClubMemberType} records. Import into a specific type.
     *  Is overruled by any "ClubMemberType" columns in the import.
     */
    protected $types = array();

    public function __construct($objectClass = null) {
        if(!$objectClass) $objectClass = 'ClubMember';

        parent::__construct($objectClass);
    }

    // Do we have valid Emails?
    public $duplicateChecks = array(
        'Email' => 'Email',
    );

    public function processRecord($record, $columnMap, &$results, $preview = false) {
        $objID = parent::processRecord($record, $columnMap, $results, $preview);

        $_cache_typeByCode = array();

        // Add to predefined types
        $clubmember = DataObject::get_by_id($this->objectClass, $objID);
        foreach($this->types as $type) {
            // TODO This isnt the most memory effective way to add clubmembers to a type
            $clubmember->Types()->add($type);
        }

        // Add to types defined in CSV
        if(isset($record['Types']) && $record['Types']) {
            $typeCodes = explode(',', $record['Types']);
            foreach($typeCodes as $typeCode) {
                $typeCode = Convert::raw2url($typeCode);
                if(!isset($_cache_typeByCode[$typeCode])) {
                    $type = Type::get()->filter('Code', $typeCode)->first();
                    if(!$type) {
                        $type = new Type();
                        $type->Code = $typeCode;
                        $type->Title = $typeCode;
                        $type->write();
                    }
                    $clubmember->Types()->add($type);
                    $_cache_typeByCode[$typeCode] = $type;
                }
            }
        }

        $clubmember->destroy();
        unset($clubmember);

        return $objID;
    }

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
