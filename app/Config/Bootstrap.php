<?php

require_once '../vendor/autoload.php';
require_once ABSPATH . '/app/config/config.php';

use MCP\SqlServer\Config\Database;
use MCP\SqlServer\Controllers\MCPServerController;
use Tracy\Debugger;
use Tracy\Bridges\Psr\TracyExtensionLoader;

// Configuração do Flight
//Flight::set('flight.views.path', './views');

// Get the $app var to use below
if(empty($app)) {
	$app = Flight::app();
}

// Configuração de CORS para permitir requisições do VSCode/Copilot
$app->before('start', function () {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    Database::configureAll();
});


// Endpoint principal para JSON-RPC 2.0
$app->route('POST /', function ()  {
    try {
        $input = file_get_contents('php://input');
        $request = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $response = [
                'jsonrpc' => '2.0',
                'id' => null,
                'error' => [
                    'code' => -32700,
                    'message' => 'Parse error'
                ]
            ];
            Flight::jsonHalt($response, 400);
        }

        $mcpServer = new MCPServerController();

        $response = $mcpServer->handleRequest($request);
        if (!isset($response['jsonrpc']) || $response['jsonrpc'] !== '2.0') {
            $response = [
                'jsonrpc' => '2.0',
                'id' => $request['id'] ?? null,
                'error' => [
                    'code' => -32600,
                    'message' => 'Invalid Request'
                ]
            ];
        }
        if (!isset($response['id'])) {
            $response['id'] = $request['id'] ?? null;
        }
        Flight::jsonHalt($response);

    } catch (Exception $e) {
        $response = [
            'jsonrpc' => '2.0',
            'id' => $request['id'] ?? null,
            'error' => [
                'code' => -32603,
                'message' => 'Internal error',
                'data' => $e->getMessage()
            ]
        ];
        Flight::jsonHalt($response, 500);
    }
});

// Endpoint de health check
$app->route('GET /health', function () {
    Flight::json([
        'status' => 'healthy',
        'timestamp' => date('c'),
        'mcp_version' => '2025-06-18'
    ]);
});

// Endpoint para documentação
$app->route('GET /', function () {
    Flight::json([
        'name' => 'SQL Server MCP Server',
        'version' => '1.0.0',
        'description' => 'Servidor MCP para exposição de estrutura de dados SQL Server',
        'mcp_version' => '2025-06-18',
        'endpoints' => [
            'POST /' => 'JSON-RPC 2.0 endpoint',
            'GET /health' => 'Health check',
            'GET /' => 'Esta documentação'
        ]
    ]);
});

// Tratamento de erro 404
$app->map('notFound', function () {
    Flight::json([
        'jsonrpc' => '2.0',
        'id' => null,
        'error' => [
            'code' => -32601,
            'message' => 'Method not found'
        ]
    ], 404);
});

// Iniciar o servidor
$app->start();