<?php

namespace App\Models\Inventory;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class PurchaseOrder
 *
 * Handles database operations for the `purchase_orders` table.
 */
class PurchaseOrder
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all purchase orders from the database.
     *
     * @return array A list of all purchase orders.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT po.*, v.name as vendor_name FROM purchase_orders po JOIN vendors v ON po.vendor_id = v.id ORDER BY order_date DESC");
        return $stmt->fetchAll();
    }

    /**
     * Finds a single purchase order by its ID.
     *
     * @param int $id The purchase order's ID.
     * @return mixed The purchase order data if found, otherwise false.
     */
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT po.*, v.name as vendor_name FROM purchase_orders po JOIN vendors v ON po.vendor_id = v.id WHERE po.id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Creates a new purchase order.
     *
     * @param array $data An associative array of purchase order data.
     * @return string|false The ID of the new purchase order on success, false on failure.
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO purchase_orders (vendor_id, order_date, expected_delivery_date, status, total_amount, notes)
                VALUES (:vendor_id, :order_date, :expected_delivery_date, :status, :total_amount, :notes)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'vendor_id' => $data['vendor_id'],
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => $data['status'] ?? 'Pending',
                'total_amount' => $data['total_amount'] ?? 0.00,
                'notes' => $data['notes'] ?? null,
            ]);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error creating purchase order: " . $e->getMessage());
            return false;
        }
    }
}
