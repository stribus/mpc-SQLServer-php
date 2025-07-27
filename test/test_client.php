<?php

class MCPTestClient {
    private string $url;
    private int $id = 1;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct(string $url) {
        $this->url = rtrim($url, '/') . '/';
    }

    public function call(string $method, array $params = []): array {
        $request = [
            'jsonrpc' => '2.0',
            'id'      => $this->id++,
            'method'  => $method
        ];
        if (!empty($params)) {
            $request['params'] = $params;
        }

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        $response = curl_exec($ch);
        if ($response === false) {
            throw new RuntimeException('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response: ' . json_last_error_msg());
        }
        return $decoded;
    }

    public function assert(bool $condition, string $message): void {
        if ($condition) {
            echo "[PASS] $message\n";
            $this->passed++;
        } else {
            echo "[FAIL] $message\n";
            $this->failed++;
        }
    }

    public function summary(): void {
        echo "\n--- Test Summary ---\n";
        echo "Passed: {$this->passed}, Failed: {$this->failed}\n";
    }
}

// Ajuste a URL conforme seu ambiente IIS/FlightPHP
$client = new MCPTestClient('http://localhost/mcp-sqlserver/');

try {
    echo "1) initialize\n";
    $resp = $client->call('initialize', [
        'protocolVersion' => '2025-06-18',
        'capabilities'    => new stdClass(),
        'clientInfo'      => ['name' => 'test_client', 'version' => '1.0.0']
    ]);
    $client->assert(isset($resp['result']), 'initialize retorna result');

    echo "\n2) tools/list\n";
    $resp = $client->call('tools/list');
    $client->assert(isset($resp['result']['tools']), 'tools/list retorna array "tools"');
    $tools = $resp['result']['tools'];
    foreach (['get_databases','get_tables','get_table_structure','get_stored_procedures'] as $tool) {
        $client->assert(in_array($tool, $tools), "$tool está registrado");
    }

    echo "\n3) tools/call – get_databases\n";
    $resp = $client->call('tools/call', ['name' => 'get_databases', 'arguments' => []]);
    $client->assert(isset($resp['result']) && is_array($resp['result']), 'get_databases retorna array de nomes de DB');
    $databases = $resp['result'];
    $dbName = reset($databases);
    $client->assert(is_string($dbName) && $dbName !== '', "Nome de database válido: $dbName");

    echo "\n4) tools/call – get_tables\n";
    $resp = $client->call('tools/call', [
        'name'      => 'get_tables',
        'arguments' => ['database' => $dbName]
    ]);
    $client->assert(isset($resp['result']) && is_array($resp['result']), 'get_tables retorna array');
    $tableEntry = reset($resp['result']);
    $client->assert(isset($tableEntry['name']), 'Cada entrada de tabla tem "name"');
    $tableName = $tableEntry['name'];

    echo "\n5) tools/call – get_table_structure\n";
    $resp = $client->call('tools/call', [
        'name'      => 'get_table_structure',
        'arguments' => ['database' => $dbName, 'table' => $tableName]
    ]);
    $client->assert(isset($resp['result']['columns']), 'get_table_structure retorna "columns"');
    $col = reset($resp['result']['columns']);
    $client->assert(isset($col['name'], $col['type']), 'Cada coluna tem "name" e "type"');

    echo "\n6) tools/call – get_stored_procedures\n";
    $resp = $client->call('tools/call', [
        'name'      => 'get_stored_procedures',
        'arguments' => ['database' => $dbName]
    ]);
    $client->assert(isset($resp['result']) && is_array($resp['result']), 'get_stored_procedures retorna array');

    echo "\n7) prompts/list\n";
    $resp = $client->call('prompts/list');
    $client->assert(isset($resp['result']['prompts']) && is_array($resp['result']['prompts']), 'prompts/list retorna array "prompts"');

    echo "\n8) resources/list – raiz\n";
    $resp = $client->call('resources/list', ['uri' => 'sqlserver:///']);
    $client->assert(isset($resp['result']) && is_array($resp['result']), 'resources/list (root) retorna array');
    $first = reset($resp['result']);
    $client->assert(strpos($first['uri'] ?? '', 'sqlserver:///') === 0, 'URI de recurso válida');

    echo "\n9) resources/list – database específico\n";
    if (!empty($first['uri'])) {
        $resp = $client->call('resources/list', ['uri' => $first['uri']]);
        $client->assert(isset($resp['result']) && is_array($resp['result']), 'resources/list (db) retorna array');
    }

    $client->summary();

} catch (Throwable $e) {
    echo "\n[ERROR] {$e->getCode()}: {$e->getMessage()}\n";
    $client->summary();
    exit(1);
}