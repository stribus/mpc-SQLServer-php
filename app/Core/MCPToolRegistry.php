<?php

namespace MCP\SqlServer\Core;

use MCP\SqlServer\Interfaces\MCPToolInterface;
use MCP\SqlServer\Helpers\ClassAutoLoader;
use Exception;


class MCPToolRegistry {
    /** @var MCPToolInterface[] */
    private array $tools = [];

    public function register(MCPToolInterface $tool): void {
        $this->tools[$tool->getName()] = $tool;
    }

    public function get(string $name): MCPToolInterface {
        if (!isset($this->tools[$name])) {
            throw new Exception("Tool '{$name}' Não encontrada", -32601);
        }
        return $this->tools[$name];
    }

    public function list(): array {
        return array_map(function($tool) {
            $required = [];
            $arguments = method_exists($tool, 'getArguments') ? $tool->getArguments() : [];
            $inputSchema = [
                'type' => 'object',
                'properties' => []
            ];
            foreach ($arguments as $parameter) {
                $inputSchema['properties'][$parameter['name']] = [
                    'type' => $parameter['type'] ?? 'string',
                    'description' => $parameter['description'] ?? ''
                ];
                if (isset($parameter['required']) && $parameter['required']) {
                    $required[] = $parameter['name'];
                }
            }

            $return = [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'inputSchema' => $inputSchema,
                'title' => $tool->getTitle() ?? $tool->getName(),
            ];
            if (!empty($required)) {
                $return['required'] = $required;
            }

            return $return;

        }, $this->tools);
    }
}
