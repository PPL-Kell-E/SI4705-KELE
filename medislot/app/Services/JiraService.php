<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JiraService
{
    private $baseUrl;
    private $token;
    private $email;

    public function __construct()
    {
        $this->baseUrl = env('JIRA_URL', 'https://liujunxinsec.atlassian.net');
        $this->token = env('JIRA_TOKEN');
        $this->email = env('JIRA_EMAIL', '');
    }

    /**
     * Test connection ke Jira
     */
    public function testConnection()
    {
        try {
            $response = Http::withBasicAuth($this->email, $this->token)
                ->withoutVerifying()
                ->timeout(10)
                ->get($this->baseUrl . '/rest/api/3/myself');

            if ($response->successful()) {
                $user = $response->json();
                return [
                    'success' => true,
                    'message' => 'Connected successfully',
                    'user' => $user['displayName'],
                    'email' => $user['emailAddress']
                ];
            }

            return [
                'success' => false,
                'message' => 'HTTP ' . $response->status(),
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get backlog dari board
     */
    public function getBacklog($boardId = 3, $projectKey = 'PSG')
    {
        try {
            // Method 1: Agile API
            $response = Http::withBasicAuth($this->email, $this->token)
                ->withoutVerifying()
                ->timeout(10)
                ->get($this->baseUrl . '/rest/agile/1.0/boards/' . $boardId . '/backlog');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            // Method 2: Fallback ke REST API v2
            $response = Http::withBasicAuth($this->email, $this->token)
                ->withoutVerifying()
                ->timeout(10)
                ->get($this->baseUrl . '/rest/api/2/search', [
                    'jql' => "project = $projectKey",
                    'maxResults' => 100
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'HTTP ' . $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get specific issue
     */
    public function getIssue($issueKey)
    {
        try {
            $response = Http::withBasicAuth($this->email, $this->token)
                ->withoutVerifying()
                ->timeout(10)
                ->get($this->baseUrl . '/rest/api/2/issue/' . $issueKey);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'HTTP ' . $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create issue
     */
    public function createIssue($projectKey, $summary, $description, $issueType = 'Task')
    {
        try {
            $response = Http::withBasicAuth($this->email, $this->token)
                ->withoutVerifying()
                ->timeout(10)
                ->post($this->baseUrl . '/rest/api/2/issue', [
                    'fields' => [
                        'project' => ['key' => $projectKey],
                        'summary' => $summary,
                        'description' => $description,
                        'issuetype' => ['name' => $issueType]
                    ]
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'key' => $response->json()['key']
                ];
            }

            return [
                'success' => false,
                'message' => 'HTTP ' . $response->status(),
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
