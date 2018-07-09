<?php

/**
 * Imports clubmember records, and checks/updates duplicates based on
 * FirstName + LastName + Birthday.
 * To reset index on table use ALTER TABLE clubmember AUTO_INCREMENT = 1
 * 
 * @package clubmaster
 * @subpackage loader
 */

use SilverStripe\Dev\CsvBulkLoader;
/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

class ClubMemberCsvBulkLoader extends CsvBulkLoader
{   
    /**
     * @todo Better messages for relation checks and duplicate detection
     * Note that columnMap isn't used.
     *
     * @param array $record
     * @param array $columnMap
     * @param BulkLoader_Result $results
     * @param boolean $preview
     *
     * @return int
     */
    protected function processRecord($record, $columnMap, &$results, $preview = false)
    {		
		/*foreach ($record as $key => $value) {
			Injector::inst()->get(LoggerInterface::class)->debug('ClubMemberCsvBulkLoader - processRecord()' . ' key='.$key . ' value=' . $value);
        }*/
		
        // Skip if required data is not present
        if (!$this->hasRequiredData($record)) {
            //$results->addSkipped("Required data is missing.");
            return;
        }

        // Add information about creation type
        if (empty($record['CreationType'])) {
            $record['CreationType'] = 'Import';
        }

        // Nationality needs to be strtolower
        if ($record['Nationality']) {
            $record['Nationality'] = strtolower($record['Nationality']);
        }
		
		// Attention 32bit version, e.g. XAMPP (Windows): The valid range of a timestamp is typically from 
		// Fri, 13 Dec 1901 20:45:54 UTC 
		// to 
		// Tue, 19 Jan 2038 03:14:07 UTC. 
		// (These are the dates that correspond to the minimum and maximum values for a 32-bit signed integer.)
		if(2147483647 == PHP_INT_MAX) {
			if ($record['Birthday']) {
				// 1900-01-01
				if($record['Birthday'] < '1901-12-14') 
				{
					$record['Birthday'] = '1901-12-14';
					Injector::inst()->get(LoggerInterface::class)->debug('ClubMemberCsvBulkLoader - Birthday changed to ' . $record['Birthday']);
				}
			}
		}		
        // Verify equal address
        if ($record['Street'] == $record['AccountHolderStreet'] &&
            $record['StreetNumber'] == $record['AccountHolderStreetNumber'] &&
            $record['Zip'] == $record['AccountHolderZip'] &&
            $record['City'] == $record['AccountHolderCity']
        ) {
            $record['EqualAddress'] = '1';
        } else {
            $record['EqualAddress'] = '0';
        }
		
		// TODO: Compare fields of existing members with given fields to evaluate differences  
		// $existingObj = $this->findExistingObject($record, $columnMap);
		
		return parent::processRecord($record, $columnMap, $results, $preview = false);

    }

    /*
     * Using a callback function to  check for unique record
     */
    public $duplicateChecks = [
        'FirstName' => [
            'callback' => 'checkFirstLastBirthday'
        ]
    ];

    /* Callback method to check for FirstName, LastName, & Birthday
     * as unique key for a record
     */
    public function checkFirstLastBirthday($fieldName, $record)
    {
		//Injector::inst()->get(LoggerInterface::class)->debug('ClubMemberCsvBulkLoader - checkFirstLastBirthday(' . fieldName . ',record)');
		/*
		foreach ($record as $key => $value) {
			Injector::inst()->get(LoggerInterface::class)->debug('ClubMemberCsvBulkLoader - processRecord()' . ' key='.$key . ' value=' . $value);
        }*/
        $first = $record['FirstName'];
        $last = $record['LastName'];
        $birthday = $record['Birthday'];
        $member = ClubMember::get()->filter([
            'FirstName' => $first, 
			'LastName' => $last, 
			'Birthday' => $birthday])->First();
		
        return $member;
    }

    /**
     * Map CSV column name => db column name
     * Map columns to DataObject-properties. If not specified,
     * we assume the first row in the file contains the column headers.
     * The order of your array should match the column order.
     */
    public $columnMap = [
        'Salutation' => 'Salutation',
        'NameTitle' => 'NameTitle',
        'FirstName' => 'FirstName',
        'LastName' => 'LastName',
        'CareOf' => 'CareOf',
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
        'AccountHolderTitle' => 'AccountHolderTitle',
        'AccountHolderFirstName' => 'AccountHolderFirstName',
        'AccountHolderLastName' => 'AccountHolderLastName',
        'AccountHolderStreet' => 'AccountHolderStreet',
        'AccountHolderStreetNumber' => 'AccountHolderStreetNumber',
        'AccountHolderZip' => 'AccountHolderZip',
        'AccountHolderCity' => 'AccountHolderCity',
        'Iban' => 'Iban',
        'Bic' => 'Bic',
        //Special
        'Active' => 'Active',
        'Insurance' => 'Insurance',
        'Age' => 'Age',
        'Sex' => 'Sex',
        'SerializedFileName' => 'SerializedFileName',
        //'FormClaimDate',
        'CreationType' => 'CreationType',
        //Pending,
        'MandateReference' => 'MandateReference'
    ];

    /* Fetch relations with a callback */
    public $relationCallbacks = [
        'Type.TypeName' => [
            'relationname' => 'Type',
            'callback' => 'getTypeByTypeName'
        ]
    ];
	
    /**
     * Get the related DataObject
     * @return ClubMemberType
     */
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
        //$spec = parent::getImportSpec();

        // Use columnMap as white list
        $spec['fields'] = array_keys($this->columnMap);
        $spec['relations'] = [];
        return $spec;
    }

    /**
     * Check within processRecord if the given mapped record has the required data.
     * @param  array $mappedrecord
     * @return boolean
     */
    protected function hasRequiredData($mappedrecord)
    {
        if (!is_array($mappedrecord) || empty($mappedrecord)) {
            return false;
        } else {
            foreach ($mappedrecord as $key => $value) {
                //if (!isset($value) || empty($value)) return false;
                // All fields need to be there
                if (!isset($value)) {
                    return false;
                }
                // Minimum requirement for an import record (to detect duplicates): FirstName, LastName and Birthday
                if (($key == 'FirstName' || $key == 'LastName' || $key == 'Birthday') && empty($value)) {
                    return false;
                }
            }
        }

        return true;
    }
}
