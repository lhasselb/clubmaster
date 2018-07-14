<?php

namespace SYBEHA\Clubmaster\Reports;

use SilverStripe\Reports\Report;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SYBEHA\Clubmaster\Models\ClubMember;

/**
 * Class ClubMemberReport
 * @package SYBEHA\Clubmaster\Reports
 */
class ClubMemberReport extends Report
{
    // the name of the report
    public function title()
    {
        return 'Alle Mitglieder';
    }

    // what we want the report to return
    public function sourceRecords($params = null)
    {
        return ClubMember::get()->sort('Since');
    }

    // which fields on that object we want to show
    public function columns()
    {
        $fields = array(
        'Salutation' => 'Salutation',
        'FirstName' => 'FirstName',
        'LastName' => 'LastName',
        'Birthday' => 'Birthday',
        'Nationality' => 'Nationality',
        'Street' => 'Street',
        'StreetNumber' => 'StreetNumber',
        'Zip' => 'Zip',
        'City' => 'City',
        'Since' => 'Date',
        'Insurance' => 'Insurance',
        'Age' => 'Age', // Calculated
        'Sex' => 'Sex', // Calculated
        'ExportType' => 'ClubMemberType',
        'CreationType' => 'CreationType', // Distinguish Formular,Import,Händisch
        'MandateReference' => 'MandateReference' // max 35 char. (A-z0-9) TODO: has_one? (Multiple members might share one)
        );

        return $fields;
    }

    public function getReportField()
    {
        $gridField = parent::getReportField();
        
        //$gridField->setModelClass('');
        $gridConfig = $gridField->getConfig();
        //SS_Log::log('gridConfig='.$gridConfig,SS_Log::WARN);
        $gridConfig->getComponentByType(GridFieldPaginator::class)->setItemsPerPage(500);
        
        //$gridConfig->removeComponentsByType('GridFieldPrintButton');
        //$gridConfig->removeComponentsByType('GridFieldExportButton');
        
        /*$gridConfig->addComponents (
            new GridFieldPrintAllButton('buttons-after-left'),
            new GridFieldExportAllButton('buttons-after-left')
        );*/
        
        return $gridField;
    }
}
