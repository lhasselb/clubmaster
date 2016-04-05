<?php

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
        if(!$record->canEdit()) return;

        $field = GridField_FormAction::create($gridField, 'ActivateMemberAction'.$record->ID,
            _t("GridFieldActivateClubMemberAction.ACTIVATEMEMBER",'ActivateMember'),
            "activateMemberAction", array('RecordID' => $record->ID)
        )->setAttribute('data-icon', 'accept');

        return $field->Field();
    }

    public function getActions($gridField) {
        return array('activateMemberAction');
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
        if($actionName == 'activateMemberAction') {
            // perform your action here
            $item = $gridField->getList()->byID($arguments['RecordID']);
            if(!$item) {
                return;
            }

            $item->Active = 1;
            $item->save();
            // output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(200, 'Activate Member Action Done.');
        }
    }
}
