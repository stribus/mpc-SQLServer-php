<?php

/**
 * Entrada do servidor MCP para modo STDIO (VS Code integration).
 */
define('ABSPATH', str_replace('\\', '/', dirname(__FILE__)).'/');

require_once 'vendor/autoload.php';

require_once ABSPATH.'/app/config/config.php';

use MCP\SqlServer\Config\Database;
use MCP\SqlServer\Controllers\MCPServerController;

// Configurar PDO para o servidor MCP
Database::configureAll();

// Função para ler entrada JSON-RPC do STDIN
function readJsonRpcFromStdin(): ?array
{
    $input = '';
    while (($line = fgets(STDIN)) !== false) {
        $input .= $line;
        // Verifica se temos uma mensagem JSON completa
        if (($decoded = json_decode(trim($input), true)) !== null) {
            return $decoded;
        }
    }

    return null;
}

// Função para enviar resposta JSON-RPC para STDOUT
function sendJsonRpcToStdout(array $response): void
{
    echo json_encode($response)."\n";
    flush();
}

// Loop principal do servidor MCP
try {
    $mcpServer = new MCPServerController();

    // Modo STDIO - lê do STDIN e escreve no STDOUT
    while (true) {
        $request = readJsonRpcFromStdin();

        if (null === $request) {
            break; // EOF ou erro de leitura
        }

        try {
            $response = $mcpServer->handleRequest($request);

            // Garantir formato JSON-RPC 2.0 válido
            if (!isset($response['jsonrpc']) || '2.0' !== $response['jsonrpc']) {
                $response = [
                    'jsonrpc' => '2.0',
                    'id' => $request['id'] ?? null,
                    'error' => [
                        'code' => -32600,
                        'message' => 'Invalid Request',
                    ],
                ];
            }

            if (!isset($response['id'])) {
                $response['id'] = $request['id'] ?? null;
            }

            sendJsonRpcToStdout($response);
        } catch (Exception $e) {
            $errorResponse = [
                'jsonrpc' => '2.0',
                'id' => $request['id'] ?? null,
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error',
                    'data' => $e->getMessage(),
                ],
            ];
            sendJsonRpcToStdout($errorResponse);
        }
    }
} catch (Exception $e) {
    // Log de erro crítico
    error_log('MCP Server Critical Error: '.$e->getMessage());

    exit(1);
}
