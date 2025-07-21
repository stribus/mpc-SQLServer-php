<?php

namespace MCP\SqlServer\Interfaces;

interface MCPPromptInterface  {
    public function getName(): string;
    public function getTitle(): string;
    public function getDescription(): string;
    public function getArguments(): array;
    public function getPromptText(array $arguments): mixed;
}
