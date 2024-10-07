#!/usr/bin/env php
<?php

require_once __DIR__ . "/src/DatabaseConnection.php";
require_once __DIR__ . "/src/TrackingLogs.php";

$config_file = getenv("HOME") . "/.db_config.php";

function configure_db($config_file)
{
    echo "Database credentials configuration:\n";

    $db_host = readline("Database host : ");
    $db_port = readline("Database port : ");
    $db_name = readline("Database name : ");
    $db_username = readline("username : ");
    $db_pass = readline("Password : ");


    $config_content = "<?php\n";
    $config_content .= "\$db_host = '$db_host';\n";
    $config_content .= "\$db_port = '$db_port';\n";
    $config_content .= "\$db_name = '$db_name';\n";
    $config_content .= "\$db_username = '$db_username';\n";
    $config_content .= "\$db_pass = '$db_pass';\n";

    file_put_contents($config_file, $config_content);
    echo "Configuration saved in $config_file\n";
}

if (!file_exists($config_file)) {
    echo "The configuration file does not exist. Please configure it.\n";
    configure_db($config_file);
}

require $config_file;

function check_transaction(string $transaction_id, string $gds)
{
    try {
        global $db_host, $db_port, $db_name, $db_pass, $db_username;

        $databaseConnection = new DatabaseConnection($db_host, $db_port, $db_username, $db_pass, $db_name);

        $trackingsLogs = new TrackingLogs($databaseConnection);

        $trackingsLogs->doRequestForTrackingData(['transactionId' => $transaction_id, 'gds' => $gds]);

    } catch(Exception $exception) {
        echo (string) ($exception->getMessage());
    }

}

if ($argc === 3) {
    $transaction_id = (string) $argv[1];
    $gds = (string) $argv[2];
    check_transaction($transaction_id, $gds);
    echo "check in your downloads folder";
} else {
    echo "Usage : check.php check exact <transactionID>\n";
}
?>
