<?php

namespace MCP\SqlServer\Tools;

use MCP\SqlServer\Interfaces\AbstractMCPTool;

class TableStructureTool extends AbstractMCPTool
{
    protected string $name = 'get_table_structure';
    protected string $description = 'Retorna a estrutura de uma tabela específica do banco especificado.';
    protected ?string $title = 'Obter Estrutura da Tabela';

    protected array $arguments = [
        [
            'name' => 'database',
            'type' => 'string',
            'description' => 'Nome do banco de dados',
            'required' => true,
        ],
        [
            'name' => 'table',
            'type' => 'string',
            'description' => 'Nome da tabela',
            'required' => true,
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

    public function execute(array $arguments): mixed
    {
        $database = $arguments['database'] ?? '';
        $table = $arguments['table'] ?? '';

        if (empty($database) || empty($table)) {
            throw new \InvalidArgumentException('Os parâmetros "database" e "table" são obrigatórios.');
        }

        $stmt = $this->pdo->prepare("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE FROM [{$database}].INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :table");
        $stmt->bindParam(':table', $table);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
