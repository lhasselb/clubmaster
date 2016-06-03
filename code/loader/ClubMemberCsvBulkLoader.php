<?php

/**
 * Imports clubmember records, and checks/updates duplicates based on
 * FirstName + LastName + Birthday.
 *
 * @package clubmaster
 * @subpackage loader
 */
class ClubMemberCsvBulkLoader extends CsvBulkLoader
{

    /**
     * Overwrite processRecord
     */
    public function processRecord($record, $columnMap, &$results, $preview = false)
    {

        /*foreach ($record as $key => $value) {
            SS_Log::log('key='.$key.' value='.$value,SS_Log::WARN);
        }*/

        //skip if required data is not present
        if (!$this->hasRequiredData($record)) {
            //$results->addSkipped("Required data is missing.");
            return;
        }

        // Add information about creation type
        $record['CreationType'] = 'Import';

        // Verify equal address
        if (
            $record['Street'] == $record['AccountHolderStreet'] &&
            $record['StreetNumber'] == $record['AccountHolderStreetNumber'] &&
            $record['Zip'] == $record['AccountHolderZip'] &&
            $record['City'] == $record['AccountHolderCity']
        ) {
            $record['EqualAddress'] = '1';
        } else {
            $record['EqualAddress'] = '0';
        }

        return parent::processRecord($record, $columnMap, $results, $preview);
    }

    /*
     * Using a callback function to  check for unique record
     */
    public $duplicateChecks = array(
        'FirstName' => array(
            'callback' => 'checkFirstLastBirthday'
        )
    );

    /* Callback method to check for FirstName, LastName, & Birthday
     * as unique key for a record
     */
    public function checkFirstLastBirthday($fieldName, $record)
    {

        /*SS_Log::log('fieldName='.$fieldName,SS_Log::WARN);
        foreach ($record as $key => $value) {
            SS_Log::log('key='.$key.' value='.$value,SS_Log::WARN);
        }*/
        $first = $record['FirstName'];
        $last = $record['LastName'];
        $birthday = $record['Birthday'];
        $member = ClubMember::get()->filter(array('FirstName' => $first, 'LastName' => $last, 'Birthday' => $birthday))->First();

        return $member;
    }

    /**
     * Map CSV column name => db column name
     * Map columns to DataObject-properties. If not specified,
     * we assume the first row in the file contains the column headers.
     * The order of your array should match the column order.
     */
    public $columnMap = array(
        'Salutation' => 'Salutation',
        'FirstName' => 'FirstName',
        'LastName' => 'LastName',
        'Birthday' => 'Birthday',
        'Nationality' => 'Nationality',
        'Street' => 'Street',
        'StreetNumber' => 'StreetNumber',
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
        'AccountHolderStreetNumber' => 'AccountHolderStreetNumber',
        'AccountHolderZip' => 'AccountHolderZip',
        'AccountHolderCity' => 'AccountHolderCity',
        'Iban' => 'Iban',
        'Bic' => 'Bic'
    );

    /* Fetch relations with a callback */
    public $relationCallbacks = array(
        'Type.TypeName' => array(
            'relationname' => 'Type',
            'callback' => 'getTypeByTypeName'
        )
    );

    public static function getTypeByTypeName(&$obj, $val, $record)
    {
        $type = ClubMemberType::get()->filter('TypeName', $val)->First();
        return $type;
    }

    /**
     * Generate the information for show spec link
     * @return [type] [description]
     */
    public function getImportSpec()
    {
        //SS_Log::log('getImportSpec()',SS_Log::WARN);
        //$spec = parent::getImportSpec();

        // Use columnMap as white list
        $spec['fields'] = array_keys($this->columnMap);
        $spec['relations'] = array();

        return $spec;
    }

    /**
     * Check if the given mapped record has the required data.
     * @param  array $mappedrecord
     * @return boolean
     */
    protected function hasRequiredData($mappedrecord)
    {
        if (!is_array($mappedrecord) || empty($mappedrecord)) {
            return false;
        } else {
            foreach ($mappedrecord as $key => $value) {
                if (!isset($value) || empty($value)) return false;
            }
        }

        return true;
    }
}
