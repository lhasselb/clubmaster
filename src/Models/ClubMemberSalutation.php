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
 *
 * @package Sybeha\Clubmaster
 * @subpackage Model
 * @author Lars Hasselbach <lars.hasselbach@gmail.com>
 * @since 13.11.2021
 * @copyright 2021 [Sybeha]
 * @license see license file in modules root directory
 */
class ClubMemberSalutation extends DataObject
{
    /*
     * Important: Please note: It is strongly recommended to define a table_name for all namespaced models.
     * Not defining a table_name may cause generated table names to be too long
     * and may not be supported by your current database engine.
     * The generated naming scheme will also change when upgrading to SilverStripe 5.0 and potentially break.
     */
    private static $table_name = 'ClubMemberSalutation';

    private static $db = [
        'SalutationName' => 'Varchar(255)',
        'ShowInFrontEnd' => 'Boolean(0)',
        'Gender' => 'Enum(array("D","W","M"))'
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
        'SalutationName',
        'ShowInFrontEnd'
    ];

    private static $searchable_fields = [];

    private static $default_sort = 'SalutationName ASC';

    public function fieldLabels($includerelations = true)
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['SalutationName'] = _t('Sybeha\Clubmaster\Models\ClubMemberSalutation.SALUTATIONNAME', 'Salutation name');
        $labels['ClubMembers'] = _t('Sybeha\Clubmaster\Models\ClubMemberSalutation.CLUBMEMBERS', 'Club members');
        $labels['ShowInFrontEnd'] = _t('Sybeha\Clubmaster\Models\ClubMemberSalutation.SHOW_IN_FRONTEND', 'Show in frontend');
        $labels['Gender'] = _t('Sybeha\Clubmaster\Models\ClubMemberSalutation.GENDER', 'Gender');
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
        $defaultSalutation = ["Divers" => "d", "Frau" => "w","Herr" => "m","Schülerin" => "w","Schüler" => "m"];
        foreach ($defaultSalutation as $currentSalutation => $currentGender) {
            // Does it exist ?
            if (!$salutation = ClubMemberSalutation::get()->filter('SalutationName', $currentSalutation)->first()) {
                // No: Add db row
                $salutation = ClubMemberSalutation::create(["SalutationName" => $currentSalutation,"Gender" => $currentGender,"ShowInFrontEnd" => "1"])->write();
                Injector::inst()->get(LoggerInterface::class)
                    ->debug('ClubMemberSalutation - requireDefaultRecords()' . ' created salutation  = ' .
                        ClubMemberSalutation::get()->byID($salutation)->SalutationName);
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
        return $this->SalutationName;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $clubMemberSalutations = array_fill(0,sizeof(ClubMemberSalutation::get()),ClubMemberSalutation::get());
        /*foreach (ClubMemberSalutation::get() as $clubMemberSalutation) {
            Injector::inst()->get(LoggerInterface::class)
                ->debug('ClubMemberSalutation - onBeforeWrite()' . ' salutation  = ' . $clubMemberSalutation->SalutationName);
        }*/
        if(in_array($this->SalutationName, $clubMemberSalutations)) {
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
