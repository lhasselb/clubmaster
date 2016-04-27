<?php
/**
 * Bulk action handler for activating records.
 *
 * @author Lars Hasselbach
 */
class GridFieldBulkActionActivateMemberHandler extends GridFieldBulkActionHandler
{
    /**
     * RequestHandler allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = array('activateMember','deactivateMember');

    /**
     * RequestHandler url => action map.
     *
     * @var array
     */
    private static $url_handlers = array(
    'activateMember' => 'activateMember',
    'deactivateMember' => 'deactivateMember'
    );

    /**
     * Activate the selected records passed from the activate bulk action.
     *
     * @param SS_HTTPRequest $request
     *
     * @return SS_HTTPResponse List of avtivated records ID
     */
    public function activateMember(SS_HTTPRequest $request)
    {
        $ids = array();

        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);
            $record->Active = 1;
            $record->write();
        }

        $response = new SS_HTTPResponse(Convert::raw2json(array(
            'done' => true,
            'records' => $ids,
        )));
        $response->addHeader('Content-Type', 'text/json');

        return $response;
    }

    /**
     * De-Activate the selected records passed from the activate bulk action.
     *
     * @param SS_HTTPRequest $request
     *
     * @return SS_HTTPResponse List of avtivated records ID
     */
    public function deactivateMember(SS_HTTPRequest $request)
    {
        $ids = array();

        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);
            $record->Active = 0;
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
