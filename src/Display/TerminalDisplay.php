<?php declare(strict_types=1);

namespace App\Display;

class TerminalDisplay
{
    // Définit les couleurs disponibles pour les textes dans le terminal
    private const COLORS = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'reset' => "\033[0m"
    ];

    // Méthode pour colorer du texte
    public static function colorize(string $text, string $color): string
    {
        return self::COLORS[$color] . $text . self::COLORS['reset'];
    }

    // Méthode pour afficher les instructions d'utilisation
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
    }

    // Méthode pour afficher les messages d'erreur
    public static function showError(string $message): void
    {
        echo self::colorize($message, 'red') . "\n";
    }

    public static function showSuces(string $message): void
    {
        echo self::colorize($message, 'green') . "\n";
    }
}
