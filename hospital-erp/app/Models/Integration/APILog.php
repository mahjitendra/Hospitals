<?php

namespace App\Models\Integration;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class APILog
 *
 * Handles database operations for the `api_logs` table.
 */
class APILog
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Creates a new API log entry.
     *
     * @param array $data An associative array of log data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO api_logs (request_method, request_uri, request_payload, response_code, response_payload, ip_address)
                VALUES (:request_method, :request_uri, :request_payload, :response_code, :response_payload, :ip_address)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'request_method' => $data['request_method'],
                'request_uri' => $data['request_uri'],
                'request_payload' => $data['request_payload'] ?? null,
                'response_code' => $data['response_code'] ?? null,
                'response_payload' => $data['response_payload'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating API log: " . $e->getMessage());
            return false;
        }
    }
}
