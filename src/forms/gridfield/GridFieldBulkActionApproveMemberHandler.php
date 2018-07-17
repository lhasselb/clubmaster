<?php

namespace SYBEHA\Clubmaster\Forms\Gridfield;

use Colymba\BulkManager\BulkAction\Handler;
use Colymba\BulkTools\HTTPBulkToolsResponse;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\FieldType\DBDatetime;
use Exception;

/**
 * Bulk action handler for approving member records.
 * Member class will be changed from ClubMemberPending to ClubMember.
 * Class GridFieldBulkActionApproveMemberHandler
 * @package SYBEHA\Clubmaster\Forms\Gridfield
 */
class GridFieldBulkActionApproveMemberHandler extends Handler
{
    /**
     * URL segment used to call this handler
     * If none given, @BulkManager will fallback to the Unqualified class name
     *
     * @var string
     */
    private static $url_segment = 'approvemember';

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
        '' => 'approveMember'

    );

    /**
     * Front-end label for this handler's action
     *
     * @var string
     */
    protected $label = 'approveMember';

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
        return _t('SYBEHA\Clubmaster\Admins\ClubAdmin.GRIDFIELDBULKDROPDOWNAPPROVE', $this->getLabel());
    }

    /**
     * Activate the selected records passed from the approve bulk action.
     *
     * @param HTTPRequest $request
     *
     * @return HTTPBulkToolsResponse List of avtivated records ID
     */
    public function approveMember(HTTPRequest $request)
    {
        $response = new HTTPBulkToolsResponse(true, $this->gridField);
        try {
            $ids = array();
            foreach ($this->getRecords() as $record) {
                array_push($ids, $record->ID);
                $record->Pending = 0;
                $record->Active = 1;
                $record->ClassName = 'ClubMember';
                $record->Since = DBDatetime::now();
                $record->write();
            }
            $doneCount = count($response->getSuccessRecords());
            $message = sprintf(
                'Antrag von %1$d zugestimmt',
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
