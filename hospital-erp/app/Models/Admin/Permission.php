<?php

namespace App\Models\Admin;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Permission
 *
 * Handles database operations for the `permissions` table.
 */
class Permission
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all permissions from the database.
     *
     * @return array A list of all permissions.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM permissions ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Creates a new permission.
     *
     * @param array $data An associative array of permission data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO permissions (name, description) VALUES (:name, :description)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating permission: " . $e->getMessage());
            return false;
        }
    }
}
