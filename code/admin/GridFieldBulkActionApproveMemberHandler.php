<?php
/**
 * Bulk action handler for approving records.
 *
 * @author
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
            SS_Log::log('approveMember called',SS_Log::WARN);
        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);
            SS_Log::log('approveMember class='.$record->getClassName,SS_Log::WARN);
            //$record->Active = 1;
            //$record->write();
        }

        $response = new SS_HTTPResponse(Convert::raw2json(array(
            'done' => true,
            'records' => $ids,
        )));
        $response->addHeader('Content-Type', 'text/json');

        return $response;
    }
}
