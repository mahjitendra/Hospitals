<?php

namespace App\Models;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class User
 *
 * Represents a user in the system and handles all database
 * interactions for the `users` table.
 */
class User
{
    private PDO $db;

    /**
     * User constructor.
     * Gets the database connection instance.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds a user by their email address.
     *
     * @param string $email The user's email address.
     * @return mixed The user data as an associative array if found, otherwise false.
     */
    public function findByEmail(string $email)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            // In a real application, you should log this error.
            error_log("Error finding user by email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new user in the database.
     *
     * @param array $data An associative array containing user data (name, email, password).
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        // The password should be hashed by the controller before being passed to this method.
        $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
        } catch (\PDOException $e) {
            // In a real application, you should log this error.
            // The UNIQUE constraint on email might cause this to fail, which is expected.
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }
}
