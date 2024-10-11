#!/usr/bin/env php
<?php declare(strict_types=1);

autoloadManager();

use App\Config\ConfigManager;
use App\Database\DatabaseConnection;
use App\Display\TerminalDisplay;
use App\Logger\TrackingLogs;

$configManager = new ConfigManager();

if ($argc === 3 && $argv[1] === 'reconfigure' && $argv[2] === 'config') {
    $configManager->reconfigure();
    exit(0);
}

if ($argc !== 3) {
    TerminalDisplay::showUsageInstructions();
    exit(1);
}

$configManager->set();

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

function autoloadManager(): void
{
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require __DIR__ . '/vendor/autoload.php';
    } elseif (file_exists(__DIR__ . '/../lib/vendor/autoload.php')) {
        require __DIR__ . '/../lib/vendor/autoload.php';
    } else {
        echo "Error: autoload.php not found. Please check your installation.\n";
        exit(1);
    }
}

