
<?php
/**
 * Add clubmaster specific behaviour to ModelAdmin
 *
 * @package clubmaster
 * @subpackage extensions
 */
class ModelAdminExtension extends Extension  {

    //Disable the clean (delete all) checkbox during import
    public function updateImportForm(&$form) {
        $form->Fields()->removeByName('EmptyBeforeImport');
    }

}
