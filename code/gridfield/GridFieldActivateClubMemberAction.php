<?php
/**
 * Gridfield action handler for activating/deactivating records.
 *
 * @author Lars Hasselbach
 */
class GridFieldActivateClubMemberAction implements GridField_ColumnProvider, GridField_ActionProvider {

    public function augmentColumns($gridField, &$columns) {

        if(!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnAttributes($gridField, $record, $columnName) {
        return array('class' => 'col-buttons');
    }

    public function getColumnMetadata($gridField, $columnName) {
        if($columnName == 'Actions') {
            return array('title' => '');
        }
    }

    public function getColumnsHandled($gridField) {
        return array('Actions');
    }

    /**
     *
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $columnName
     * @return string - the HTML for the column
     */
    public function getColumnContent($gridField, $record, $columnName) {

        if(!$record->canEdit() || $record != "ClubMember" ) return;
        if(!$record->isActive()) {
            $field = GridField_FormAction::create($gridField, 'ActivateMember'.$record->ID, false,
                "activatemember", array('RecordID' => $record->ID))
            ->addExtraClass('gridfield-button-activate')
            ->setAttribute('title', _t('GridFieldActivateClubMemberAction.ACTIVATEMEMBER',"ActivateMember"))
            ->setAttribute('data-icon', 'decline')
            ->setDescription( _t('GridFieldActivateClubMemberAction.ACTIVATEMEMBER',"ActivateMember"));
        }
        elseif($record->isActive()) {
            $field = GridField_FormAction::create($gridField, 'DeActivateMember'.$record->ID, false,
                "deactivatemember", array('RecordID' => $record->ID))
            ->addExtraClass('gridfield-button-deactivate')
            ->setAttribute('title', _t('GridFieldActivateClubMemberAction.DEACTIVATEMEMBER',"DeActivateMember"))
            ->setAttribute('data-icon', 'accept')
            ->setDescription( _t('GridFieldActivateClubMemberAction.DEACTIVATEMEMBER',"DeActivateMember"));
        }
        return $field->Field();
    }

    public function getActions($gridField) {
        return array('activatemember','deactivatemember');
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data) {

        if($actionName == 'activatemember')
        {
            // perform your action here
            $item = $gridField->getList()->byID($arguments['RecordID']);
            if(!$item) {
                return;
            }
            //SS_Log::log("handleAction item=".$item->FirstName,SS_Log::WARN);
            $item->Active = 1;
            $item->write();
            // output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(200, _t("GridFieldActivateClubMemberAction.ACTIVATEMEMBERDONE",'ActivateMember Done.') );
        }
        elseif($actionName == 'deactivatemember')
        {
            // perform your action here
            $item = $gridField->getList()->byID($arguments['RecordID']);
            if(!$item) {
                return;
            }
            //SS_Log::log("handleAction item=".$item->FirstName,SS_Log::WARN);
            $item->Active = 0;
            $item->write();
            // output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(200, _t("GridFieldActivateClubMemberAction.DEACTIVATEMEMBERDONE",'ActivateMember Done.') );
        }
    }
}
