<?php

namespace SYBEHA\Clubmaster\Reports;

use SilverStripe\Reports\Report;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SYBEHA\Clubmaster\Models\ClubMember;
use SilverStripe\ORM\ArrayList;

/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

/**
 * Class ClubMemberReport
 *
 * @package SYBEHA\Clubmaster\Reports
 */
class ClubMemberFamilyReport extends Report
{
    // the name of the report
    public function title()
    {
        return 'Mitglieder mit identischem Nachnamen und PLZ ';
    }

    // what we want the report to return
    public function sourceRecords($params = null)
    {
        $family = new ArrayList();
        $equalLastName = new ArrayList();

        // Iterate over sorted list
        foreach (ClubMember::get()->sort('LastName') as $member) {
            // Get members with equal last name
            $list = ClubMember::get()->filter([
                'LastName' => $member->LastName
            ]);

            // More than one member with the same name ?
            if ($list->count() > 1) {
                $equalLastName->push($member);
            }
        }

        // Iterate over list with equal last names
        foreach ($equalLastName as $member) {
            //Injector::inst()->get(LoggerInterface::class)
            //    ->debug('ClubMemberFamilyReport - sourceRecords() lastname = ' . $member->LastName);

            // More than one with the same Zip ?
            $currentList = ClubMember::get()->filter([
                'LastName' => $member->LastName,
                'Zip' => $member->Zip
            ]);

            //Injector::inst()->get(LoggerInterface::class)
            //    ->debug('ClubMemberFamilyReport - sourceRecords() '.$currentList->count().' entries for ' . $member->LastName);
        
            if ($currentList->count() > 1) {
                $family->push($member);
            }
        }


        return $family->sort([
            'LastName' => 'ASC',
            'Zip'=>'ASC',
            'Street' => 'ASC',
            'StreetNumber' => 'ASC'
        ]);
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
        // Distinguish Formular,Import,Händisch
        'CreationType' => 'CreationType',
        // max 35 char. (A-z0-9) @todo: has_one? (Multiple members might share one)
        'MandateReference' => 'MandateReference',
        // TODO: Add link method
        //'Link' => 'Link',
        );

        return $fields;
    }

    public function getReportField()
    {
        $gridField = parent::getReportField();

        //$gridField->setModelClass('');
        $gridConfig = $gridField->getConfig();
        //Injector::inst()->get(LoggerInterface::class)
        //->debug('ClubMemberReport - getReportField() gridConfig = ' . $gridConfig);

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
