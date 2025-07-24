<?php

namespace MCP\SqlServer\Tools;

use MCP\SqlServer\Interfaces\AbstractMCPTool;
use PDO;

class SPCodeStructureTool extends AbstractMCPTool
{

    private PDO $pdo;

    protected string $name = 'sp_code_structure';
    protected string $description = 'Retorna a estrutura de código de uma stored procedure paginada por numero de caracteres.';
    protected ?string $title = 'Estrutura de Código da Stored Procedure';
    protected array $arguments = [
        [
            'name' => 'database',
            'type' => 'string',
            'description' => 'Nome do banco de dados',
            'required' => true
        ],
        [
            'name' => 'procedure_name',
            'type' => 'string',
            'description' => 'Nome da stored procedure',
            'required' => true
        ],
        // TODO: Implementar opção para incluir/remover comentários no código
        // Se implementado, descomente a linha abaixo e ajuste a lógica de retorno
        // [
        //     'name' => 'include_comments',
        //     'type' => 'boolean',
        //     'description' => 'Incluir comentários no código',
        //     'default' => true,
        //     'required' => false
        // ],
        [
            'name' => 'character_start',
            'type' => 'integer',
            'description' => 'Número do primeiro caractere, maior ou igual a zero (0), e maior que o character_end',
            'required' => true
        ],
        [
            'name' => 'character_end',
            'type' => 'integer',
            'description' => 'Número do último caractere, se maior que o tamanho total do código, será ajustado para o tamanho total',
            'required' => true
        ]
    ];

    public function __construct()
    {
        $pdo = \Flight::get('pdo');
        if (!$pdo instanceof \PDO) {
            throw new \Exception("PDO não está configurado corretamente.", -32603);
        }
        $this->pdo = $pdo;
    }

    public function execute(array $arguments): mixed
    {
        $database = $arguments['database'] ?? '';
        $procedureName = $arguments['procedure_name'] ?? '';
        $includeComments = $arguments['include_comments'] ?? true;
        $characterStart = $arguments['character_start'] ?? 0;
        $characterEnd = $arguments['character_end'] ?? 0;

        if (empty($database) || empty($procedureName) || $characterStart < 0 || $characterEnd < $characterStart) {
            throw new \InvalidArgumentException('Parâmetros inválidos fornecidos.', -32602);
        }

        $stmt = $this->pdo->prepare("EXEC [$database].dbo.sp_helptext :procedureName;");
        $stmt->bindValue(':procedureName', $procedureName);
        $stmt->execute();

        $code = '';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $code .= $row['Text'] ?? '';
        }


        if ($code === false || empty($code)) {
            throw new \RuntimeException('Stored procedure não encontrada.', -32601);
        }

        // Verifica se os índices de caracteres estão dentro do tamanho do código
        $totalSize = strlen($code);
        if ($characterStart < 0 ||  $characterEnd < $characterStart || $characterStart >= $totalSize ) {
            throw new \InvalidArgumentException('Os índices de caracteres estão fora do intervalo do código da stored procedure. Length total: ' . $totalSize, -32602);
        }
        if ($characterEnd >= $totalSize) {
            // Se o índice final for maior que o tamanho total, ajusta para o tamanho total
            $characterEnd = $totalSize - 1;
        }

        if ($characterStart == 0 && $characterEnd == 0) {
            // Se ambos os índices forem zero, retorna somente o tamanho total do código
            return [
                'database' => $database,
                'procedure_name' => $procedureName,
                'total_size' => $totalSize, // Tamanho total do código da stored procedure
                'character_start' => 0,
                'character_end' => 0,
                'code_size' => 0,
                'code' => ""
            ];
        }
        // Extrai a parte do código solicitada
        $codePart = substr($code, $characterStart, $characterEnd - $characterStart + 1);

        if ($codePart === false) {
            throw new \RuntimeException('Erro ao extrair parte do código da stored procedure.', -32603);
        }
        return [
            'database' => $database,
            'procedure_name' => $procedureName,
            'total_size' => $totalSize, // Tamanho total do código da stored procedure
            'character_start' => $characterStart,
            'character_end' => $characterEnd,
            'code_size' => $characterEnd - $characterStart + 1,
            'code' => $codePart
        ];
    }

}