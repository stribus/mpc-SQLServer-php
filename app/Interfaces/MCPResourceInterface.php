<?php

namespace MCP\SqlServer\Interfaces;

interface MCPResourceInterface
{
    public function getName(): string;

    public function getSchema(): string;

    public function getTitle(): string;

    public function getDescription(): string;

    public function listResources(string $uri): array;

    public function getContent(string $uri): mixed;
}
