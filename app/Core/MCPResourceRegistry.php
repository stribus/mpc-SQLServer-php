<?php

namespace MCP\SqlServer\Core;

use MCP\SqlServer\Interfaces\MCPResourceInterface;

class MCPResourceRegistry
{
    /** @var MCPResourceInterface[] */
    private array $resources = [];

    public function register(MCPResourceInterface $resource): void
    {
        $this->resources[strtolower($resource->getSchema())] = $resource;
    }


    // retorna o recurso baseado no schema
    public function get(string $schema): MCPResourceInterface
    {
        if (0 !== strpos($schema, '://')) {
            throw new \Exception("URI inválida: {$schema}", -32600);
        }
        if (strpos($schema, '://') > 0) {
            $schema = substr($schema, strpos($schema, '://'));
        }
        if (!isset($this->resources[strtolower($schema)])) {
            throw new \Exception("Resource '{$schema}' não encontrada", -32601);
        }

        return $this->resources[strtolower($schema)];
    }

    public function list(): array {

        return array_map(fn($resource) => [
            'name' => $resource->getName(),
            'description' => $resource->getDescription(),
            'title' => $resource->getTitle() ?? $resource->getName(),
        ], $this->resources);
    }
}
