<?php

namespace App\Models\Financial;

use App\Libraries\Core\Database;
use PDO;

/**
 * Class Payment
 *
 * Handles database operations for the `payments` table.
 */
class Payment
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds all payments for a specific invoice.
     *
     * @param int $invoiceId The ID of the invoice.
     * @return array A list of payments.
     */
    public function findByInvoiceId(int $invoiceId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE invoice_id = :invoice_id ORDER BY payment_date DESC");
        $stmt->execute(['invoice_id' => $invoiceId]);
        return $stmt->fetchAll();
    }

    /**
     * Creates a new payment record.
     *
     * @param array $data An associative array of payment data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO payments (invoice_id, patient_id, payment_date, amount, payment_method, transaction_id)
                VALUES (:invoice_id, :patient_id, :payment_date, :amount, :payment_method, :transaction_id)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'invoice_id' => $data['invoice_id'] ?? null,
                'patient_id' => $data['patient_id'],
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
            ]);
        } catch (\PDOException $e) {
            error_log("Error creating payment: " . $e->getMessage());
            return false;
        }
    }
}
