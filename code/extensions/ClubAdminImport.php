<?php

class ClubAdminImport extends Extension
{

    /**
     * Prevent existing import form from showing up
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
