<?php

// use flight\debug\tracy\TracyExtensionLoader;
// use Tracy\Debugger;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;


// Set the default timezone
date_default_timezone_set('America/Sao_Paulo');

// Set the error reporting level
error_reporting(E_ALL);

// Set the default character encoding
if(function_exists('mb_internal_encoding') === true) {
	mb_internal_encoding('UTF-8');
}

// Set the default locale
if(function_exists('setlocale') === true) {
	setlocale(LC_ALL, 'pt_BR.UTF-8');
}


// Load log configuration
// cria arquivo de log por data e mantém apenas os últimos 30 dias
$logFile = __DIR__ . '/../logs/app.log';
$log = new Logger('app');
$log->pushHandler(new RotatingFileHandler($logFile, 30, \Monolog\Level::Debug));