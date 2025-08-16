<?php

namespace App\Models\Financial;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Invoice
 *
 * Handles database operations for the `invoices` table.
 */
class Invoice
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all invoices for a specific patient.
     *
     * @param int $patientId The ID of the patient.
     * @return array A list of invoices.
     */
    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE patient_id = :patient_id ORDER BY invoice_date DESC");
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    /**
     * Finds a single invoice by its ID.
     *
     * @param int $id The invoice ID.
     * @return mixed The invoice data if found, otherwise false.
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Creates a new invoice.
     *
     * @param array $data An associative array of invoice data.
     * @return string|false The ID of the new invoice on success, false on failure.
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO invoices (patient_id, invoice_date, due_date, total_amount, status)
                VALUES (:patient_id, :invoice_date, :due_date, :total_amount, :status)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'patient_id' => $data['patient_id'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'total_amount' => $data['total_amount'],
                'status' => $data['status'] ?? 'Draft',
            ]);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error creating invoice: " . $e->getMessage());
            return false;
        }
    }
}
