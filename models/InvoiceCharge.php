<?php
class InvoiceCharge {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    public function addCharge($data) {
        $sql = 'INSERT INTO invoice_additional_charges (invoice_id, charge_type, description, amount, is_percentage) 
                VALUES (:invoice_id, :charge_type, :description, :amount, :is_percentage)';
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':invoice_id', $data['invoice_id'], PDO::PARAM_INT);
        $stmt->bindParam(':charge_type', $data['charge_type']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':amount', $data['amount']);
        $stmt->bindParam(':is_percentage', $data['is_percentage'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    public function getChargesByInvoiceId($invoice_id) {
        $sql = 'SELECT * FROM invoice_additional_charges WHERE invoice_id = :invoice_id ORDER BY id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteCharge($id) {
        $sql = 'DELETE FROM invoice_additional_charges WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function deleteAllChargesByInvoiceId($invoice_id) {
        $sql = 'DELETE FROM invoice_additional_charges WHERE invoice_id = :invoice_id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}

