<?php

namespace App\Models\Clinical;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class ClinicalNote
 *
 * Handles database operations for the `clinical_notes` table.
 */
class ClinicalNote
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all clinical notes for a specific patient.
     *
     * @param int $patientId The ID of the patient.
     * @return array A list of clinical notes.
     */
    public function findByPatientId(int $patientId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM clinical_notes WHERE patient_id = :patient_id ORDER BY note_date DESC");
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new clinical note.
     *
     * @param array $data An associative array of clinical note data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO clinical_notes (patient_id, doctor_id, note_date, note_type, note)
                VALUES (:patient_id, :doctor_id, :note_date, :note_type, :note)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'],
                'note_date' => $data['note_date'],
                'note_type' => $data['note_type'],
                'note' => $data['note'],
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating clinical note: " . $e->getMessage());
            return false;
        }
    }
}
