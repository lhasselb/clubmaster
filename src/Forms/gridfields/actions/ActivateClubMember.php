<?php

namespace SYBEHA\Clubmaster\Forms\Gridfields\Actions;

use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Control\Controller;

/* Use Model */
use SYBEHA\Clubmaster\Models\ClubMemberPending;
use SYBEHA\Clubmaster\Models\ClubMember;
/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

/**
 * Gridfield action handler for activating/deactivating records.
 * Class ActivateClubmemberAction
 *
 * @package SYBEHA\Clubmaster\Forms\Gridfields\Actions;
 */
class ActivateClubMember implements GridField_ColumnProvider, GridField_ActionProvider
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
            //->debug('ActivateClubMember - getColumnContent() columnName = ' . $columnName
            //    . ' record = ' . $record);

        // ClubMember only
        $clubMemberClass = get_class(new ClubMember());
        // Injector::inst()->get(LoggerInterface::class)
        //    ->debug('ApproveClubMember - getColumnContent() clubMemberClass = '
        //        . $clubMemberClass);
        if (!$record->canEdit() || $record != $clubMemberClass) {
            return;
        }
        if (!$record->isActive()) {
            $field = GridField_FormAction::create(
                $gridField,
                'ActivateMember' . $record->ID,
                _t(
                    'SYBEHA\Clubmaster\Forms\Gridfields\Actions\ActivateClubMember.ACTIVATE_MEMBER',
                    'Activate member'
                ),
                'activatemember',
                ['RecordID' => $record->ID]
            )
                ->addExtraClass('gridfield-button-activate')
                ->setAttribute(
                    'title',
                    _t(
                        'SYBEHA\Clubmaster\Forms\Gridfields\Actions\ActivateClubMember.ACTIVATE_MEMBER',
                        'Activate member'
                    )
                )
                ->setAttribute('data-icon', 'decline')
                ->setDescription(
                    _t(
                        'SYBEHA\Clubmaster\Forms\Gridfields\Actions\ActivateClubMember.ACTIVATE_MEMBER',
                        'Activate member'
                    )
                );
        } elseif ($record->isActive()) {
            $field = GridField_FormAction::create(
                $gridField,
                'DeActivateMember' . $record->ID,
                _t(
                    'SYBEHA\Clubmaster\Forms\Gridfields\Actions\ActivateClubMember.DE_ACTIVATE_MEMBER',
                    'Deactivate member'
                ),
                'deactivatemember',
                ['RecordID' => $record->ID]
            )
                ->addExtraClass('gridfield-button-deactivate')
                ->setAttribute(
                    'title',
                    _t(
                        'SYBEHA\Clubmaster\Forms\Gridfields\Actions\ActivateClubMember.DE_ACTIVATE_MEMBER',
                        'Deactivate member'
                    )
                )
                ->setAttribute('data-icon', 'accept')
                ->setDescription(
                    _t(
                        'SYBEHA\Clubmaster\Forms\Gridfields\Actions\ActivateClubMember.DE_ACTIVATE_MEMBER',
                        'Deactivate member'
                    )
                );
        }
        return $field->Field();
    }

    public function getActions($gridField)
    {
        return ['activatemember', 'deactivatemember'];
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {

        if ($actionName == 'activatemember') {
            // Perform your action here
            $item = $gridField->getList()->byID($arguments['RecordID']);
            if (!$item) {
                return;
            }
            $item->Active = 1;
            $item->write();
            // Output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(
                200,
                _t(
                    'SYBEHA\Clubmaster\Forms\Gridfields\Actions\ActivateClubMember.ACTIVATE_MEMBER_DONE',
                    'Member activated.'
                )
            );
        } elseif ($actionName == 'deactivatemember') {
            // Perform your action here
            $item = $gridField->getList()->byID($arguments['RecordID']);
            if (!$item) {
                return;
            }

            $item->Active = 0;
            $item->write();
            // Output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(
                200,
                _t(
                    'SYBEHA\Clubmaster\Forms\Gridfield\GridFieldActivateClubMemberAction.DE_ACTIVATE_MEMBER_DONE',
                    'Member deactivated.'
                )
            );
        }
    }
}
