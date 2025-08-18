<?php

namespace App\Models\Clinical;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Prescription
 *
 * Handles database operations for the `prescriptions` table.
 */
class Prescription
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all prescriptions for a specific patient.
     *
     * @param int $patientId The ID of the patient.
     * @return array A list of prescriptions.
     */
    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM prescriptions WHERE patient_id = :patient_id ORDER BY start_date DESC");
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new prescription.
     *
     * @param array $data An associative array of prescription data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO prescriptions (patient_id, doctor_id, drug, dosage, frequency, start_date, end_date, status)
                VALUES (:patient_id, :doctor_id, :drug, :dosage, :frequency, :start_date, :end_date, :status)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'drug' => $data['drug'],
                'dosage' => $data['dosage'],
                'frequency' => $data['frequency'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'status' => $data['status'] ?? 'Active',
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating prescription: " . $e->getMessage());
            return false;
        }
    }
}
