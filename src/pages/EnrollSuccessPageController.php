<?php

namespace SYBEHA\Clubmaster\Pages;

use PageController;

/**
 * Enroll success page template controller
 * Class EnrollPageSuccessController
 *
 * @package SYBEHA\Clubmaster\Pages
 */
class EnrollPageSuccessController extends PageController
{
    /**
     * An array of actions that can be accessed via a request. Each array element should be an action name, and the
     * permissions or conditions required to allow the user to access it.
     *
     * <code>
     * [
     *     'action', // anyone can access this action
     *     'action' => true, // same as above
     *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
     *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
     * ];
     * </code>
     *
     * @var array
     */
    private static $allowed_actions = [];

    public function init()
    {
        parent::init();
    }
}