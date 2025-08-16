<?php

namespace App\Models\Inventory;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class PurchaseOrderItem
 *
 * Handles database operations for the `purchase_order_items` table.
 */
class PurchaseOrderItem
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all items for a specific purchase order.
     *
     * @param int $orderId The ID of the purchase order.
     * @return array A list of purchase order items.
     */
    public function findByOrderId(int $orderId): array
    {
        $sql = "SELECT poi.*, i.name as item_name
                FROM purchase_order_items poi
                JOIN inventory_items i ON poi.inventory_item_id = i.id
                WHERE poi.purchase_order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new purchase order item.
     *
     * @param array $data An associative array of item data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO purchase_order_items (purchase_order_id, inventory_item_id, quantity, unit_price)
                VALUES (:purchase_order_id, :inventory_item_id, :quantity, :unit_price)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'purchase_order_id' => $data['purchase_order_id'],
                'inventory_item_id' => $data['inventory_item_id'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating purchase order item: " . $e->getMessage());
            return false;
        }
    }
}
