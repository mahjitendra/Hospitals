<?php

namespace App\Models\HumanResource;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Schedule
 *
 * Handles database operations for the `staff_schedules` table.
 */
class Schedule
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all schedules for a specific staff member.
     *
     * @param int $staffId The ID of the staff member.
     * @return array A list of schedules.
     */
    public function findByStaffId(int $staffId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM staff_schedules WHERE staff_id = :staff_id ORDER BY shift_start DESC");
        $stmt->execute(['staff_id' => $staffId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new schedule for a staff member.
     *
     * @param array $data An associative array of schedule data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO staff_schedules (staff_id, shift_start, shift_end, notes)
                VALUES (:staff_id, :shift_start, :shift_end, :notes)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'staff_id' => $data['staff_id'],
                'shift_start' => $data['shift_start'],
                'shift_end' => $data['shift_end'],
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating schedule: " . $e->getMessage());
            return false;
        }
    }
}
