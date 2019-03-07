<?php

namespace SYBEHA\Clubmaster\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

use SYBEHA\Clubmaster\Models\ClubMember;
/* Logging */
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;

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

    public function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['TypeName'] = _t('SYBEHA\Clubmaster\Models\ClubMemberType.TYPENAME', 'type name');
        $labels['ClubMembers'] = _t('SYBEHA\Clubmaster\Models\ClubMemberType.CLUBMEMBERS', 'ClubMembers');
        $labels['ShowInFrontEnd'] = _t('SYBEHA\Clubmaster\Models\ClubMemberType.SHOW_IN_FRONTEND', 'Show in frontend');
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

        /**
         * Temporarily hide all link and file tracking tabs/fields in the CMS UI
         * added in SS 4.2 until 4.3 is available
         *
         * Related GitHub issues and PRs:
         *   - https://github.com/silverstripe/silverstripe-cms/issues/2227
         *   - https://github.com/silverstripe/silverstripe-cms/issues/2251
         *   - https://github.com/silverstripe/silverstripe-assets/pull/163
         * */
        $fields->removeByName(['FileTracking', 'LinkTracking']);

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

        $defaultTypes = ['Vollverdiener','Student / Azubi / Schüler'];
        foreach ($defaultTypes as $currentType) {
            if (!$type = ClubMemberType::get()->filter('TypeName', $currentType)->first()) {
                $type = ClubMemberType::create(["TypeName" => $currentType,"ShowInFrontEnd" => "1"])->write();
                Injector::inst()->get(LoggerInterface::class)
                    ->debug('ClubMemberType - requireDefaultRecords()' . ' created type  = ' . ClubMemberType::get()->byID($type)->TypeName);
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
        if ($this->TypeName == 'Vollverdiener' || $this->TypeName == 'Student / Azubi / Schüler') {
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
