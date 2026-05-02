<?php

namespace App\Http\Controllers;

use App\Services\JiraService;
use Illuminate\Http\Request;

class JiraController extends Controller
{
    private $jiraService;

    public function __construct(JiraService $jiraService)
    {
        $this->jiraService = $jiraService;
    }

    /**
     * Test connection
     * GET /jira/test
     */
    public function test()
    {
        $result = $this->jiraService->testConnection();
        return response()->json($result);
    }

    /**
     * Get backlog
     * GET /jira/backlog
     */
    public function backlog()
    {
        $result = $this->jiraService->getBacklog();
        return response()->json($result);
    }

    /**
     * Get specific issue
     * GET /jira/issue/{key}
     */
    public function getIssue($key)
    {
        $result = $this->jiraService->getIssue($key);
        return response()->json($result);
    }

    /**
     * Create issue
     * POST /jira/issue
     */
    public function createIssue(Request $request)
    {
        $validated = $request->validate([
            'project_key' => 'required|string',
            'summary' => 'required|string',
            'description' => 'required|string',
            'issue_type' => 'string|default:Task'
        ]);

        $result = $this->jiraService->createIssue(
            $validated['project_key'],
            $validated['summary'],
            $validated['description'],
            $validated['issue_type'] ?? 'Task'
        );

        return response()->json($result);
    }
}
