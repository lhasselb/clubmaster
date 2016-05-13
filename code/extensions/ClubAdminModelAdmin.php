
<?php
/**
 * Add clubmaster specific behaviour to ModelAdmin
 *
 * @package clubmaster
 * @subpackage extensions
 */
class ModelAdminExtension extends Extension  {

    public function updateImportForm(&$form, &$specHTML) {
        /* Remove checkbox clear all before import */
        $form->Fields()->removeByName('EmptyBeforeImport');
        /* Disable default import form */
        if (!Permission::checkMember(Member::currentUser(), 'CMS_ACCESS_LeftAndMain')) {$form = null;}
    }

}
