#!/usr/bin/env php
<?php declare(strict_types=1);

autoloadManager();

use App\Config\ConfigManager;
use App\Database\DatabaseConnection;
use App\Display\TerminalDisplay;
use App\Logger\TrackingLogs;

$configManager = new ConfigManager();

handleCliArguments($argc, $argv, $configManager);

function handleCliArguments(int $argc, array $argv, ConfigManager $configManager): void
{
    if ($argc === 2 && $argv[1] === 'add-query') {
        $configManager->addQuery();
        exit(0);
    }

    if ($argc === 2 && $argv[1] === 'show-queries') {
        $configManager->showQueries();
        exit(0);
    }

    if ($argc === 3 && $argv[1] === 'reconfigure' && $argv[2] === 'config') {
        $configManager->reconfigure();
        exit(0);
    }

    if ($argc !== 3) {
        TerminalDisplay::showUsageInstructions();
        exit(0);
    }

    executeDatabaseQuery($argv[1], $argv[2], $configManager);

}

function executeDatabaseQuery(string $transactionId, string $gds, ConfigManager $configManager): void
{
    $configManager->set();

    TerminalDisplay::showSuccess("Fetching information...");

    $databaseConnection = new DatabaseConnection(
        $configManager->get('db_host'),
        $configManager->get('db_port'),
        $configManager->get('db_username'),
        $configManager->get('db_pass'),
        $configManager->get('db_name')
    );

    $trackingLogs = new TrackingLogs($databaseConnection, $configManager->getConfig());

    $trackingLogs->doRequestForTrackingData($transactionId, $gds);

    TerminalDisplay::showSuccess("Folder created and saving in Downloads.\n");
}

function autoloadManager(): void
{
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require __DIR__ . '/vendor/autoload.php';
    } elseif (file_exists(__DIR__ . '/../lib/vendor/autoload.php')) {
        require __DIR__ . '/../lib/vendor/autoload.php';
    } else {
        TerminalDisplay::showError("Error: autoload.php not found. Please check your installation.\n");
        exit(1);
    }
}

