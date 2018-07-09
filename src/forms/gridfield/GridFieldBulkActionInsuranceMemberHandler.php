<?php

namespace Sybeha\clubmaster\forms\gridfield;

use Colymba\BulkManager\BulkAction\Handler;
use Colymba\BulkTools\HTTPBulkToolsResponse;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use Exception;

/**
 * Bulk action handler for adding insurance flag to member records.
 *
 * @author Lars Hasselbach
 */
class GridFieldBulkActionInsuranceMemberHandler extends Handler
{
    /**
     * URL segment used to call this handler
     * If none given, @BulkManager will fallback to the Unqualified class name
     * 
     * @var string
     */
    private static $url_segment = 'insuremember';

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
        '' => 'insureMember'

    );
    /**
     * Front-end label for this handler's action
     * 
     * @var string
     */
    protected $label = 'insureMember';
    /**
     * Front-end icon path for this handler's action.
     * 
     * @var string
     */
    protected $icon = '';
    /**
     * Extra classes to add to the bulk action button for this handler
     * Can also be used to set the button font-icon e.g. font-icon-trash
     * 
     * @var string
     */
    protected $buttonClasses = '';
    
    /**
     * Whether this handler should be called via an XHR from the front-end
     * 
     * @var boolean
     */
    protected $xhr = true;
    
    /**
     * Set to true is this handler will destroy any data.
     * A warning and confirmation will be shown on the front-end.
     * 
     * @var boolean
     */
    protected $destructive = false;
    /**
     * Return i18n localized front-end label
     *
     * @return array
     */
    public function getI18nLabel()
    {
        return _t('ClubAdmin.GRIDFIELDBULKDROPDOWNINSURANCE', $this->getLabel());
    }

    /**
     * Activate the selected records passed from the approve bulk action.
     *
     * @param HTTPRequest $request
     *
     * @return HTTPBulkToolsResponse List of avtivated records ID
     */
    public function insureMember(HTTPRequest $request)
    {
        $response = new HTTPBulkToolsResponse(true, $this->gridField);
        try {
            $ids = array();
            foreach ($this->getRecords() as $record) {
                array_push($ids, $record->ID);
                $record->Insurance = 1;
                $record->write();
            }
            $doneCount = count($response->getSuccessRecords());
            $message = sprintf(
                'BLSV von %1$d gesetzt.',
                $doneCount
            );
            $response->setMessage($message);
        } catch (Exception $ex) {
            $response->setStatusCode(500);
            $response->setMessage($ex->getMessage());
        }
        return $response;
    }
}
