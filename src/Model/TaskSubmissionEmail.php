<?php

/**
 * This file contains the "QuestionnaireEmail" class.
 *
 * @category SilverStripe_Project
 * @package SDLT
 * @author  Catalyst I.T. SilverStripe Team 2018 <silverstripedev@catalyst.net.nz>
 * @copyright NZ Transport Agency
 * @license BSD-3
 * @link https://www.catalyst.net.nz
 */

namespace NZTA\SDLT\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\HTMLEditor\HtmlEditorField;
use SilverStripe\Control\Director;
use NZTA\SDLT\Model\TaskSubmission;
use SilverStripe\Forms\EmailField;

/**
 * Class TaskSubmissionEmail
 */
class TaskSubmissionEmail extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'TaskSubmissionEmail';

    /**
     * @var array
     */
    private static $db = [
        'Label' => 'Varchar(32)',
        'FromEmailAddress' => 'Varchar(255)',
        'EmailSubject' => 'Text',
        'EmailBody' => 'HTMLText',
        'EmailSignature' => 'HTMLText',
        'LinkPrefix' => 'Varchar(32)',
        'ApprovalLinkEmailSubject' => 'Text',
        'ApprovalLinkEmailBody' => 'HTMLText',
    ];

    /**
     *
     * @var array
     */
    private static $summary_fields = [
        'Label' => 'Label',
        'FromEmailAddress' => 'From Email Address',
        'EmailSubject' => 'Email Subject',
        'LinkPrefix' => 'Link Prefix',
    ];

    /**
     *
     * @var array
     */
    private static $has_one = [
        'Owner' => Task::class
    ];

    /**
     * getCMSFields
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['OwnerID']);

        $fields->addFieldsToTab(
            'Root.Main',
            [
                EmailField::create('FromEmailAddress'),
                TextField::create('EmailSubject', "Submitter Email Subject")
                    ->setDescription("You may use any of the following variables in"
                    ." the subject of your email: {\$taskName}, {\$taskLink}, {\$productName}, "
                    ." {\$submitterName}, and {\$submitterEmail}. They will be "
                    ." replaced with the actual value."),
                HtmlEditorField::create('EmailBody', "Submitter Email Body")
                    ->setRows('3')
                    ->setDescription("You may use any of the following variables in"
                    ." the body of your email: {\$taskName}, {\$taskLink}, {\$productName}, "
                    ." {\$submitterName}, and {\$submitterEmail}. They will be "
                    ." replaced with the actual value."),
                HtmlEditorField::create('EmailSignature')
                    ->setRows('3'),
                TextField::create('ApprovalLinkEmailSubject', "Approver Email Subject")
                    ->setDescription("You may use any of the following variables in"
                    ." the subject of your email: {\$taskName}, {\$taskLink}, {\$productName}, "
                    ." {\$submitterName}, and {\$submitterEmail}. They will be "
                    ." replaced with the actual value."),
                HtmlEditorField::create('ApprovalLinkEmailBody', "Approver Email Body")
                    ->setRows('3')
                    ->setDescription("You may use any of the following variables in"
                    ." the body of your email: {\$taskName}, {\$taskLink}, {\$productName}, "
                    ." {\$submitterName}, and {\$submitterEmail}. They will be "
                    ." replaced with the actual value."),
            ]
        );

        return $fields;
    }
}
