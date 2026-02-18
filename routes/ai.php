<?php

use Laravel\Mcp\Facades\Mcp;
use App\Mcp\Servers\QueueAnalyticsServer;

/*
|--------------------------------------------------------------------------
| MCP Server Routes
|--------------------------------------------------------------------------
|
| Register your MCP servers here. Servers can be registered as 'web'
| (accessible via HTTP) or 'local' (accessible via CLI/stdio).
|
*/

// Web server (HTTP API access) - requires authentication
Mcp::web('/mcp/queue-analytics', QueueAnalyticsServer::class)
    ->middleware(['auth:sanctum', 'throttle:60,1']);

// Local server (CLI/stdio access) - for desktop AI clients like Claude Desktop
Mcp::local('queue-analytics', QueueAnalyticsServer::class);
