<?php

namespace SYBEHA\Clubmaster\Forms\Gridfield;

use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Control\Controller;

/**
 * Gridfield action handler for activating/deactivating records.
 * Class GridFieldActivateClubmemberAction
 *
 * @package SYBEHA\Clubmaster\Forms\Gridfield;
 */
class GridFieldActivateClubMemberAction implements GridField_ColumnProvider, GridField_ActionProvider
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

        if (!$record->canEdit() || $record != 'SYBEHA\Clubmaster\Models\ClubMember') {
            return;
        }
        if (!$record->isActive()) {
            $field = GridField_FormAction::create(
                $gridField,
                'ActivateMember' . $record->ID,
                _t(
                    'SYBEHA\Clubmaster\Forms\Gridfield\GridFieldActivateClubMemberAction.ACTIVATEMEMBER',
                    'Activate member'
                ),
                'activatemember',
                ['RecordID' => $record->ID]
            )
                ->addExtraClass('gridfield-button-activate')
                ->setAttribute(
                    'title',
                    _t(
                        'SYBEHA\Clubmaster\Forms\Gridfield\GridFieldActivateClubMemberAction.ACTIVATEMEMBER',
                        'ActivateMember'
                    )
                )
                ->setAttribute('data-icon', 'decline')
                ->setDescription(
                    _t(
                        'SYBEHA\Clubmaster\Forms\Gridfield\GridFieldActivateClubMemberAction.ACTIVATEMEMBER',
                        'ActivateMember'
                    )
                );
        } elseif ($record->isActive()) {
            $field = GridField_FormAction::create(
                $gridField,
                'DeActivateMember' . $record->ID,
                _t(
                    'SYBEHA\Clubmaster\Forms\Gridfield\GridFieldActivateClubMemberAction.DEACTIVATEMEMBER',
                    'Deactivate member'
                ),
                'deactivatemember',
                ['RecordID' => $record->ID]
            )
                ->addExtraClass('gridfield-button-deactivate')
                ->setAttribute(
                    'title',
                    _t(
                        'SYBEHA\Clubmaster\Forms\Gridfield\GridFieldActivateClubMemberAction.DEACTIVATEMEMBER',
                        'DeActivateMember'
                    )
                )
                ->setAttribute('data-icon', 'accept')
                ->setDescription(
                    _t(
                        'SYBEHA\Clubmaster\Forms\Gridfield\GridFieldActivateClubMemberAction.DEACTIVATEMEMBER',
                        'DeActivateMember'
                    )
                );
        }
        return $field->Field();
    }

    public function getActions($gridField)
    {
        return ['activatemember', 'deactivatemember'];
    }

    public function handleAction($gridField, $actionName, $arguments, $data)
    {

        if ($actionName == 'activatemember') {
            // perform your action here
            $item = $gridField->getList()->byID($arguments['RecordID']);
            if (!$item) {
                return;
            }
            //SS_Log::log('handleAction item='.$item->FirstName,SS_Log::WARN);
            $item->Active = 1;
            $item->write();
            // output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(
                200,
                _t(
                    'SYBEHA\Clubmaster\Forms\Gridfield\GridFieldActivateClubMemberAction.ACTIVATEMEMBERDONE',
                    'ActivateMember Done.'
                )
            );
        } elseif ($actionName == 'deactivatemember') {
            // perform your action here
            $item = $gridField->getList()->byID($arguments['RecordID']);
            if (!$item) {
                return;
            }
            //SS_Log::log('handleAction item='.$item->FirstName,SS_Log::WARN);
            $item->Active = 0;
            $item->write();
            // output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(
                200,
                _t(
                    'SYBEHA\Clubmaster\Forms\Gridfield\GridFieldActivateClubMemberAction.DEACTIVATEMEMBERDONE',
                    'ActivateMember Done.'
                )
            );
        }
    }
}
