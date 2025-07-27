#!/usr/bin/env node

/**
 * Ponte entre VS Code MCP (STDIO) e o servidor HTTP MCP SQL Server
 * Converte comunicação STDIO para requisições HTTP
 */

const http = require('http');
const readline = require('readline');

const MCP_SERVER_URL = 'http://localhost/mcp-sqlserver/';

// Debug mode
const DEBUG = process.env.DEBUG === 'true';

function debug(message) {
    if (DEBUG) {
        console.error('[DEBUG]', message);
    }
}

// Configurar interface de leitura do STDIN
const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout,
    terminal: false
});

debug('MCP Bridge iniciado, aguardando entrada...');

// Função para enviar requisição HTTP para o servidor MCP
function sendHttpRequest(jsonData) {
    return new Promise((resolve, reject) => {
        const postData = JSON.stringify(jsonData);

        debug(`Enviando requisição: ${postData}`);

        const options = {
            hostname: 'localhost',
            port: 80,
            path: '/mcp-sqlserver/',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(postData),
                'User-Agent': 'VS Code MCP Bridge'
            }
        };

        const req = http.request(options, (res) => {
            let data = '';

            res.on('data', (chunk) => {
                data += chunk;
            });

            res.on('end', () => {
                try {
                    debug(`Resposta recebida: ${data}`);
                    const response = JSON.parse(data);
                    resolve(response);
                } catch (e) {
                    reject(new Error(`Invalid JSON response: ${data}`));
                }
            });
        });

        req.on('error', (e) => {
            debug(`Erro na requisição: ${e.message}`);
            reject(e);
        });

        req.write(postData);
        req.end();
    });
}// Processar cada linha do STDIN como uma mensagem JSON-RPC
rl.on('line', async (line) => {
    try {
        const trimmedLine = line.trim();
        if (!trimmedLine) return;

        const request = JSON.parse(trimmedLine);

        // Enviar para o servidor HTTP
        const response = await sendHttpRequest(request);

        // Retornar resposta via STDOUT
        console.log(JSON.stringify(response));

    } catch (error) {
        // Enviar erro JSON-RPC em caso de falha
        const errorResponse = {
            jsonrpc: '2.0',
            id: null,
            error: {
                code: -32603,
                message: 'Internal error',
                data: error.message
            }
        };
        console.log(JSON.stringify(errorResponse));
    }
});

// Tratar encerramento do processo
rl.on('close', () => {
    process.exit(0);
});

process.on('SIGINT', () => {
    process.exit(0);
});

process.on('SIGTERM', () => {
    process.exit(0);
});
