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
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Forms\FormField;
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
        'SubmitterEmailSubject' => 'Text',
        'SubmitterEmailBody' => 'HTMLText',
        'EmailSignature' => 'HTMLText',
        'LinkPrefix' => 'Varchar(32)',
        'ApprovalLinkEmailSubject' => 'Text',
        'ApprovalLinkEmailBody' => 'HTMLText',
        'StakeholdersEmailSubject' => 'Text',
        'StakeholdersEmailBody' => 'HTMLText',
    ];

    /**
     *
     * @var array
     */
    private static $summary_fields = [
        'Label' => 'Label',
        'FromEmailAddress' => 'From Email Address',
        'SubmitterEmailSubject' => 'Submitter Email Subject',
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
        $fields->removeByName([
            'Label',
            'FromEmailAddress',
            'EmailSignature',
            'LinkPrefix',
            'OwnerID',
            'SubmitterEmailSubject',
            'SubmitterEmailBody',
            'ApprovalLinkEmailSubject',
            'ApprovalLinkEmailBody',
            'StakeholdersEmailSubject',
            'StakeholdersEmailBody'
        ]);

        $fields->addFieldsToTab(
            'Root.Main',
            [
                ToggleCompositeField::create(
                    "main",
                    FormField::name_to_label("Main"),
                    [
                        TextField::create('Label'),
                        EmailField::create('FromEmailAddress'),
                        HtmlEditorField::create('EmailSignature')
                            ->setRows('3'),
                        TextField::create('LinkPrefix'),
                    ]
                ),
                ToggleCompositeField::create(
                    "submitterEmail",
                    FormField::name_to_label("Submitter Email"),
                    [
                        TextField::create('SubmitterEmailSubject', "Submitter Email Subject")
                            ->setDescription("You may use any of the following variables in"
                            ." the subject of your email: {\$taskName}, {\$taskLink}, {\$productName}, "
                            ." {\$submitterName}, and {\$submitterEmail}. They will be "
                            ." replaced with the actual value."),
                        HtmlEditorField::create('SubmitterEmailBody', "Submitter Email Body")
                            ->setRows('3')
                            ->setDescription("You may use any of the following variables in"
                            ." the body of your email: {\$taskName}, {\$taskLink}, {\$productName}, "
                            ." {\$submitterName}, and {\$submitterEmail}. They will be "
                            ." replaced with the actual value."),
                    ]
                ),
                ToggleCompositeField::create(
                    "approvalEmail",
                    FormField::name_to_label("Approval Email"),
                    [
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
                ),
                ToggleCompositeField::create(
                    "stakeholdersEmail",
                    FormField::name_to_label("Stakeholders Email"),
                    [
                        TextField::create('StakeholdersEmailSubject', "Stakeholders Email Subject")
                            ->setDescription("You may use any of the following variables in"
                            ." the subject of your email: {\$taskName}, {\$taskLink}, {\$productName}, "
                            ." {\$submitterName}, and {\$submitterEmail}. They will be "
                            ." replaced with the actual value."),
                        HtmlEditorField::create('StakeholdersEmailBody', "Stakeholders Email Body")
                            ->setRows('3')
                            ->setDescription("You may use any of the following variables in"
                            ." the body of your email: {\$taskName}, {\$taskLink}, {\$productName}, "
                            ." {\$submitterName}, and {\$submitterEmail}. They will be "
                            ." replaced with the actual value."),
                    ]
                ),
            ]
        );

        return $fields;
    }
}
