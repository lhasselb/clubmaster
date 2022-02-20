<?php

namespace SYBEHA\Clubmaster\Forms\Gridfields\Bulkactions;

use Colymba\BulkManager\BulkAction\Handler;
use Colymba\BulkTools\HTTPBulkToolsResponse;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use Exception;

use SilverStripe\ORM\FieldType\DBDatetime;

use SYBEHA\Clubmaster\Models\ClubMemberPending;
use SYBEHA\Clubmaster\Models\ClubMember;

/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

/**
 * Bulk action handler for approving member records.
 * Member class will be changed from ClubMemberPending to ClubMember.
 * Class ApproveMemberHandler
 *
 * @package SYBEHA\Clubmaster\Forms\Gridfields\Bulkactions
 */
class ApproveMemberHandler extends Handler
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
    private static $allowed_actions = ['approveMember'];

    /**
     * RequestHandler url => action map.
     *
     * @var array
     */
    private static $url_handlers = ['' => 'approveMember'];

    /**
     * Front-end label for this handler's action
     *
     * @var string
     */
    protected $label = 'Approve multiple';

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
        return _t('SYBEHA\Clubmaster\Forms\Gridfields\Bulkactions\ApproveMemberHandler.GRIDFIELD_BULK_DROPDOWN_APPROVE', $this->getLabel());
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
        $records = $this->getRecords();
        $response = new HTTPBulkToolsResponse(true, $this->gridField);

        try {
            foreach ($this->getRecords() as $record) {

                $record->Pending = 0;
                $record->Active = 1;
                $clubMember = new ClubMember();
                // Add namespaced classname 'SYBEHA\Clubmaster\Models\ClubMember';
                $record->ClassName = $clubMember->getClassName();
                // Add date only if missing !
                if (empty($record->Since)) {
                    $record->Since = DBDatetime::now();
                }
                $done = $record->write();
                if ($done) {
                    $response->addSuccessRecord($record);
                } else {
                    $response->addFailedRecord($record, $done);
                }
            }
            $doneCount = count($response->getSuccessRecords());
            //Injector::inst()->get(LoggerInterface::class)->debug('ApproveMemberHandler - approveMember() doneCount = ' . $doneCount);
            $message = sprintf(
                _t('SYBEHA\Clubmaster\Forms\Gridfields\Bulkactions\ApproveMemberHandler.GRIDFIELD_BULK_DROPDOWN_APPROVED', '%s member requests approved'),
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
