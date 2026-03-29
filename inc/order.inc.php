<?php
// inc/order.inc.php

class OrderManager {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function updateFulfillment($purchase_id, $seller_username, $status, $tracking_number) {
        $sql = "UPDATE PRODUCT_PURCHASE 
                SET order_status = ?, 
                    tracking_number = ? 
                WHERE purchase_id = ? 
                AND seller_username = ?";

        $stmt = $this->db->prepare($sql);
        
        // s = string, i = integer
        $stmt->bind_param('ssis', $status, $tracking_number, $purchase_id, $seller_username);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getOrderDetails($purchase_id) {
        $sql = "SELECT * FROM PRODUCT_PURCHASE WHERE purchase_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $purchase_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function getOrdersBySeller($seller_username) {
        $sql = "SELECT pp.*, p.product_name 
                FROM PRODUCT_PURCHASE pp
                JOIN PRODUCT p ON pp.product_id = p.product_id
                WHERE pp.seller_username = ? 
                ORDER BY pp.updated_at DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $seller_username);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>