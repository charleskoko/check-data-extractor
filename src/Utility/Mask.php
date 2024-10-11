<?php declare(strict_types=1);

namespace App\Utility;

class Mask
{
    public static function maskPassword(string $password): string
    {
        return str_repeat('*', max(0, strlen($password) - 2)) . substr($password, -2);
    }
}
