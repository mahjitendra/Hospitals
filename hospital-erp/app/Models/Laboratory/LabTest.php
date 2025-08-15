<?php

namespace App\Models\Laboratory;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class LabTest
 *
 * Handles database operations for the `lab_tests` table (test catalog).
 */
class LabTest
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all available lab tests.
     *
     * @return array A list of all lab tests.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM lab_tests ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Finds a single lab test by its ID.
     *
     * @param int $id The lab test's ID.
     * @return mixed The test data if found, otherwise false.
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM lab_tests WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
