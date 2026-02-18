<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;


class QueueAnalyticsServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'QwaitingAI Queue Analytics';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        # QwaitingAI Queue Analytics Server
        
        This server provides AI-powered queue analytics, performance insights, predictions, 
        and optimization recommendations for the QwaitingAI queue management system.
        
        ## Available Tools
        
        ### predict-queue-performance
        Predicts future queue performance based on historical data analysis.
        
        **Use when:** You need to predict future queue performance for capacity planning or forecasting.
        
        ## How to Use
        
        1. Specify the team_id and location_id to analyze
        2. Provide target_start_date and target_end_date for the prediction period
        3. Review the JSON response with predicted metrics

        
        ## Example Query
        "Analyze queue performance for location 1 from January 1-31, 2026"
        
        ## Data Requirements
        - Valid team/tenant ID
        - Existing location with queue data
        - Date range within available data (recommend 7-90 days for meaningful insights)
        
        ## Response Format
        All responses are in JSON format with:
        - period: Date range analyzed
        - metrics: Core performance metrics
        - trends: Percentage changes vs previous period
        - insights: AI-generated recommendations (if requested)
        - summary: Executive summary with health assessment
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        \App\Mcp\Tools\PredictQueuePerformanceTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        // Future: QueueMetricsResource, AnalyticsReportResource
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        //Future: QueueAnalystPrompt
    ];
}
