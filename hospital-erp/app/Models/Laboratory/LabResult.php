<?php

namespace App\Models\Laboratory;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class LabResult
 *
 * Handles database operations for the `lab_results` table.
 */
class LabResult
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds a lab result by its corresponding order ID.
     *
     * @param int $orderId The ID of the lab order.
     * @return mixed The result data if found, otherwise false.
     */
    public function findByOrderId(int $orderId)
    {
        $stmt = $this->db->prepare("SELECT * FROM lab_results WHERE lab_order_id = :order_id");
        $stmt->execute(['order_id' => $orderId]);
        // An order could have multiple results/components, but we'll fetch one for now.
        return $stmt->fetch();
    }

    /**
     * Creates a new lab result.
     *
     * @param array $data An associative array of lab result data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO lab_results (lab_order_id, result_value, result_unit, is_abnormal, result_date, notes)
                VALUES (:lab_order_id, :result_value, :result_unit, :is_abnormal, :result_date, :notes)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'lab_order_id' => $data['lab_order_id'],
                'result_value' => $data['result_value'],
                'result_unit' => $data['result_unit'] ?? null,
                'is_abnormal' => $data['is_abnormal'] ?? false,
                'result_date' => $data['result_date'],
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating lab result: " . $e->getMessage());
            return false;
        }
    }
}
