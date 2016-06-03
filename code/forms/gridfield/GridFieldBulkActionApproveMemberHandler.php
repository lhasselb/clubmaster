<?php

/**
 * Bulk action handler for approving records.
 *
 * @author Lars Hasselbach
 */
class GridFieldBulkActionApproveMemberHandler extends GridFieldBulkActionHandler
{
    /**
     * RequestHandler allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = array('approveMember');

    /**
     * RequestHandler url => action map.
     *
     * @var array
     */
    private static $url_handlers = array(
        'approveMember' => 'approveMember'

    );

    /**
     * Activate the selected records passed from the approve bulk action.
     *
     * @param SS_HTTPRequest $request
     *
     * @return SS_HTTPResponse List of avtivated records ID
     */
    public function approveMember(SS_HTTPRequest $request)
    {
        $ids = array();

        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);
            $record->Pending = 0;
            $record->Active = 1;
            $record->ClassName = 'ClubMember';
            $record->Since = SS_Datetime::now();
            $record->write();
        }

        $response = new SS_HTTPResponse(Convert::raw2json(array(
            'done' => true,
            'records' => $ids,
        )));
        $response->addHeader('Content-Type', 'text/json');

        return $response;
    }
}
