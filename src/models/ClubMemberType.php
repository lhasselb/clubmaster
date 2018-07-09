<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

/**
 * ClubMemberType object
 *
 * @package clubmaster
 * @subpackage models
 */
class ClubMemberType extends DataObject
{
    /*
     * Important: Please note: It is strongly recommended to define a table_name for all namespaced models.
     * Not defining a table_name may cause generated table names to be too long
     * and may not be supported by your current database engine.
     * The generated naming scheme will also change when upgrading to SilverStripe 5.0 and potentially break.
     */
    private static $table_name = 'ClubMemberType';
    private static $db = array(
        'TypeName' => 'Varchar(255)'
    );

    private static $has_many = array(
        'ClubMembers' => 'ClubMember'
    );

    private static $summary_fields = array(
        'TypeName'
    );

    private static $searchable_fields = array();

    function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['TypeName'] = _t('ClubMemberType.TYPENAME', 'TypeName');
        $labels['ClubMembers'] = _t('ClubMemberType.CLUBMEMBERS', 'ClubMembers');
        return $labels;
    }

    function getCMSFields()
    {
        $fields = parent::getCMSFields();

        return $fields;
    }

    public function getTitle()
    {
        return $this->TypeName;
    }

    // Only clubadmins are allowed
    public function canView($member = null)
    {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    // Only clubadmins are allowed
    public function canEdit($member = null)
    {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }

    // Only admins (Group Administrators) are allowed
    public function canDelete($member = null)
    {
        return Permission::check('CMS_ACCESS_LeftAndMain', 'any', $member);
    }

    // Only clubadmins are allowed
    public function canCreate($member = null, $context = array())
    {
        return Permission::check('CMS_ACCESS_ClubAdmin', 'any', $member);
    }
}
