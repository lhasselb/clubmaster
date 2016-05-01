<?php
/**
 * Gridfield action handler for approving records.
 *
 * @author Lars Hasselbach
 */
class GridFieldApproveClubMemberAction implements GridField_ColumnProvider, GridField_ActionProvider {

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
        //SS_Log::log('record='.$record,SS_Log::WARN);
        if(!$record->canEdit() || $record != 'ClubMemberPending' ) return;
        if($record->isPending())
        {
            $field = GridField_FormAction::create($gridField, 'ApproveMember'.$record->ID, false,
                'approvemember', array('RecordID' => $record->ID))
            ->addExtraClass('gridfield-button-activate')
            ->setAttribute('title', _t('GridFieldApproveClubMemberAction.APPROVEMEMBER','ApproveMember'))
            ->setAttribute('data-icon', 'accept')
            ->setDescription( _t('GridFieldApproveClubMemberAction.APPROVEMEMBER','ApproveMember'));
        }
        return $field->Field();
    }

    public function getActions($gridField) {
        return array('approvemember');
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
        //SS_Log::log('handleAction() called, action name ='.$actionName,SS_Log::WARN);
        if($actionName == 'approvemember')
        {
            $clubMemberPending = ClubMemberPending::get()->byId($arguments['RecordID']);
            if(!$clubMemberPending) {
                return;
            }
            $clubMemberPending->Pending = 0;
            $clubMemberPending->Active = 1;
            $clubMemberPending->ClassName = 'ClubMember';
            $clubMemberPending->Since = SS_Datetime::now();
            $clubMemberPending->write();

            // output a success message to the user
            Controller::curr()->getResponse()->setStatusCode(200, _t('GridFieldApproveClubMemberAction.APPROVEMEMBERDONE','ApproveMember Done.') );
        }
    }
}
