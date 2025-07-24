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
            // $required = [];
            // $arguments = method_exists($tool, 'getArguments') ? $tool->getArguments() : [];
            $inputSchema = $tool->getInputSchema();
            $outputSchema = $tool->getOutputSchema();


            $return = [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'title' => $tool->getTitle() ?? $tool->getName(),
                'inputSchema' => $inputSchema,
            ];

            if ($outputSchema !== null) {
                $return['outputSchema'] = $outputSchema;
            }

            return $return;

        }, $this->tools);
    }
}
