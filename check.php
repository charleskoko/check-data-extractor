#!/usr/bin/env php
<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Config\ConfigManager;
use App\Database\DatabaseConnection;
use App\Display\TerminalDisplay;
use App\Logger\TrackingLogs;

$config_file = getenv("HOME") . "/.db_config.php";

if ($argc === 3 && $argv[1] === 'reconfigure' && $argv[2] === 'config') {
    $configManager = new ConfigManager($config_file);
    $configManager->reconfigure();
    exit(0);
}

if ($argc !== 3) {
    TerminalDisplay::showUsageInstructions();
    exit(1);
}

$configManager = new ConfigManager($config_file);

$databaseConnection = new DatabaseConnection(
    $configManager->get('db_host'),
    $configManager->get('db_port'),
    $configManager->get('db_username'),
    $configManager->get('db_pass'),
    $configManager->get('db_name')
);

$trackingLogs = new TrackingLogs($databaseConnection);

$trackingLogs->doRequestForTrackingData($argv[1], $argv[2]);

TerminalDisplay::showSuces("Check the downloads folder.\n");

