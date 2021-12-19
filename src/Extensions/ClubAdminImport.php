<?php

namespace Sybeha\Clubmaster\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Member;

/**
 * Class ClubAdminImport
 *
 * @package  Sybeha\Clubmaster\Extensions
 * @property \SilverStripe\Admin\ModelAdmin $owner
 */
class ClubAdminImport extends Extension
{
    /**
     * Prevent existing import form from showing up
     *
     * @param Form as reference
     */
    public function updateImportForm(&$form)
    {
        if (!Permission::checkMember(Member::currentUser(), 'CMS_ACCESS_LeftAndMain')) {
            $form = null;
        } else {
            // Remove checkbox
            $form->Fields()->removeByName('EmptyBeforeImport');
        }
    }
}
