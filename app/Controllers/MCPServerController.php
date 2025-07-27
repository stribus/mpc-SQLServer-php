<?php

namespace MCP\SqlServer\Controllers;

use MCP\SqlServer\Core\MCPService;

class MCPServerController
{
    private MCPService $service;

    public function __construct()
    {
        // Inicializa o serviço MCP
        $this->service = new MCPService();
    }

    public function handleRequest($request)
    {
        // Valida estrutura JSON-RPC
        if (!isset($request['jsonrpc']) || '2.0' !== $request['jsonrpc']) {
            return [
                'jsonrpc' => '2.0',
                'id' => $request['id'] ?? null,
                'error' => [
                    'code' => -32600,
                    'message' => 'Invalid Request',
                ],
            ];
        }

        $id = $request['id'] ?? null;

        try {
            switch ($request['method']) {
                case 'initialize':
                    
                    $result = [
                        'protocolVersion' => '2024-11-05',
                        'capabilities' => [
                            
                            // 'methods' => [
                            //     'initialize',
                            //     'tools/list',
                            //     'tools/call',
                            //     'resources/list',
                            //     'resources/read',
                            //     'prompts/list',
                            //     'prompts/get',
                            // ],
                        ],
                        'instructions' => 'Use the MCP server to interact with SQL Server databases. Available methods: tools/list, tools/call, resources/list, resources/read, prompts/list, prompts/get.',
                    ];
                    if ($this->service->hasPrompts()) {
                        $result['capabilities']['prompts'] = [];
                    }
                    if ($this->service->hasResources()) {
                        $result['capabilities']['resources'] = [];
                    }
                    if ($this->service->hasTools()) {
                        $result['capabilities']['tools'] = [];
                    }

                    break;

                case 'tools/list':
                    $result = $this->service->listTools();

                    break;

                case 'tools/call':
                    $params = $request['params'] ?? [];
                    $result = $this->service->callTool($params);

                    break;

                case 'resources/list':
                    $uri = $request['params']['uri'] ?? '';
                    $result = $this->service->listResources($uri);

                    break;

                case 'resources/read':
                    $uri = $request['params']['uri'] ?? '';
                    $result = $this->service->getResource($uri);

                    break;

                case 'prompts/list':
                    $result = $this->service->listPrompts();
                    break;

                case 'prompts/get':
                    $params = $request['params'] ?? [];
                    $name = $params['name'] ?? '';
                    $context = $params['context'] ?? [];
                    $result = $this->service->getPrompt($name, $context);

                    break;

                default:
                    return [
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'error' => [
                            'code' => -32601,
                            'message' => 'Method not found',
                        ],
                    ];
            }

            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error',
                    'data' => $e->getMessage(),
                ],
            ];
        }
    }
}
