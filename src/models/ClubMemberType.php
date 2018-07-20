<?php

namespace SYBEHA\Clubmaster\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

use SYBEHA\Clubmaster\Models\ClubMember;

/**
 * Class ClubMemberType
 *
 * @package SYBEHA\Clubmaster\Models
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

    private static $db = [
        'TypeName' => 'Varchar(255)'
    ];

    private static $has_many = [
        'ClubMembers' => ClubMember::class
    ];

    private static $summary_fields = [
        'TypeName'
    ];

    private static $searchable_fields = [];

    public function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['TypeName'] = _t('SYBEHA\Clubmaster\Models\ClubMemberType.TYPENAME', 'type name');
        $labels['ClubMembers'] = _t('SYBEHA\Clubmaster\Models\ClubMemberType.CLUBMEMBERS', 'ClubMembers');
        return $labels;
    }

    public function getCMSFields()
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
