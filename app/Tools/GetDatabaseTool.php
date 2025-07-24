<?php

namespace App\Tools;

use Flight;
use MCP\SqlServer\Interfaces\MCPResourceInterface;
use MCP\SqlServer\Interfaces\MCPToolInterface;

use MCP\SqlServer\Interfaces\AbstractMCPTool;
use PDO;

class GetDatabaseTool extends AbstractMCPTool
{

    private PDO $pdo;
    protected string $name = 'get_databases';
    protected string $description = 'Ferramenta para obtenção dos nomes dos bancos de dados.';
    protected ?string $title = 'Obter Bancos de Dados';
    protected array $arguments = [];
    protected array | null | string $outputSchema = [
        'type' => 'array',
        'items' => [
            'type' => 'string',
            'description' => 'Nome dos bancos de dados'
        ]
    ];

    public function __construct() {
        $pdo = Flight::get('pdo');
        if (!$pdo instanceof PDO) {
            throw new \Exception("PDO não está configurado corretamente.", -32603);
        }
        $this->pdo = $pdo;
    }

    public function execute(array $arguments): array
    {
        $stmt = $this->pdo->query("SELECT name FROM sys.databases");
        $return = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $return;
    }
}