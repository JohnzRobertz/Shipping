<?php
class Payment {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    /**
     * บันทึกการชำระเงิน
     */
    public function recordPayment($invoiceId, $amount, $paymentDate, $paymentMethod, $referenceNumber = '', $notes = '') {
        try {
            $this->db->beginTransaction();
            
            // บันทึกข้อมูลการชำระเงิน
            $stmt = $this->db->prepare("
                INSERT INTO payments (
                    invoice_id, amount, payment_date, payment_method, 
                    reference_number, notes, created_by, created_at
                ) VALUES (
                    :invoice_id, :amount, :payment_date, :payment_method,
                    :reference_number, :notes, :created_by, NOW()
                )
            ");
            
            $stmt->bindParam(':invoice_id', $invoiceId);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':payment_date', $paymentDate);
            $stmt->bindParam(':payment_method', $paymentMethod);
            $stmt->bindParam(':reference_number', $referenceNumber);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':created_by', $_SESSION['user_id']);
            $stmt->execute();
            
            $paymentId = $this->db->lastInsertId();
            
            // ดึงข้อมูลใบแจ้งหนี้
            $stmt = $this->db->prepare("
                SELECT total_amount, paid_amount FROM invoices WHERE id = :invoice_id
            ");
            $stmt->bindParam(':invoice_id', $invoiceId);
            $stmt->execute();
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // อัพเดทยอดชำระเงินและสถานะใบแจ้งหนี้
            $newPaidAmount = $invoice['paid_amount'] + $amount;
            $status = 'partially_paid';
            
            if ($newPaidAmount >= $invoice['total_amount']) {
                $status = 'paid';
            }
            
            $stmt = $this->db->prepare("
                UPDATE invoices
                SET paid_amount = :paid_amount, status = :status, updated_at = NOW()
                WHERE id = :invoice_id
            ");
            $stmt->bindParam(':paid_amount', $newPaidAmount);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':invoice_id', $invoiceId);
            $stmt->execute();
            
            $this->db->commit();
            return [
                'success' => true,
                'payment_id' => $paymentId
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * ดึงประวัติการชำระเงินของใบแจ้งหนี้
     */
    public function getPaymentsByInvoice($invoiceId) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username as created_by_name
            FROM payments p
            LEFT JOIN users u ON p.created_by = u.id
            WHERE p.invoice_id = :invoice_id
            ORDER BY p.payment_date DESC
        ");
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ดึงรายงานการชำระเงินตามวิธีการชำระเงิน
     */
    public function getPaymentReportByMethod($startDate = null, $endDate = null) {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($startDate) {
            $whereClause .= " AND payment_date >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        if ($endDate) {
            $whereClause .= " AND payment_date <= :end_date";
            $params[':end_date'] = $endDate;
        }
        
        $sql = "
            SELECT 
                payment_method,
                COUNT(*) as count,
                SUM(amount) as total_amount
            FROM payments
            $whereClause
            GROUP BY payment_method
        ";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ดึงรายงานการชำระเงินตามเดือน
     */
    public function getPaymentReportByMonth($year) {
        $sql = "
            SELECT 
                MONTH(payment_date) as month,
                COUNT(*) as count,
                SUM(amount) as total_amount
            FROM payments
            WHERE YEAR(payment_date) = :year
            GROUP BY MONTH(payment_date)
            ORDER BY MONTH(payment_date)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>