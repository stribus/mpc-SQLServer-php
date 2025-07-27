<?php

namespace MCP\SqlServer\Config;

use Dotenv\Dotenv;
use Flight;

class Database
{
    private static $config = [
        // 'server1' => [
        //     'host' => 'localhost',
        //     'port' => 1433,
        //     'username' => 'user1',
        //     'password' => 'password1',
        //     'database' => 'db1',
        // ],
        // 'server2' => [
        //     'host' => 'localhost',
        //     'port' => 1433,
        //     'username' => 'user2',
        //     'password' => 'password2',
        //     'database' => 'db2',
        //     'options' => [ // Configuração sql server
        //         'encrypt' => true,
        //         'trustServerCertificate' => false,
        //         'connectionPooling' => true,
        //         'integratedSecurity' => true, // Usar autenticação integrada do Windows
        //         'appName' => 'MCP SQL Server Client',
        //         'loginTimeout' => 30, // Tempo limite de login em segundos
        //         'queryTimeout' => 300, // Tempo limite de consulta em segundos
        //         'charset' => 'utf8', // Conjunto de caracteres
        //         'ssl' => true, // Habilitar SSL
        //     ]
        // ]
    ];

    public static function getConfig($serverName)
    {
        if (isset(self::$config[$serverName])) {
            return self::$config[$serverName];
        }

        throw new \Exception("Configuração do servidor '{$serverName}' não encontrada.");
    }

    public static function getAllConfigs()
    {
        return self::$config;
    }

    public static function configureAll()
    {
        // foreach (self::$config as $serverName => $config) {
        //     // Registra o servidor com FlightPHP
        //     // Verifica se a configuração contém opções de autenticação
        //     try {
        //         Flight::register($serverName, function () use ($config) {
        //             if (isset($config['options']) && isset($config['options']['integratedSecurity']) && $config['options']['integratedSecurity']) {
        //                 // Usar autenticação integrada do Windows
        //                 $connection = new \PDO(
        //                     "sqlsrv:Server={$config['host']},{$config['port']};Database={$config['database']}",
        //                     null,
        //                     null,
        //                     $config['options'] ?? []
        //                 );
        //             } else {
        //                 // Usar autenticação SQL Server
        //                 if (!isset($config['username']) || !isset($config['password'])) {
        //                     throw new \Exception("Usuário e senha são necessários para autenticação SQL Server.");
        //                 }
        //                 $connection = new \PDO(
        //                     "sqlsrv:Server={$config['host']},{$config['port']};Database={$config['database']}",
        //                     $config['username'],
        //                     $config['password'],
        //                     $config['options'] ?? []
        //                 );
        //             }

        //             $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        //             return $connection;
        //         });
        //     } catch (\Exception $e) {
        //         throw new \Exception("Erro ao configurar o servidor '{$serverName}': " . $e->getMessage());
        //     }
        // }
        // por enquanto vai ser apenas o localhost
        self::loadConfig();
        // Verifica se a configuração do servidor 'localhost' existe
        if (!isset(self::$config['localhost'])) {
            throw new \Exception("Configuração do servidor 'localhost' não encontrada.");
        }

        try {
            $config = self::getConfig('localhost');
            if (isset($config['integratedSecurity']) && $config['integratedSecurity']) {
                // Usar autenticação integrada do Windows
                $connection = new \PDO(
                    "sqlsrv:Server={$config['host']},{$config['port']};Database={$config['database']}",
                    null,
                    null
                );
            } else {
                // Usar autenticação SQL Server
                if (!isset($config['user']) || !isset($config['password'])) {
                    throw new \Exception('Usuário e senha são necessários para autenticação SQL Server.');
                }
                $connection = new \PDO(
                    "sqlsrv:Server={$config['host']},{$config['port']};Database={$config['database']}",
                    $config['user'],
                    $config['password']
                );
            }
            $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(\PDO::SQLSRV_ATTR_ENCODING, \PDO::SQLSRV_ENCODING_UTF8);

            \Flight::set('pdo', $connection);
        } catch (\Exception $e) {
            throw new \Exception("Erro ao configurar o servidor 'localhost': ".$e->getMessage());
        }
    }

    private static function loadConfig()
    {
        // Carrega a configuração do arquivo .env na raiz do projeto
        $dotenv = Dotenv::createImmutable(ABSPATH);
        $dotenv->load();
        foreach ($_ENV as $key => $value) {
            if (0 === strpos($key, 'DB_')) {
                // $key = 'DB_localhost_host'
                $parts = explode('_', $key);
                if (count($parts) >= 3) {
                    $serverName = strtolower($parts[1]);
                    $configKey = implode('_', array_slice($parts, 2));
                    if (!isset(self::$config[$serverName])) {
                        self::$config[$serverName] = [];
                    }
                    self::$config[$serverName][$configKey] = $value;
                }
            }
        }
    }
}
