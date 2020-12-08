<?php

/**
 * This file contains the "GroupExtension" class.
 *
 * @category SilverStripe_Project
 * @package SDLT
 * @author  Catalyst I.T. SilverStripe Team 2018 <silverstripedev@catalyst.net.nz>
 * @copyright NZ Transport Agency
 * @license BSD-3
 * @link https://www.catalyst.net.nz
 */

namespace NZTA\SDLT\Extension;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Group;
use NZTA\SDLT\Model\Questionnaire;
use NZTA\SDLT\Model\Task;

/**
 * Class GroupExtension
 */
class GroupExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $belongs_many_many = [
        'Questionnaires' => Questionnaire::class,
        'Tasks' => Task::class
    ];

    /**
     * find or create a new group from the given $title string
     *
     * @param string $title Title of the group
     * @return DataObject
     */
    public static function find_or_make_by_name($title)
    {
        $groupInDB = Group::get()->filter([
            'Title' => $title
        ])->first();

        // if group doesn't exist with the given name then create one.
        if (empty($groupInDB)) {
            $newgroup = Group::create();
            $newgroup->Title = $title;
            $newgroup->write();
            $groupInDB = $newgroup;
        }

        return $groupInDB;
    }
}
