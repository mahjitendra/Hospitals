<?php

namespace App\Models\Admin;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class RolePermission
 *
 * Handles database operations for the `role_permissions` pivot table.
 */
class RolePermission
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all permissions for a specific role.
     *
     * @param int $roleId The ID of the role.
     * @return array A list of permissions.
     */
    public function findByRoleId(int $roleId): array
    {
        $stmt = $this->db->prepare("SELECT p.* FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = :role_id");
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll();
    }

    /**
     * Assigns a permission to a role.
     *
     * @param int $roleId The ID of the role.
     * @param int $permissionId The ID of the permission.
     * @return bool True on success, false on failure.
     */
    public function assign(int $roleId, int $permissionId): bool
    {
        $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
        } catch (\PDOException $e) {
            error_log("Error assigning permission to role: " . $e->getMessage());
            return false;
        }
    }
}
