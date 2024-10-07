#!/usr/bin/env php
<?php declare(strict_types=1);

$config_file = getenv("HOME") . "/.db_config.php";

class DatabaseConnection
{
    private static $instance = null;
    private $conn;
    private string $db_host;
    private string $db_port;
    private string $db_user;
    private string $db_pass;
    private string $db_name;

    public function __construct(string $db_host, string $db_port, string $db_user, string $db_pass, string $db_name)
    {
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->db_name = $db_name;
        $this->db_port = $db_port;

        try {
            $this->conn = new PDO("mysql:host={$this->db_host};port={$this->db_port};dbname={$this->db_name}", $this->db_user, $this->db_pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \RuntimeException("Failed to connect to the database: " . $e->getMessage());
        }
    }

    public static function getInstance(string $db_host, string $db_port, string $db_user, string $db_pass, string $db_name): DatabaseConnection
    {
        if (!self::$instance) {
            self::$instance = new DatabaseConnection($db_host, $db_port, $db_user, $db_pass, $db_name);
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}

class TrackingLogs
{
    private DatabaseConnection $databaseConnection;

    private const TABLE = [
        'yps' => 'tracking_ypsilon',
        'trf' => 'tracking_travelfusion',
        'ama' => 'tracking_amadeus',
        'sab' => 'tracking_sabre',
    ];

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * @param array $arg
     * $arg[0] => transactionId
     * $arg[1] => GDS
     * @return void
     */
    public function doRequestForTrackingData(array $arg): void
    {
        $transactionId = $arg['transactionId'];
        $table = self::TABLE[strtolower($arg['gds'])];
        if (null !== $transactionId) {
            $sql = "SELECT timestamp, type, UNCOMPRESS(request_raw), UNCOMPRESS(response_raw) FROM $table WHERE tracking_api_transaction = :tracking_api_transaction";
        } else {
            throw new \RuntimeException('no arguments passed');
        }

        $connection = $this->databaseConnection->getConnection();
        $request = $connection->prepare($sql);
        $request->bindParam(':tracking_api_transaction', $transactionId, PDO::PARAM_STR);
        $request->execute();
        $this->saveInfoInFolder($transactionId, $request);
    }

    /**
     * @param $transactionId
     * @param $request
     * @return void
     */
    public function saveInfoInFolder($transactionId, $request): void
    {
        $folder = getenv("HOME") . "/Downloads/transaction_{$transactionId}";
        if (!file_exists($folder) && !mkdir($folder) && !is_dir($folder)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $folder));
        }
        while ($row = $request->fetch(PDO::FETCH_ASSOC)) {
            $timestamp = $row['timestamp'];
            $type = $row['type'];
            $request_raw = $row['UNCOMPRESS(request_raw)'];
            $response_raw = $row['UNCOMPRESS(response_raw)'];
            $fileName = "$folder/{$transactionId}_{$timestamp}_{$type}_RQ.xml";
            file_put_contents($fileName, $request_raw);
            $fileName = "$folder/{$transactionId}_{$timestamp}_{$type}_RS.xml";
            file_put_contents($fileName, $response_raw);
        }
    }
}

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

function load_existing_config($config_file): array
{
    if (!file_exists($config_file)) {
        throw new \RuntimeException("Configuration file not found.");
    }

    include $config_file;

    if (!isset($db_host, $db_port, $db_name, $db_username, $db_pass)) {
        throw new \RuntimeException("Invalid configuration file format.");
    }

    return [
        'db_host' => $db_host,
        'db_port' => $db_port,
        'db_name' => $db_name,
        'db_username' => $db_username,
        'db_pass' => $db_pass,
    ];
}

function mask_password(string $password): string
{
    // Si le mot de passe est inférieur à 2 caractères, renvoie-le tel quel.
    if (strlen($password) <= 2) {
        return $password;
    }

    // Remplace tous les caractères sauf les deux derniers par des astérisques (*)
    return str_repeat('*', strlen($password) - 2) . substr($password, -2);
}

function reconfigure_db($config_file): void
{
    $config = load_existing_config($config_file);

    echo "Modify the database credentials (press Enter to keep the current value):\n";

    $masked_password = mask_password($config['db_pass']);
    $db_host = readline("Database host [{$config['db_host']}] : ") ?: $config['db_host'];
    $db_port = readline("Database port [{$config['db_port']}] : ") ?: $config['db_port'];
    $db_name = readline("Database name [{$config['db_name']}] : ") ?: $config['db_name'];
    $db_username = readline("Username [{$config['db_username']}] : ") ?: $config['db_username'];
    $db_pass = readline("Password [{$masked_password}] : ") ?: $config['db_pass'];

    // Sauvegarder les modifications dans le fichier
    $config_content = "<?php\n";
    $config_content .= "\$db_host = '$db_host';\n";
    $config_content .= "\$db_port = '$db_port';\n";
    $config_content .= "\$db_name = '$db_name';\n";
    $config_content .= "\$db_username = '$db_username';\n";
    $config_content .= "\$db_pass = '$db_pass';\n";

    file_put_contents($config_file, $config_content);
    echo "Configuration updated and saved in $config_file\n";
}


require $config_file;

function check_transaction(string $transaction_id, string $gds)
{
    try {
        global $db_host, $db_port, $db_name, $db_pass, $db_username;

        $databaseConnection = new DatabaseConnection($db_host, $db_port, $db_username, $db_pass, $db_name);

        $trackingsLogs = new TrackingLogs($databaseConnection);

        $trackingsLogs->doRequestForTrackingData(['transactionId' => $transaction_id, 'gds' => $gds]);

    } catch (Exception $exception) {

        echo (string)($exception->getMessage());

    }

}

if ($argc === 3 && $argv[1] === 'reconfigure' && $argv[2] === 'config') {
    reconfigure_db($config_file);
    exit(0);
}

if ($argc === 3) {
    $transaction_id = (string)$argv[1];
    $gds = (string)$argv[2];
    check_transaction($transaction_id, $gds);
    echo "check in your downloads folder";
    exit(0);
} else {
    echo "Usage : check-data-extractor <transactionID> <GDS>\n";
}
?>
