# Configuração do Servidor MCP SQL Server no VS Code

## ✅ Status da Configuração

Seu servidor MCP SQL Server está configurado e funcionando! Aqui está o que foi configurado:

### 1. Servidor HTTP MCP
- **URL**: `http://localhost/mcp-sqlserver/`
- **Status**: ✅ Funcionando (testado com health check)
- **Ferramentas disponíveis**: ✅ Confirmado

### 2. Extensão VS Code
- **Extensão instalada**: `Copilot MCP` (automatalabs.copilot-mcp)
- **Status**: ✅ Instalada

### 3. Configuração MCP
- **Arquivo**: `%APPDATA%\Code\User\profiles\31a7ec62\mcp.json`
- **Servidor**: `mcp-sqlserver`
- **Bridge Script**: `.\mcp-bridge.js`

## 🔧 Ferramentas Disponíveis

Seu servidor MCP expõe as seguintes ferramentas para a IA:

1. **`get_databases`** - Lista todos os bancos de dados disponíveis
2. **`get_tables`** - Lista tabelas de um banco específico
3. **`get_stored_procedures`** - Lista stored procedures de um banco
4. **`sp_code_structure`** - Obtém código de uma stored procedure (paginado)
5. **`table_structure`** - Obtém estrutura de uma tabela

## 🚀 Como Usar

### No VS Code com GitHub Copilot:

1. **Abra o Chat do Copilot** (Ctrl+Shift+I)

2. **Peça para a IA usar suas ferramentas MCP**:
   ```
   @workspace Usando o servidor MCP SQL Server, me mostre todos os bancos de dados disponíveis
   ```

3. **Exemplos de comandos**:
   ```
   @workspace Liste as tabelas do banco 'MinhaBase'
   @workspace Mostre a estrutura da tabela 'Users' do banco 'MinhaBase'
   @workspace Liste as stored procedures do banco 'MinhaBase'
   @workspace Mostre o código da stored procedure 'sp_GetUsers'
   ```

### Configuração do Banco de Dados

Seu arquivo `.env` está configurado para:
- **Host**: localhost
- **Database**: MASTER
- **Autenticação**: Windows Integrated Security
- **Porta**: 1433

## 🔍 Verificação e Testes

### Testar o Servidor HTTP:
```powershell
# Health check
Invoke-RestMethod -Uri "http://localhost/mcp-sqlserver/health"

# Listar ferramentas
$body = '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
Invoke-RestMethod -Uri "http://localhost/mcp-sqlserver/" -Method POST -Body $body -ContentType "application/json"
```

### Testar uma Ferramenta:
```powershell
$body = '{"jsonrpc":"2.0","id":1,"method":"tools/call","params":{"name":"get_databases","arguments":{}}}'
Invoke-RestMethod -Uri "http://localhost/mcp-sqlserver/" -Method POST -Body $body -ContentType "application/json"
```

## 🐛 Solução de Problemas

### Se o VS Code não reconhecer o servidor MCP:

1. **Reinicie o VS Code** completamente
2. **Verifique se o Node.js está instalado**: `node --version`
3. **Teste o bridge script**: `node .\mcp-bridge.js`
4. **Verifique os logs do VS Code**: Help > Toggle Developer Tools > Console

### Se houver erros de banco de dados:

1. **Verifique a conexão SQL Server**
2. **Confirme as credenciais no arquivo `.env`**
3. **Teste uma consulta simples**: `SELECT @@VERSION`

## 📝 Próximos Passos

Agora você pode:
- Usar a IA para explorar sua estrutura de banco de dados
- Gerar consultas SQL automaticamente
- Obter ajuda com stored procedures
- Analisar esquemas de tabelas

**Dica**: Comece pedindo para a IA listar seus bancos de dados para confirmar que tudo está funcionando!
