<?php

namespace App\Models\Inventory;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Vendor
 *
 * Handles database operations for the `vendors` table.
 */
class Vendor
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all vendors from the database.
     *
     * @return array A list of all vendors.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM vendors ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Finds a single vendor by its ID.
     *
     * @param int $id The vendor's ID.
     * @return mixed The vendor data if found, otherwise false.
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM vendors WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Creates a new vendor.
     *
     * @param array $data An associative array of vendor data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO vendors (name, contact_person, phone_number, email, address)
                VALUES (:name, :contact_person, :phone_number, :email, :address)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'name' => $data['name'],
                'contact_person' => $data['contact_person'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating vendor: " . $e->getMessage());
            return false;
        }
    }
}
