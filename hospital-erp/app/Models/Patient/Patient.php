<?php

namespace App\Models\Patient;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Patient
 *
 * Handles database operations for the `patients` table.
 */
class Patient
{
    private PDO $db;

    /**
     * Patient constructor.
     * Gets the database connection instance.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all patients from the database.
     *
     * @return array A list of all patients.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT id, first_name, last_name, date_of_birth, gender FROM patients ORDER BY last_name, first_name");
        return $stmt->fetchAll();
    }

    /**
     * Finds a single patient by their ID.
     *
     * @param int $id The patient's ID.
     * @return mixed The patient data if found, otherwise false.
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Creates a new patient in the database.
     *
     * @param array $data An associative array of patient data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO patients (first_name, last_name, date_of_birth, gender, address, city, state, zip_code, phone_number, email, emergency_contact_name, emergency_contact_phone, blood_type, user_id)
                VALUES (:first_name, :last_name, :date_of_birth, :gender, :address, :city, :state, :zip_code, :phone_number, :email, :emergency_contact_name, :emergency_contact_phone, :blood_type, :user_id)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'zip_code' => $data['zip_code'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'email' => $data['email'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'blood_type' => $data['blood_type'] ?? null,
                'user_id' => $data['user_id'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating patient: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the status of a patient.
     *
     * @param int $id The patient's ID.
     * @param string $status The new status (e.g., 'Discharged').
     * @return bool True on success, false on failure.
     */
    public function updateStatus(int $id, string $status): bool
    {
        $sql = "UPDATE patients SET status = :status WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['status' => $status, 'id' => $id]);
        } catch (\PDOException $e) {
            error_log("Error updating patient status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds a single patient by their associated user ID.
     *
     * @param int $userId The user's ID from the `users` table.
     * @return mixed The patient data if found, otherwise false.
     */
    public function findByUserId(int $userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }
}
