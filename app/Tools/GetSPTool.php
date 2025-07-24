<?php

namespace MCP\SqlServer\Tools;

use MCP\SqlServer\Interfaces\AbstractMCPTool;
use PDO;
use Flight;

class GetSPTool extends AbstractMCPTool {

    private PDO $pdo;

    protected string $name = 'get_stored_procedures';
    protected string $description = 'Retorna todas as stored procedures do banco especificado.';
    protected ?string $title = 'Obter Stored Procedures';
    protected array $arguments = [
        [
            'name' => 'database',
            'type' => 'string',
            'description' => 'Nome do banco de dados',
            'required' => true
        ]
    ];

    protected array | null | string $outputSchema = [
        'type' => 'array',
        'items' => [
            'type' => 'string',
            'description' => 'Nome das stored procedures'
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

        $stmt = $this->pdo->prepare("SELECT name FROM [$database].sys.procedures");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}