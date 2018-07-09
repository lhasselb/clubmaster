<?php

namespace Sybeha\clubmaster\forms\gridfield;

use Colymba\BulkManager\BulkAction\Handler;
use Colymba\BulkTools\HTTPBulkToolsResponse;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use Exception;

/**
 * Bulk action handler for activating records.
 *
 * @author Lars Hasselbach
 */
class GridFieldBulkActionActivateMemberHandler extends Handler
{
    /**
     * URL segment used to call this handler
     * If none given, @BulkManager will fallback to the Unqualified class name
     * 
     * @var string
     */
    private static $url_segment = 'activatemember';

    /**
     * RequestHandler allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = array('activateMember');

    /**
     * RequestHandler url => action map.
     *
     * @var array
     */
    private static $url_handlers = array(
        '' => 'activateMember'
    );

    /**
     * Front-end label for this handler's action
     * 
     * @var string
     */
         protected $label = 'activateMember';
    
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
        return _t('ClubAdmin.GRIDFIELDBULKDROPDOWNACTIVATE', $this->getLabel());
    }

    /**
     * Activate the selected records passed from the activate bulk action.
     *
     * @param HTTPRequest $request
     *
     * @return HTTPBulkToolsResponse List of avtivated records ID
     */
    public function activateMember(HTTPRequest $request)
    {
        $response = new HTTPBulkToolsResponse(true, $this->gridField);
        try {
            $ids = array();
            foreach ($this->getRecords() as $record) {
                array_push($ids, $record->ID);
                $record->Active = 1;
                $record->write();
            }
            $doneCount = count($response->getSuccessRecords());
            $message = sprintf(
                'Mitglied %1$d aktiviert',
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
