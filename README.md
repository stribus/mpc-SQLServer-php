# Servidor MCP para SQL Server

Este projeto implementa um servidor MCP (Model Context Protocol) em PHP que expõe a estrutura de dados de um banco SQL Server 2019 para ferramentas de IA como GitHub Copilot no VSCode.

## 📋 Características

- **Protocolo MCP 2025-06-18**: Implementação completa e rigorosa do padrão MCP
- **JSON-RPC 2.0**: Todas as respostas seguem o padrão JSON-RPC 2.0
- **SQL Server 2019**: Integração completa com SQL Server via extensão sqlsrv
- **FlightPHP Framework**: Framework leve e eficiente para APIs REST
- **IIS + Windows 11**: Configurado para hospedagem em IIS no Windows

## 🚀 Funcionalidades

### Ferramentas (Tools) Disponíveis

1. **get_databases**: Retorna lista de bancos de dados disponíveis
2. **get_tables**: Retorna tabelas e views de um banco específico
3. **get_table_structure**: Retorna estrutura detalhada de uma tabela
4. **get_stored_procedures**: Retorna stored procedures de um banco

### Recursos (Resources) Disponíveis

- Estrutura completa de cada banco de dados via `resources/list`
- Leitura de dados específicos via `resources/read`

### Métodos MCP Obrigatórios

- ✅ `initialize`: Inicialização do servidor
- ✅ `tools/list`: Lista ferramentas disponíveis
- ✅ `tools/call`: Executa ferramentas
- ✅ `resources/list`: Lista recursos disponíveis (bancos, tabelas, etc.)
- ✅ `resources/read`: Lê recursos específicos (linhas, colunas, etc.)
- ✅ `prompts/list`: Lista prompts disponíveis

## 📁 Estrutura do Projeto

```estrutura
projeto/
├── app/                          # Código fonte do servidor MCP
│   ├── Config/                   # Configurações do servidor
│   │   ├── Bootstrap.php         # Inicialização do servidor
│   │   ├── Config.php            # Configurações gerais
│   │   └── Database.php          # Configurações do banco de dados
│   ├── Controllers/              # Controladores do FlightPHP
│   │   └── MCPServerController.php # Servidor MCP principal
│   ├── Core/                     # Núcleo do MCP (registries, helpers, etc.)
│   ├── Helpers/                  # Funções utilitárias
│   ├── Interfaces/               # Interfaces e classes abstratas
│   ├── Models/                   # Modelos de dados
│   ├── Prompts/                  # Prompts MCP
│   ├── Resources/                # Recursos MCP (ex: SQLResource)
│   └── Tools/                    # Ferramentas MCP (ex: TablesTool)
├── public/                       # Diretório público para o IIS
│   ├── web.config                # Configuração do IIS
│   └── index.php                 # Ponto de entrada principal
├── vendor/                       # Dependências do Composer
├── composer.json                 # Dependências do projeto
├── composer.lock                 # Lockfile do Composer
├── test/                         # Testes unitários e cliente de teste
│   └── test_client.php           # Cliente de teste
├── extras/                       # Arquivos extras
│   └── mcp_config.json           # Configuração para VSCode
└── logs/                         # Logs do sistema (criar manualmente)
```

## 🔧 Instalação e Configuração

### 1. Pré-requisitos

- Windows 11
- IIS com PHP 8.1
- SQL Server 2019
- Extensão PHP sqlsrv
- Composer

### 2. Instalação das Dependências

```bash
composer install
```

### 3. Configuração do Banco de Dados

Edite o arquivo `.env` ou `config/database.php` para definir as credenciais do SQL Server:

```.env
DB_SERVER=localhost
DB_USERNAME=sa
DB_PASSWORD=sua_senha_aqui
```

### 4. Configuração do IIS

1. Crie um novo site no IIS apontando para a pasta do projeto
2. Certifique-se que o arquivo `web.config` está na raiz
3. Configure as permissões necessárias para o PHP acessar o SQL Server

### 5. Teste da Instalação

Execute o cliente de teste:

```bash
php test_client.php
```

## 🔌 Integração com VSCode/Copilot

### Configuração Manual

1. Configure o arquivo `mcp_config.json` conforme suas necessidades
2. Adicione a configuração no settings.json do VSCode:

```json
{
  "mcp.servers": {
    "sqlserver": {
      "command": "curl",
      "args": ["-X", "POST", "-H", "Content-Type: application/json", "-d", "@-", "http://localhost:8080"]
    }
  }
}
```

### Uso com GitHub Copilot

O servidor MCP fornecerá contexto automaticamente sobre:

- Estrutura de tabelas
- Relacionamentos (FK/PK)
- Tipos de dados
- Índices e constraints
- Stored procedures disponíveis

## 📡 API Endpoints

### POST /

Endpoint principal JSON-RPC 2.0 para todas as operações MCP.

Exemplo de inicialização:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "initialize",
  "params": {
    "protocolVersion": "2025-06-18",
    "capabilities": {},
    "clientInfo": {
      "name": "VSCode",
      "version": "1.0.0"
    }
  }
}
```

### GET /health

Health check do servidor.

### GET /

Documentação da API.

## 🛠️ Exemplos de Uso

### Obter Lista de Databases

```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/call",
  "params": {
    "name": "get_databases",
    "arguments": {}
  }
}
```

### Obter Estrutura de Tabela

```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "get_table_structure",
    "arguments": {
      "database": "MinhaDatabase",
      "table": "dbo.Usuarios"
    }
  }
}
```

## 🔍 Troubleshooting

### Erro de Conexão com SQL Server

1. Verifique as credenciais em `config/database.php`
2. Certifique-se que a extensão sqlsrv está instalada
3. Verifique se o SQL Server está rodando e acessível

### Erro 500 no IIS

1. Verifique os logs do IIS
2. Confirme que o PHP está configurado corretamente
3. Verifique permissões de arquivo

### Cliente de IA não recebe contexto

1. Verifique se o servidor está inicializado corretamente
2. Confirme que as configurações MCP estão corretas
3. Teste com o cliente de teste incluído

## 📊 Monitoramento

O servidor inclui:

- Health check endpoint (`/health`)
- Tratamento de erros padronizado
- Logs opcionais (configurar `LOG_ENABLED` em `config/database.php`)

## 🔐 Segurança

- Conexões SQL parametrizadas
- Validação rigorosa de entrada
- Escape de caracteres SQL
- Headers CORS configuráveis

## 🤝 Contribuição

Para contribuir com o projeto:

1. Faça fork do repositório
2. Crie uma branch para sua feature
3. Implemente os testes necessários
4. Submeta um pull request

## 📄 Licença

Este projeto está licenciado sob a MIT License.

## 📞 Suporte

Para dúvidas ou problemas:

- Execute `php test_client.php` para diagnóstico
- Verifique os logs do IIS
- Consulte a documentação oficial do MCP: [https://modelcontextprotocol.io/](https://modelcontextprotocol.io/)