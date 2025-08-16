<?php

namespace App\Models\Admin;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class UserRole
 *
 * Handles database operations for the `user_roles` pivot table.
 */
class UserRole
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all roles for a specific user.
     *
     * @param int $userId The ID of the user.
     * @return array A list of roles.
     */
    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT r.* FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Assigns a role to a user.
     *
     * @param int $userId The ID of the user.
     * @param int $roleId The ID of the role.
     * @return bool True on success, false on failure.
     */
    public function assign(int $userId, int $roleId): bool
    {
        $sql = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['user_id' => $userId, 'role_id' => $roleId]);
        } catch (\PDOException $e) {
            error_log("Error assigning role to user: " . $e->getMessage());
            return false;
        }
    }
}
