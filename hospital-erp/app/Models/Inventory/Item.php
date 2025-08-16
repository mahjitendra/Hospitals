<?php

namespace App\Models\Inventory;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Item
 *
 * Handles database operations for the `inventory_items` table.
 */
class Item
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all inventory items from the database.
     *
     * @return array A list of all items.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM inventory_items ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Finds a single item by its ID.
     *
     * @param int $id The item's ID.
     * @return mixed The item data if found, otherwise false.
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM inventory_items WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Creates a new inventory item.
     *
     * @param array $data An associative array of item data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO inventory_items (name, description, category, quantity, reorder_level, unit_price, location)
                VALUES (:name, :description, :category, :quantity, :reorder_level, :unit_price, :location)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'quantity' => $data['quantity'] ?? 0,
                'reorder_level' => $data['reorder_level'] ?? 0,
                'unit_price' => $data['unit_price'] ?? 0.00,
                'location' => $data['location'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating inventory item: " . $e->getMessage());
            return false;
        }
    }
}
