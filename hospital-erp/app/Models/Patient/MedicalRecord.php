<?php

namespace App\Models\Patient;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class MedicalRecord
 *
 * Handles database operations for the `medical_records` table.
 */
class MedicalRecord
{
    private PDO $db;

    /**
     * MedicalRecord constructor.
     * Gets the database connection instance.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all medical records for a specific patient.
     *
     * @param int $patientId The ID of the patient.
     * @return array A list of medical records.
     */
    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM medical_records WHERE patient_id = :patient_id ORDER BY record_date DESC");
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new medical record.
     *
     * @param array $data An associative array of medical record data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO medical_records (patient_id, record_date, doctor_name, diagnosis, notes)
                VALUES (:patient_id, :record_date, :doctor_name, :diagnosis, :notes)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'patient_id' => $data['patient_id'],
                'record_date' => $data['record_date'],
                'doctor_name' => $data['doctor_name'],
                'diagnosis' => $data['diagnosis'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating medical record: " . $e->getMessage());
            return false;
        }
    }
}
