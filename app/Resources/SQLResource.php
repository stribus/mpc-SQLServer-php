<?php

namespace MCP\SqlServer\Resources;

use MCP\SqlServer\Interfaces\AbstractMCPResource;


class SQLResource extends AbstractMCPResource {

    protected string $name = 'sql';
    protected string $schema = 'sqlserver';
    protected string $title = 'SQL Server Resource';
    protected string $description = 'Retorna estrutura de um banco de dados SQL Server.';
    protected ?string $uri = 'sqlserver://';



    public function listResources(string $uri): array {
        $content = [];
        $params = $this->URI2Arguments($uri);
        if (empty($params['database'])) {
            $SQL = "SELECT name FROM sys.databases";
            $stmt = $this->dbConnection->query($SQL);
            $databases = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($databases as $database) {
                $content[] = [
                    'name' => $database,
                    'uri' => "{$this->uri}/$database",
                    "title" => "Banco de Dados: $database",
                    "description" => "Banco de dados SQL Server: $database",
                    "type" => "database",
                    "mimeType" => "text/plain"
                ];
            }
        }else{
            $SQL = "SELECT name FROM sys.tables WHERE object_id = OBJECT_ID(:table)";
            $stmt = $this->dbConnection->prepare($SQL);
            $stmt->execute(['table' => $params['table']]);
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($tables as $table) {
                $content[] = [
                    'name' => $table,
                    'uri' => "{$this->uri}/$params[database]/$table",
                    "title" => "Tabela: $table",
                    "description" => "Tabela SQL Server: $table",
                    "type" => "table",
                    "mimeType" => "text/plain"
                ];
            }
            if (empty($tables)) {
                return [
                    'content' => [
                        'type' => 'text',
                        'value' => "Nenhuma tabela encontrada no banco de dados {$params['database']}."
                    ]
                ];
            }
        }

        return [
                'content' => $content
            ];
    }

    // Executa a consulta SQL e retorna o resultado
    public function getContent(string $uri): array {
        $params = $this->URI2Arguments($uri);

        // Validação básica
        if (empty($params['database'])) {
            return [
                'content' => [
                    'type' => 'text',
                    'value' => 'Parâmetros obrigatórios ausentes: database e query.'
                ]
            ];
        }

        $database = $params['database'];
        $query = $params['query'];
        $queryParams = $params['params'] ?? [];

        // Permitir apenas SELECT por segurança
        if (!preg_match('/^\s*SELECT/i', $query)) {
            return [
                'content' => [
                    'type' => 'text',
                    'value' => 'Apenas comandos SELECT são permitidos.'
                ]
            ];
        }

        // Conexão SQL Server
        $config = require(__DIR__ . '/../config/database.php');
        $connectionInfo = [
            "Database" => $database,
            "UID" => $config['username'],
            "PWD" => $config['password'],
            "CharacterSet" => "UTF-8"
        ];
        $serverName = $config['host'] . ',' . $config['port'];

        $conn = sqlsrv_connect($serverName, $connectionInfo);

        if ($conn === false) {
            return [
                'content' => [
                    'type' => 'text',
                    'value' => 'Erro de conexão com o SQL Server: ' . print_r(sqlsrv_errors(), true)
                ]
            ];
        }

        // Executa consulta parametrizada
        $stmt = sqlsrv_query($conn, $query, $queryParams);

        if ($stmt === false) {
            sqlsrv_close($conn);
            return [
                'content' => [
                    'type' => 'text',
                    'value' => 'Erro ao executar a consulta: ' . print_r(sqlsrv_errors(), true)
                ]
            ];
        }

        $rows = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        return [
            'content' => [
                'type' => 'text',
                'value' => json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            ]
        ];
    }

    protected function URI2Arguments(string $uri): array
    {
        if(strpos($uri, "{$this->uri}/") !== 0) {
            throw new \InvalidArgumentException("URI inválida: $uri");
        }
        $parts = explode('/', substr($uri, strlen("{$this->uri}/")));
        $arguments = [];
        if (count($parts) > 0) {
            $arguments['database'] = $parts[0];
        }
        if (count($parts) > 1) {
            $arguments['table'] = $parts[1];
        }
        return $arguments;
    }
}
