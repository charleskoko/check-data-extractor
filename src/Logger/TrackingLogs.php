<?php declare(strict_types=1);

namespace App\Logger;

use App\Database\DatabaseConnection;
use PDO;
use PDOStatement;

class TrackingLogs
{
    private const TABLES = [
        'yps' => 'tracking_ypsilon',
        'trf' => 'tracking_travelfusion',
        'ama' => 'tracking_amadeus',
        'sab' => 'tracking_sabre',
    ];
    private DatabaseConnection $databaseConnection;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    public function doRequestForTrackingData(string $transactionId, string $gds): void
    {
        $table = self::TABLES[strtolower($gds)] ?? null;

        if ($table === null) {
            throw new \RuntimeException('Invalid GDS or transaction ID.');
        }

        $sql = "SELECT timestamp, type, UNCOMPRESS(request_raw), UNCOMPRESS(response_raw) 
                FROM $table WHERE tracking_api_transaction = :tracking_api_transaction";

        $connection = $this->databaseConnection->getConnection();
        $request = $connection->prepare($sql);
        $request->bindParam(':tracking_api_transaction', $transactionId, PDO::PARAM_STR);
        $request->execute();

        $this->saveInfoInFolder($transactionId, $request);
    }

    private function saveInfoInFolder(string $transactionId, PDOStatement $request): void
    {
        $folder = getenv("HOME") . "/Downloads/transaction_{$transactionId}";

        if (!is_dir($folder) && !mkdir($folder, 0777, true)) {
            throw new \RuntimeException("Failed to create directory: $folder");
        }

        while ($row = $request->fetch(PDO::FETCH_ASSOC)) {
            $timestamp = $row['timestamp'];
            $type = $row['type'];
            $request_raw = $row['UNCOMPRESS(request_raw)'];
            $response_raw = $row['UNCOMPRESS(response_raw)'];

            file_put_contents("$folder/{$transactionId}_{$timestamp}_{$type}_RQ.xml", $request_raw);
            file_put_contents("$folder/{$transactionId}_{$timestamp}_{$type}_RS.xml", $response_raw);
        }
    }
}
