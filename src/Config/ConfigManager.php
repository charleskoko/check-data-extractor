<?php declare(strict_types=1);

namespace App\Config;

use App\Display\TerminalDisplay;

class ConfigManager
{
    private array $config;
    private string $config_file;


    public function __construct()
    {
        $config_file = getenv("HOME") . "/.db_config.php";
        $this->config_file = $config_file;
        if (file_exists($config_file)) {
            $config = include $config_file;
            if (!is_array($config)) {
                TerminalDisplay::showInfo("The configuration file is invalid: it does not return an array.");
                $isDeleted = exec("rm -f " . escapeshellarg($config), $output, $return_var);
                if (!$isDeleted) {
                    TerminalDisplay::showWarning("Please manually delete the file in {$this->config_file} before continuing.");
                }
                TerminalDisplay::showSuces("The file was successfully deleted.");
                exit(1);
            }
            $this->config = $config;
        } else {
            $this->createConfigFile();
        }
    }

    public function set(): void
    {
        if (!file_exists($this->config_file)) {
            $this->createConfigFile();
        }
    }

    public function get(string $key): mixed
    {
        return $this->config[$key] ?? null;
    }

    public function reconfigure(): void
    {
        TerminalDisplay::showInfo("Reconfiguring database credentials (leave empty to keep current value):\n");

        $this->config['db_host'] = readline("Database host [{$this->get('db_host')}] : ") ?: $this->get('db_host');
        $this->config['db_port'] = readline("Database port [{$this->get('db_port')}] : ") ?: $this->get('db_port');
        $this->config['db_name'] = readline("Database name [{$this->get('db_name')}] : ") ?: $this->get('db_name');
        $this->config['db_username'] = readline("Username [{$this->get('db_username')}] : ") ?: $this->get('db_username');
        $this->config['db_pass'] = readline("Password [****] : ") ?: $this->get('db_pass');  // password is masked

        $this->save();
    }

    public function createConfigFile(): void
    {

        $this->config['db_host'] = 'localhost';
        $this->config['db_port'] = '3306';
        $this->config['db_name'] = 'root';
        $this->config['db_username'] = '';
        $this->config['db_pass'] = 'database_name';
        $this->save(true);
    }

    public function showQueries(): void
    {
        if (!isset($this->config['queries']) || empty($this->config['queries'])) {
            TerminalDisplay::showInfo("No SQL queries found in the configuration.");
            return;
        }

        TerminalDisplay::showInfo("Stored SQL queries:\n");

        foreach ($this->config['queries'] as $queryName => $sqlQuery) {
            TerminalDisplay::showWarning("- " . $queryName . ": ");
            echo "  " . $sqlQuery . "\n\n";
        }
    }

    public function addQuery(): void
    {
        TerminalDisplay::showInfo("Add a new SQL query to the configuration.\n");

        $queryName = readline("Enter a name for the query (e.g., tracking_query): ");
        if (empty($queryName)) {
            TerminalDisplay::showError("Query name cannot be empty.");
            return;
        }

        $sqlQuery = readline("Enter the SQL query: ");
        if (empty($sqlQuery)) {
            TerminalDisplay::showError("SQL query cannot be empty.");
            return;
        }

        $this->config['queries'][$queryName] = $sqlQuery;

        $this->save();

        TerminalDisplay::showSuces("Query '$queryName' added to the configuration successfully.");
    }

    private function save(bool $isFirstTime = false): void
    {
        if ($isFirstTime) {
            file_put_contents($this->config_file, "<?php\nreturn " . var_export($this->config, true) . ";\n");
            TerminalDisplay::showSuces("Configuration created and saved in {$this->config_file}");
        }
        if (!$isFirstTime) {
            file_put_contents($this->config_file, "<?php\nreturn " . var_export($this->config, true) . ";\n");
            TerminalDisplay::showSuces("Configuration updated and saved in {$this->config_file}");
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
