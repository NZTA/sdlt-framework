<?php

/**
 * This file contains the "SendExportJsonDataEmailJob" class.
 *
 * @category SilverStripe_Project
 * @package SDLT
 * @author  Catalyst I.T. SilverStripe Team 2018 <silverstripedev@catalyst.net.nz>
 * @copyright NZ Transport Agency
 * @license BSD-3
 * @link https://www.catalyst.net.nz
 */

namespace NZTA\SDLT\Job;

use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;
use SilverStripe\Control\Email\Email;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * A QueuedJob is specifically designed to be invoked from an onAfterWrite() process
 */
class SendExportJsonDataEmailJob extends AbstractQueuedJob implements QueuedJob
{
    /**
     * @param  string   $jsonData      Exported record as a string
     * @param  string   $dataClass     The class for exported data.
     * @param  string   $dataName      The name for exported data.
     * @param  string   $userEmail     The email address for user.
     * @param  string   $userName      The name for user.
     * @param  string   $fileName      The Name for data file.
     * @return void
     */
    public function __construct($jsonData = '', $dataClass = '', $dataName = '', $userEmail = '', $userName = '', $fileName = '')
    {
        $this->jsonData = $jsonData;
        $this->dataClass = $dataClass;
        $this->dataName = $dataName;
        $this->userEmail = $userEmail;
        $this->userName = $userName;
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return sprintf(
            'Initialising data export email for - %s',
            $this->dataClass
        );
    }

    /**
     * {@inheritDoc}
     * @return string
     */
    public function getJobType()
    {
        return QueuedJob::QUEUED;
    }

    /**
     * @return mixed void | null
     */
    public function process()
    {
        $dataExportEmail = SiteConfig::current_site_config()->DataExportEmail();
        if ($dataExportEmail && $dataExportEmail->ID) {
            $sub = $this->replaceVariable(
                $dataExportEmail->DataExportEmailSubject
            );
            $body = $this->replaceVariable(
                $dataExportEmail->DataExportEmailBody
            );
            $from = $dataExportEmail->FromEmailAddress;

            $email = Email::create()
                ->setHTMLTemplate('Email\\EmailTemplate')
                ->setData([
                    'Name' => $this->userName,
                    'Body' => $body,
                    'EmailSignature' => $dataExportEmail->EmailSignature
                ])
                ->setFrom($from)
                ->setTo($this->userEmail)
                ->setSubject($sub)
                ->addAttachmentFromData($this->jsonData, $this->fileName, 'text/json');

            $email->send();

            $this->isComplete = true;
        }
    }

    /**
     * @param string  $string          string
     * @return string
     */
    public function replaceVariable(string $string)
    {
        $dataClass = $this->dataClass;
        $dataName = $this->dataName;
        $fileName = $this->fileName;
        $userName = $this->userName;
        $suserEmail = $this->userEmail;

        $string = str_replace('{$dataClass}', $dataClass, $string);
        $string = str_replace('{$dataName}', $dataName, $string);
        $string = str_replace('{$fileName}', $fileName, $string);
        $string = str_replace('{$userName}', $userName, $string);
        $string = str_replace('{$userEmail}', $suserEmail, $string);

        return $string;
    }
}
