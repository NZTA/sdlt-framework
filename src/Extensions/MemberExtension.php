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
use SilverStripe\Security\Member;
use NZTA\SDLT\Model\QuestionnaireSubmission;

/**
 * Class MemberExtension
 */
class MemberExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $belongs_many_many = [
        'QuestionnaireSubmissions' => QuestionnaireSubmission::class,
    ];
}
