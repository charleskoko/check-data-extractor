<?php declare(strict_types=1);

namespace App\Config;

use App\Display\TerminalDisplay;

class ConfigManager
{
    private array $config;
    private string $config_file;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $config_file = getenv("HOME") . "/.db_config.php";
        $this->config_file = $config_file;
        if (file_exists($config_file)) {
            $config = include $config_file;
            if (!is_array($config)) {
                throw new \Exception("Le fichier de configuration est invalide : il ne retourne pas un tableau.");
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
        echo "Reconfiguring database credentials (leave empty to keep current value):\n";

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
        $this->save();
    }

    private function save(): void
    {
        file_put_contents($this->config_file, "<?php\nreturn " . var_export($this->config, true) . ";\n");
        TerminalDisplay::showSuces("Configuration updated and saved in {$this->config_file}\n");
    }
}
