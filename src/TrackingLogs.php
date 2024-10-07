<?php

require_once 'DatabaseConnection.php';

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