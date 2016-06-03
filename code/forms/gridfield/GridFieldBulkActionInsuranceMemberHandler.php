<?php

/**
 * Bulk action handler for approving records.
 *
 * @author Lars Hasselbach
 */
class GridFieldBulkActionInsuranceMemberHandler extends GridFieldBulkActionHandler
{
    /**
     * RequestHandler allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = array('insureMember');

    /**
     * RequestHandler url => action map.
     *
     * @var array
     */
    private static $url_handlers = array(
        'insureMember' => 'insureMember'

    );

    /**
     * Activate the selected records passed from the approve bulk action.
     *
     * @param SS_HTTPRequest $request
     *
     * @return SS_HTTPResponse List of avtivated records ID
     */
    public function insureMember(SS_HTTPRequest $request)
    {
        $ids = array();

        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);
            $record->Insurance = 1;
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
