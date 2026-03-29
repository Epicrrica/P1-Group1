<?php
// inc/order.inc.php

class OrderManager {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function updateFulfillment($purchase_id, $seller_username, $status, $tracking_number) {
        $sql = "UPDATE PRODUCT_PURCHASE 
                SET order_status = :status, 
                    tracking_number = :tracking 
                WHERE purchase_id = :purchase_id 
                AND seller_username = :seller_username";

        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':tracking', $tracking_number);
        $stmt->bindParam(':purchase_id', $purchase_id, PDO::PARAM_INT);
        $stmt->bindParam(':seller_username', $seller_username);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getOrderDetails($purchase_id) {
        $sql = "SELECT * FROM PRODUCT_PURCHASE WHERE purchase_id = :purchase_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':purchase_id', $purchase_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getOrdersBySeller($seller_username) {
        $sql = "SELECT * FROM PRODUCT_PURCHASE WHERE seller_username = :seller_username ORDER BY updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':seller_username', $seller_username);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>