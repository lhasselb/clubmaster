<?php

namespace Sybeha\Clubmaster\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

use Sybeha\Clubmaster\Models\ClubMember;
/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;
/**
 * Class ClubMemberType
 *
 * @package Sybeha\Clubmaster\Models
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
        'TypeName' => 'Varchar(255)',
        'ShowInFrontEnd' => 'Boolean(0)'
    ];

    private static $has_many = [
        'ClubMembers' => ClubMember::class
    ];

    /**
     * Set defaults
     *
     * @var array
     */
    private static $defaults = [
        'ShowInFrontEnd' => '0'
    ];

    private static $summary_fields = [
        'TypeName',
        'ShowInFrontEnd'
    ];

    private static $searchable_fields = [];

    private static $default_sort = 'TypeName ASC';

    public function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['TypeName'] = _t('Sybeha\Clubmaster\Models\ClubMemberType.TYPENAME', 'Type name');
        $labels['ClubMembers'] = _t('Sybeha\Clubmaster\Models\ClubMemberType.CLUBMEMBERS', 'Club members');
        $labels['ShowInFrontEnd'] = _t('Sybeha\Clubmaster\Models\ClubMemberType.SHOW_IN_FRONTEND', 'Show in frontend');
        return $labels;
    }

    /**
     * @see Good example of complex FormField building: SiteTree::getCMSFields()
     *
     * @return FieldList Returns a TabSet for usage within the CMS - don't use for frontend forms.
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        return $fields;
    }

    /**
     * Add default records to database. This function is called whenever the
     * database is built, after the database tables have all been created. Overload
     * this to add default records when the database is built, but make sure you
     * call parent::requireDefaultRecords().
     *
     * @uses DataExtension->requireDefaultRecords()
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        // Map of default records to be created
        $defaultTypes = ['Vollzahlend','Ermäßigt'];
        foreach ($defaultTypes as $currentType) {
            // Does it exist ?
            if (!$type = ClubMemberType::get()->filter('TypeName', $currentType)->first()) {
                // No: Add db row
                $type = ClubMemberType::create(["TypeName" => $currentType,"ShowInFrontEnd" => "1"])->write();
                Injector::inst()->get(LoggerInterface::class)
                    ->debug('ClubMemberType - requireDefaultRecords()' . ' created type  = ' .
                        ClubMemberType::get()->byID($type)->TypeName);
            }
        }
    }

    /**
     * Create a meaningful title
     *
     * @return string Title
     */
    public function getTitle()
    {
        return $this->TypeName;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $clubMemberTypes = array_fill(0,sizeof(ClubMemberType::get()),ClubMemberType::get());
        /*foreach (ClubMemberType::get() as $clubMemberType) {
           Injector::inst()->get(LoggerInterface::class)
               ->debug('ClubMemberType - onBeforeWrite()' . ' type  = ' . $clubMemberType->TypeName);
        }*/
        if(in_array($this->TypeName, $clubMemberTypes)) {
            $this->ShowInFrontEnd = 1;
        }
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
