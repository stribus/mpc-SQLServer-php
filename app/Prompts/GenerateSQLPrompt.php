<?php

namespace MCP\SqlServer\Prompts;

use MCP\SqlServer\Interfaces\AbstractMCPPrompt;

/**
 * Classe para gerar prompts SQL com base em colunas de uma tabela.
 */
class GenerateSQLPrompt extends AbstractMCPPrompt
{
    protected string $name = 'generate_sql';
    protected string $description = 'Gera uma consulta SQL com base em colunas da tabela';
    protected ?string $title = 'Gerar Consulta SQL';
    protected array $arguments = [
        'table' => [
            'type' => 'string',
            'description' => 'Nome da tabela para a qual a consulta SQL será gerada',
            'required' => true,
        ],
        'columns' => [
            'type' => 'array',
            'description' => 'Lista de colunas a serem incluídas na consulta SQL',
            'required' => true,
        ],
    ];

    public function getPromptText(array $context): string
    {
        $table = $context['table'] ?? '';
        $columns = $context['columns'] ?? [];

        $colStr = implode(', ', $columns);

        return "Escreva uma consulta SQL usando a tabela '{$table}' com as colunas: {$colStr}.";
    }
}
