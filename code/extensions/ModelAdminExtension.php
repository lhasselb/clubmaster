
<?php
/**
 * Add clubmaster specific behaviour to ModelAdmin
 *
 * @package clubmaster
 * @subpackage extensions
 */
class ModelAdminExtension extends Extension  {

    public function updateImportForm(&$form) {
        $form->Fields()->removeByName('EmptyBeforeImport');
        //$form->setTemplate('ClubAdmin_ImportSpec');
    }

}
