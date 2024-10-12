<?php declare(strict_types=1);

namespace App\Display;

class TerminalDisplay
{

    private const COLORS = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'reset' => "\033[0m"
    ];

    public static function colorize(string $text, string $color): string
    {
        return self::COLORS[$color] . $text . self::COLORS['reset'];
    }

    public static function showUsageInstructions(): void
    {
        echo self::colorize("\nUsage Instructions:\n", 'yellow');

        // Section 1 : Recherche en base de données
        echo "  " . self::colorize("1. Database search", 'green') . "\n";
        echo "     " . self::colorize("check-data-extractor <transactionID> <GDS>", 'blue') . "\n";
        echo "     Use this command to retrieve transaction information from the database.\n";
        echo "     - " . self::colorize("<transactionID>", 'yellow') . ": The unique transaction ID.\n";
        echo "     - " . self::colorize("<GDS>", 'yellow') . ": The Global Distribution System (e.g., AMA, SAB).\n";

        // Section 2 : Reconfiguration du fichier de configuration
        echo "\n  " . self::colorize("2. Reconfiguration of the configuration file", 'green') . "\n";
        echo "     " . self::colorize("check-data-extractor reconfigure config", 'blue') . "\n";
        echo "     Run this command to reconfigure the database connection settings.\n\n";

        // Section 3 : Ajouter une requête SQL
        echo "\n  " . self::colorize("3. Add a new SQL query", 'green') . "\n";
        echo "     " . self::colorize("check-data-extractor add-query", 'blue') . "\n";
        echo "     Use this command to add a new SQL query to the configuration.\n";

        // Section 4 : Afficher les requêtes SQL
        echo "\n  " . self::colorize("4. Show saved SQL queries", 'green') . "\n";
        echo "     " . self::colorize("check-data-extractor show-queries", 'blue') . "\n";
        echo "     Run this command to display all the saved SQL queries from the configuration.\n\n";

    }

    public static function showError(string $message): void
    {
        echo self::colorize($message, 'red') . "\n";
    }

    public static function showInfo(string $message): void
    {
        echo self::colorize($message, 'blue') . "\n";
    }

    public static function showSuccess(string $message): void
    {
        echo self::colorize($message, 'green') . "\n";
    }

    public static function showWarning(string $message): void
    {
        echo self::colorize($message, 'yellow') . "\n";
    }

}
