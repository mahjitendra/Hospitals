<?php

namespace App\Models\Admin;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Role
 *
 * Handles database operations for the `roles` table.
 */
class Role
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all roles from the database.
     *
     * @return array A list of all roles.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Creates a new role.
     *
     * @param array $data An associative array of role data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO roles (name, description) VALUES (:name, :description)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating role: " . $e->getMessage());
            return false;
        }
    }
}
