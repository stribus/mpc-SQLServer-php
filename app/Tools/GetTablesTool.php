<?php

namespace MCP\SqlServer\Tools;
use MCP\SqlServer\Interfaces\AbstractMCPTool;
use PDO;
use Flight;

class GetTablesTool extends AbstractMCPTool {

    private PDO $pdo;

    protected string $name = 'get_tables';
    protected string $description = 'Retorna todas as tabelas do banco especificado.';
    protected ?string $title = 'Obter Tabelas do Banco de Dados';
    protected array $arguments = [
        [
            'name' => 'database',
            'type' => 'string',
            'description' => 'Nome do banco de dados',
            'required' => true
        ]
    ];

    public function __construct() {
        $pdo = Flight::get('pdo');
        if (!$pdo instanceof PDO) {
            throw new \Exception("PDO não está configurado corretamente.", -32603);
        }
        $this->pdo = $pdo;
    }

    public function execute(array $arguments): mixed {
        $database = $arguments['database'] ?? '';
        if (empty($database)) {
            throw new \InvalidArgumentException('O parâmetro "database" é obrigatório.');
        }
        $stmt = $this->pdo->query("SELECT TABLE_NAME FROM [$database].INFORMATION_SCHEMA.TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
