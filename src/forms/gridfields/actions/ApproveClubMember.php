<?php

namespace SYBEHA\Clubmaster\Forms\Gridfields\Actions;

use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
/* Use Model */
use SYBEHA\Clubmaster\Models\ClubMemberPending;
use SYBEHA\Clubmaster\Models\ClubMember;
/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

/**
 * Gridfield action handler for approving records.
 * Class ApproveClubMember
 *
 * @package SYBEHA\Clubmaster\Forms\Gridfields\Actions;
 */
class ApproveClubMember implements GridField_ColumnProvider, GridField_ActionProvider
{

    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return ['class' => 'grid-field__col-compact'];
    }

    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName == 'Actions') {
            return ['title' => ''];
        }
    }

    public function getColumnsHandled($gridField)
    {
        return ['Actions'];
    }

    /**
     *
     * @param  GridField  $gridField
     * @param  DataObject $record
     * @param  string     $columnName
     * @return string - the HTML for the column
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        //Injector::inst()->get(LoggerInterface::class)
            //->debug('ApproveClubMember - getColumnContent() columnName = ' . $columnName
            //    . ' record = ' . $record);

        // ClubMemberPending only
        $clubMemberPendingClass = get_class(new ClubMemberPending());
        // Injector::inst()->get(LoggerInterface::class)
        //    ->debug('ApproveClubMember - getColumnContent() clubMemberPendingClass = '
        //        . $clubMemberPendingClass);
        if (!$record->canEdit() || $record != $clubMemberPendingClass) {
            return;
        }
        if ($record->isPending()) {
            $field = GridField_FormAction::create(
                $gridField,
                'ApproveMember' . $record->ID,
                _t(
                    'SYBEHA\Clubmaster\Forms\Gridfields\Actions\ApproveClubMember.APPROVE_MEMBER',
                    'Approve member'
                ),
                'approvemember',
                ['RecordID' => $record->ID]
            )
                ->addExtraClass('gridfield-button-activate')
                ->setAttribute(
                    'title',
                    _t(
                        'SYBEHA\Clubmaster\Forms\Gridfields\Actions\ApproveClubMember.APPROVE_MEMBER',
                        'Approve Member request'
                    )
                )
                ->setAttribute('data-icon', 'accept')
                ->setDescription(
                    _t(
                        'SYBEHA\Clubmaster\Forms\Gridfields\Actions\ApproveClubMember.APPROVE_MEMBER',
                        'Approve Member request'
                    )
                );
        }
        return $field->Field();
    }

    public function getActions($gridField)
    {
        return ['approvemember'];
    }

    public function handleAction($gridField, $actionName, $arguments, $data)
    {
        if ($actionName == 'approvemember') {
            $clubMemberPending = ClubMemberPending::get()->byId($arguments['RecordID']);
            if (!$clubMemberPending) {
                return;
            }

            // Move ClubMemberPending to ClubMember
            $clubMemberPending->Pending = 0;
            $clubMemberPending->Active = 1;
            $clubMember = new ClubMember();
            // Add namespaced classname 'SYBEHA\Clubmaster\Models\ClubMember';
            $clubMemberPending->ClassName = $clubMember->getClassName();
            // Add date only if missing !
            if (empty($clubMemberPending->Pending)) {
                Injector::inst()->get(LoggerInterface::class)
                    ->debug('ApproveClubMember - handleAction() date = ' . DBDatetime::now());
                $clubMemberPending->Since = DBDatetime::now();
            }
            $clubMemberPending->write();

            $siteConfig = SiteConfig::current_site_config();
            $sendApprovalMail = $siteConfig->SendApprovalMail; // set in site config
            if ($sendApprovalMail) {
                //Send an E-Mail
                $email = new Email();
                $data = $clubMemberPending->toMAp();
                //foreach ($data as $key => $value) {
                    //Injector::inst()->get(LoggerInterface::class)
                    //    ->debug('ApproveClubMember - handleAction() key = ' . $key . ' value = ' . $value);
                //}
                $email->setTo($clubMemberPending->Email)
                    ->setSubject('Anmeldung bei Jim e.V.')
                    ->setTemplate('ApproveMail')->populateTemplate(new ArrayData($data));
                $email->send();
            }

            // Output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(
                200,
                _t(
                    'SYBEHA\Clubmaster\Forms\Gridfields\Actions\ApproveClubMember.APPROVE_MEMBER_DONE',
                    'Member approved.'
                )
            );
        }
    }
}
