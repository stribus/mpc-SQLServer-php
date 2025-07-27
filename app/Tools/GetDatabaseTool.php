<?php

namespace App\Tools;

use MCP\SqlServer\Interfaces\AbstractMCPTool;

class GetDatabaseTool extends AbstractMCPTool
{
    protected string $name = 'get_databases';
    protected string $description = 'Ferramenta para obtenção dos nomes dos bancos de dados.';
    protected ?string $title = 'Obter Bancos de Dados';
    protected array $arguments = [];
    protected null|array|string $outputSchema = [
        'type' => 'array',
        'items' => [
            'type' => 'string',
            'description' => 'Nome dos bancos de dados',
        ],
    ];

    private \PDO $pdo;

    public function __construct()
    {
        $pdo = \Flight::get('pdo');
        if (!$pdo instanceof \PDO) {
            throw new \Exception('PDO não está configurado corretamente.', -32603);
        }
        $this->pdo = $pdo;
    }

    public function execute(array $arguments): array
    {
        $stmt = $this->pdo->query('SELECT name FROM sys.databases');

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
