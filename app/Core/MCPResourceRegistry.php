<?php

namespace MCP\SqlServer\Core;

use MCP\SqlServer\Interfaces\MCPResourceInterface;
use Exception;

class MCPResourceRegistry {
    /** @var MCPResourceInterface[] */
    private array $resources = [];

    public function register(MCPResourceInterface $resource): void {
        $this->resources[strtolower($resource->getSchema())] = $resource;
    }

    public function get(string $schema): MCPResourceInterface {
        if (strpos($schema, '://') === false || strpos($schema, '://') !== 0) {
            throw new Exception("URI inválida: {$schema}", -32600);
        }
        if (strpos($schema, '://') > 0) {
            $schema = strstr($schema, '://', true); // Extract the part before '://'
        }
        if (!isset($this->resources[strtolower($schema)])) {
            throw new Exception("Resource '{$schema}' não encontrada", -32601);
        }
        return $this->resources[strtolower($schema)];
    }

    // public function list(): array {

    //     return array_map(fn($resource) => [
    //         'name' => $resource->getName(),
    //         'description' => $resource->getDescription(),
    //         'arguments' => method_exists($resource, 'getArguments') ? $resource->getArguments() : []
    //     ], $this->resources);
    // }
}
