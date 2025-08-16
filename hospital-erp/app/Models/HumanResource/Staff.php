<?php

namespace App\Models\HumanResource;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Staff
 *
 * Handles database operations for the `staff` table.
 */
class Staff
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all staff members from the database.
     *
     * @return array A list of all staff members.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT id, user_id, first_name, last_name, job_title, department, status FROM staff ORDER BY last_name, first_name");
        return $stmt->fetchAll();
    }

    /**
     * Finds a single staff member by their ID.
     *
     * @param int $id The staff member's ID.
     * @return mixed The staff data if found, otherwise false.
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM staff WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Creates a new staff member.
     *
     * @param array $data An associative array of staff data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO staff (user_id, first_name, last_name, job_title, department, hire_date, phone_number, email, status)
                VALUES (:user_id, :first_name, :last_name, :job_title, :department, :hire_date, :phone_number, :email, :status)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'user_id' => $data['user_id'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'job_title' => $data['job_title'],
                'department' => $data['department'],
                'hire_date' => $data['hire_date'],
                'phone_number' => $data['phone_number'] ?? null,
                'email' => $data['email'],
                'status' => $data['status'] ?? 'Active',
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating staff member: " . $e->getMessage());
            return false;
        }
    }
}
