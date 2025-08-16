<?php

namespace App\Models\HumanResource;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Payroll
 *
 * Handles database operations for the `payrolls` table.
 */
class Payroll
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all payroll records for a specific staff member.
     *
     * @param int $staffId The ID of the staff member.
     * @return array A list of payroll records.
     */
    public function findByStaffId(int $staffId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM payrolls WHERE staff_id = :staff_id ORDER BY pay_period_end DESC");
        $stmt->execute(['staff_id' => $staffId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new payroll record for a staff member.
     *
     * @param array $data An associative array of payroll data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO payrolls (staff_id, pay_period_start, pay_period_end, gross_salary, deductions, net_salary, pay_date, status)
                VALUES (:staff_id, :pay_period_start, :pay_period_end, :gross_salary, :deductions, :net_salary, :pay_date, :status)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'staff_id' => $data['staff_id'],
                'pay_period_start' => $data['pay_period_start'],
                'pay_period_end' => $data['pay_period_end'],
                'gross_salary' => $data['gross_salary'],
                'deductions' => $data['deductions'] ?? 0.00,
                'net_salary' => $data['net_salary'],
                'pay_date' => $data['pay_date'],
                'status' => $data['status'] ?? 'Pending',
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating payroll record: " . $e->getMessage());
            return false;
        }
    }
}
