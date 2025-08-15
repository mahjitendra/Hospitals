<?php

namespace App\Models\Laboratory;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class LabOrder
 *
 * Handles database operations for the `lab_orders` table.
 */
class LabOrder
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all lab orders for a specific patient, joining with lab test and user info.
     *
     * @param int $patientId The ID of the patient.
     * @return array A list of lab orders.
     */
    public function findByPatientId(int $patientId): array
    {
        $sql = "SELECT
                    lo.id,
                    lo.order_date,
                    lo.status,
                    lt.name as test_name,
                    u.name as doctor_name
                FROM lab_orders lo
                JOIN lab_tests lt ON lo.lab_test_id = lt.id
                JOIN users u ON lo.doctor_id = u.id
                WHERE lo.patient_id = :patient_id
                ORDER BY lo.order_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new lab order.
     *
     * @param array $data An associative array of lab order data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO lab_orders (patient_id, doctor_id, lab_test_id, order_date, notes)
                VALUES (:patient_id, :doctor_id, :lab_test_id, :order_date, :notes)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'lab_test_id' => $data['lab_test_id'],
                'order_date' => $data['order_date'],
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating lab order: " . $e->getMessage());
            return false;
        }
    }
}
