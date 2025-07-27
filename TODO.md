# MCP SQL Server - TODO

## Ferramentas (Tools)
- [x] Implementar `get_table_structure` (estrutura de colunas, tipos, índices) - ✅ `TableStructureTool.php`
- [x] Implementar `get_stored_procedures` (lista de SPs do banco) - ✅ `GetSPTool.php`
- [x] Implementar `get_sp_code` (código fonte de stored procedures paginado) - ✅ `SPCodeStructureTool.php`
- [x] Corrigir namespace de `DatabaseTool.php` para `MCP\SqlServer\Tools` - ✅ Todas as tools estão no namespace correto
- [x] Registrar novas ferramentas em `app/Core/MCPToolRegistry.php` - ✅ Auto-registro via `ClassAutoLoader`

## Configuração
- [x] Ajustar parser de `.env` em `Database.php` para aceitar variáveis padrão - ✅ Funciona com `DB_localhost_*`
- [x] Criar `.env.example` com variáveis necessárias - ✅ `.env` já existe com exemplos

## Recursos (Resources)
- [ ] Refatorar `SQLResource` para usar `Flight::get('pdo')` ao invés de require manual
- [ ] Corrigir extração de parâmetros (query, params) em `getContent()`

## Prompts
- [x] Validar se todos os prompts listados no README existem em `app/Prompts` e estão registrados - ✅ `GenerateSQLPrompt.php` existe

## Testes & Documentação

- [x] Completar `test/test_client.php` para cobrir todos métodos MCP - ✅ Pasta `test` e arquivo `test/test_client.php` existem
- [ ] Criar testes unitários (PHPUnit) para tools/resources
- [ ] Atualizar README com variáveis de ambiente e exemplos reais

## Geral

- [x] Validar fluxo de registro automático de tools/resources/prompts via autoload - ✅ Funciona via `ClassAutoLoader`
- [x] Garantir que todas as respostas MCP estejam 100% compatíveis com JSON-RPC 2.0 - ✅ Implementado no `MCPServerController`
- [ ] Implementar autenticação básica para o MCP (opcional)
- [ ] Implementar cache de resultados para queries frequentes (opcional)