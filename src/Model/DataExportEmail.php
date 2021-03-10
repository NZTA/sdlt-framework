<?php

/**
 * This file contains the "DataExportEmail" class.
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
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\HTMLEditor\HtmlEditorField;
use SilverStripe\Forms\TextField;

/**
 * Class DataExportEmail
 */
class DataExportEmail extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'DataExportEmail';

    /**
     * @var array
     */
    private static $db = [
        'FromEmailAddress' => 'Varchar(255)',
        'DataExportEmailSubject' => 'Text',
        'DataExportEmailBody' => 'HTMLText',
        'EmailSignature' => 'HTMLText'
    ];

    /**
     * getCMSFields
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldsToTab(
            'Root.Main',
            [
                EmailField::create(
                    'FromEmailAddress'
                ),
                HtmlEditorField::create(
                    'EmailSignature'
                )
                    ->setRows('3'),
                TextField::create(
                    'DataExportEmailSubject',
                    'Email Subject'
                ),
                HtmlEditorField::create(
                    'DataExportEmailBody',
                    'Email Body'
                )
                    ->setRows(10)
                    ->setDescription(
                        '<p class="message notice">You can use the following variable substitutions
                        in the email body and subject:<br/><br/>' .
                        '<b>{$dataClass}</b> For exported data class<br/>' .
                        '<b>{$dataName}</b> For exported data name<br/>' .
                        '<b>{$fileName}</b> For file name<br/>' .
                        '<b>{$userName}</b> For user name<br/>' .
                        '<b>{$userEmail}</b> For user email.</p>'
                    )
            ]
        );

        return $fields;
    }
}
