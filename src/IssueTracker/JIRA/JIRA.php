<?php
/**
 * This file contains the "JIRA" class.
 *
 * @category SilverStripe_Project
 * @package SDLT
 * @author  Catalyst I.T. SilverStripe Team 2019 <silverstripedev@catalyst.net.nz>
 * @copyright NZ Transport Agency
 * @license BSD-3
 * @link https://www.catalyst.net.nz
 */

namespace NZTA\SDLT\IssueTracker\JIRA;

use GuzzleHttp\Client;
use NZTA\SDLT\IssueTracker\IssueTrackerSystem;
use SilverStripe\ORM\DataObject;
use NZTA\SDLT\Model\SecurityComponent;
use GuzzleHttp\Exception\RequestException;

/**
 * JIRA class makes calls to the cloud/back-office ticketing application for adding tasks,
 * and querying its RESR API.
 */
class JIRA extends IssueTrackerSystem
{
    /**
     * @var Guzzle\Http\Client
     */
    protected $client = null;

    /**
     * username, api_key, atlassian_instance, project_name are passed in with
     * Injector:
     *
     * ```
     * SilverStripe\Core\Injector\Injector:
     *  NZTA\SDLT\IssueTracker\JIRA\JIRA:
     *    properties:
     *      username: '`JIRA_USERNAME`'
     *      api_key: '`JIRA_API_KEY`'
     *      atlassian_instance: '`JIRA_ATLASSIAN_INSTANCE`'
     * ```
     *
     * Set the above constants in .env
     *
     * @param  string $endpoint  JIRA endpoint
     * @param  string $data      JSON-encoded data
     * @param  string $method    Which HTTP verb to use
     * @return string
     */
    public function call(string $endpoint, string $data = '', string $method = 'POST') : string
    {
        $this->client = new Client([
            'base_uri' => $this->atlassian_instance
        ]);

        switch ($method) {
            default:
            case 'POST':
                $response = $this->doPost($endpoint, $data);
                break;
            case 'GET':
                $response = $this->doGet($endpoint);
                break;
        }

        return (string) $response->getBody();
    }

    /**
     * Shortcut method to make a POST request.
     *
     * @param  string $endpoint  JIRA endpoint
     * @param  string $data      JSON-encoded data
     * @return GuzzleHttp\Psr7\Response
     */
    private function doPost(string $endpoint, string $data) : \GuzzleHttp\Psr7\Response
    {
        try {
            $response =  $this->client->request('POST', $endpoint, [
                'auth' => [
                    $this->username,
                    $this->api_key
                ],
                'body' => $data,
                'headers' => [
                    'Accept'     => 'application/json',
                    'Content-Type' => 'application/json',
                ]
            ]);
            return $response;
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            $statuscode = $response->getStatusCode();
            $jsonContent = (string) $response->getBody();
            $content = json_decode($jsonContent, true);

            if (isset($content['errors']['project']) && $statuscode == 400) {
                throw new \Exception("The JIRA Project Key does not exist. Please enter a valid JIRA Project Key.");
            } else {
                throw new \Exception($exception->getMessage());
            }
        }
    }

    /**
     * Shortcut method to make a POST request.
     *
     * @param  string $endpoint  JIRA endpoint
     *
     * @return GuzzleHttp\Psr7\Response
     */
    private function doGet(string $endpoint) : \GuzzleHttp\Psr7\Response
    {
        return $this->client->request('GET', $endpoint, [
            'auth' => [
                $this->username,
                $this->api_key
            ],
            'headers' => [
                'Accept'     => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * Adds a task to the JIRA board.
     *
     * @param string            $projectName   The name of the JIRA board to post issues to
     * @param string            $productAspect
     * @param SecurityComponent $component     Each ticket represents a single SecurityComponent.
     * @throws \Exception                      When project name is not set
     * @return string                          A string to use as the "TicketLink" field
     *                                         in the {@link JiraTicket} model.
     */
    public function addTask(string $projectName, SecurityComponent $component, string $issueType = 'Task', string $productAspect = '') : string
    {
        $projectName = strtoupper($projectName);
        $title = $component->Name;
        $title = !empty($productAspect) ? $productAspect . ' - ' . $title : $title;
        $issue = $component->getTicket();

        $issue
                ->setSummaryText(sprintf('SDLT Controls - %s (#%d)', $title, $component->ID))
                ->setProjectKey($projectName)
                ->setHeadingText($title)
                ->setDescriptionText($component->Description)
                ->setEmail($this->ticket_info_email)
                ->setIssueType($issueType);

        $baseUri = sprintf('/rest/api/%d/issue', $this->config()->get('api_version'));
        $body = $issue->compose();

        if ($result = $this->call($baseUri, $body)) {
            $json = json_decode($result);

            if (isset($json->key)) {
                return sprintf(
                    "%s/projects/%s/issues/%s",
                    $this->atlassian_instance,
                    $projectName,
                    $json->key
                );
            }
        }

        return '';
    }

    /**
     * Each JIRA ticket embodies a single {@link SecurityComponent} and contains
     * a list of control-names built from a local list of {@link SecurityControl}
     * records related to the applicable {@link SecurityComponent} record.
     *
     *
     * This function is passed an {@link SS_List} of {@link JiraTicket} which we use to query JIRA with.
     * It takes the relevant JIRA version's status notation (e.g. using JIRA V3's emoji's ala :check_mark:)
     * and converts it into a human readable status string ala "realised" or "not applicable".
     *
     * @throws Exception
     * @todo Add setter(s) to {@link JIRAIssueV3} to set arbitrary JSON and add various get/path
     * methods ala XPath for JSON (See: https://github.com/peekmo/jsonpath).
     *
     * @param object $jiraTicket single JiraTicket object
     * @return void
     *
     * Note: Assumes v3 JIRA API.
     */
    public function getControlDetailsFromJiraTicket($jiraTicket)
    {
        $controlDetails = [];

        if ($this->config()->get('api_version') != 3) {
            throw new \Exception(__FUNCTION__ . ' will only work in v3+ API.');
        }

        $emojiStatusMap = $this->issue()->config()->get('emoji_status_map');

        $ticketID = $jiraTicket->getJiraTicketID();

        /** One ticket per {@link SecurityComponent} */
        // Query JIRA...
        $baseUri = sprintf(
            '/rest/api/%d/issue/%s',
            $this->config()->get('api_version'),
            $ticketID
        );

        $result = $this->call($baseUri, '', 'GET');

        if (!$result) {
            throw new \RuntimeException('Unexpected API response: Bad response.');
        }

        if (is_null($ret = json_decode($result, true))) {
            throw new \RuntimeException('Unexpected API response: Empty response.');
        }

        if (empty($ret['fields']['description']['content'])) {
            throw new \RuntimeException('Unexpected API response: Bad response body.');
        }

        $controlBody = $ret['fields']['description']['content'];

        if (empty ($controlBody)) {
            return $controlDetails;
        }

        // Lose the response' "preamble"
        $remoteControls = array_chunk(array_slice($controlBody, 3), 2);

        if (empty($remoteControls)) {
            return $controlDetails;
        }

        foreach ($remoteControls as $remoteControl) {
            $remoteControlContent = isset($remoteControl[0]['content']) ? $remoteControl[0]['content'] : null;
            $emoji = null;
            $heading = null;

            if (!empty ($remoteControlContent)) {
              $emoji = isset($remoteControlContent[0]['attrs']['shortName']) ? $remoteControlContent[0]['attrs']['shortName'] : null;
              $heading = isset($remoteControlContent[1]['text']) ? $remoteControlContent[1]['text'] : null;

              if (empty(trim($heading)) && isset($remoteControlContent[2]['text'])) {
                $heading = isset($remoteControlContent[2]['text']) ?$remoteControlContent[2]['text'] : null;
              };
            }

            if (!in_array($emoji, array_keys($emojiStatusMap))) {
                throw new \RuntimeException('Unexpected API response: Unmatched emoji.');
            }

            if ($emoji && $heading) {
                $matches = [];
                if (preg_match("@(?<=#)[\d]+@", trim($heading), $matches)) {
                    $controlDetails[] = [
                        'ID' =>$matches[0],
                        'ControlHeading' => trim($heading),
                        'SelectedOption' => $emojiStatusMap[$emoji]
                    ];
                }
            }
        }

        return $controlDetails;
    }

}
