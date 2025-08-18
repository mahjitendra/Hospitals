<?php

namespace App\Models\Integration;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class DataMapping
 *
 * Handles database operations for the `data_mappings` table.
 */
class DataMapping
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all data mappings from the database.
     *
     * @return array A list of all data mappings.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM data_mappings");
        return $stmt->fetchAll();
    }

    /**
     * Creates a new data mapping.
     *
     * @param array $data An associative array of mapping data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO data_mappings (source_system, source_field, destination_system, destination_field, mapping_logic)
                VALUES (:source_system, :source_field, :destination_system, :destination_field, :mapping_logic)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'source_system' => $data['source_system'],
                'source_field' => $data['source_field'],
                'destination_system' => $data['destination_system'],
                'destination_field' => $data['destination_field'],
                'mapping_logic' => $data['mapping_logic'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating data mapping: " . $e->getMessage());
            return false;
        }
    }
}
