<?php

namespace MCP\SqlServer\Core;

use MCP\SqlServer\Helpers\ClassAutoLoader;
use MCP\SqlServer\Interfaces\MCPPromptInterface;
use MCP\SqlServer\Interfaces\MCPResourceInterface;
use MCP\SqlServer\Interfaces\MCPToolInterface;

class MCPService
{
    private MCPToolRegistry $tools;
    private MCPResourceRegistry $resources;
    private MCPPromptRegistry $prompts;

    public function __construct()
    {
        $this->tools = new MCPToolRegistry();
        $this->resources = new MCPResourceRegistry();
        $this->prompts = new MCPPromptRegistry();

        // Registra ferramentas com PDO manualmente
        foreach (ClassAutoLoader::autoloadClasses(ABSPATH.'/app/tools', MCPToolInterface::class) as $tool) {
            if ($tool instanceof MCPToolInterface) {
                $this->tools->register($tool);
            }
        }

        // Registra recursos
        foreach (ClassAutoLoader::autoloadClasses(ABSPATH.'/app/resources', MCPResourceInterface::class) as $resource) {
            if ($resource instanceof MCPResourceInterface) {
                $this->resources->register($resource);
            }
        }

        // Registra prompts
        foreach (ClassAutoLoader::autoloadClasses(ABSPATH.'/app/prompts', MCPPromptInterface::class) as $prompt) {
            if ($prompt instanceof MCPPromptInterface) {
                $this->prompts->register($prompt);
            }
        }
    }

    public function listTools(): array
    {
        return $this->tools->list();
    }

    public function callTool(array $params): mixed
    {
        return $this->tools->get($params['name'])->execute($params['arguments'] ?? []);
    }

    public function listResources(string $uri): array
    {
        return $this->resources->get($uri)->listResources($uri);
    }

    public function getResource(string $uri): mixed
    {
        return $this->resources->get($uri)->getContent($uri);
    }

    public function listPrompts(): array
    {
        return $this->prompts->list();
    }

    public function getPrompt(string $name, array $context): string
    {
        return $this->prompts->get($name)->getPromptText($context);
    }

    // Verifica se há ferramentas registradas
    public function hasTools(): bool
    {
        return !isset($this->tools) || !empty($this->tools->list());
    }
    // Verifica se há prompts registrados
    public function hasPrompts(): bool
    {
        return !isset($this->prompts) || !empty($this->prompts->list());
    }
    // Verifica se há recursos registrados
    public function hasResources(): bool
    {
        return !isset($this->resources) || !empty($this->resources->list());
    }
}
