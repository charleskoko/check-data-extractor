<?php declare(strict_types=1);

namespace App\Config;

class ConfigManager
{
    private array $config;
    private string $config_file;

    public function __construct(string $config_file)
    {
        $this->config_file = $config_file;
        if (file_exists($config_file)) {
            $this->config = include $config_file;
        } else {
            throw new \RuntimeException("Configuration file does not exist.");
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

    private function save(): void
    {
        file_put_contents($this->config_file, "<?php\nreturn " . var_export($this->config, true) . ";\n");
        echo "Configuration updated and saved in {$this->config_file}\n";
    }
}
