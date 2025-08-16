<?php

namespace App\Models\Financial;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class InvoiceItem
 *
 * Handles database operations for the `invoice_items` table.
 */
class InvoiceItem
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all items for a specific invoice.
     *
     * @param int $invoiceId The ID of the invoice.
     * @return array A list of invoice items.
     */
    public function findByInvoiceId(int $invoiceId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM invoice_items WHERE invoice_id = :invoice_id");
        $stmt->execute(['invoice_id' => $invoiceId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new invoice item.
     *
     * @param array $data An associative array of item data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total_price)
                VALUES (:invoice_id, :description, :quantity, :unit_price, :total_price)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'invoice_id' => $data['invoice_id'],
                'description' => $data['description'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'total_price' => $data['total_price'],
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating invoice item: " . $e->getMessage());
            return false;
        }
    }
}
