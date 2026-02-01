<?php
/**
 * Address Class
 * Handles customer addresses
 */

class Address {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create address
     */
    public function createAddress($user_id, $recipient_name, $address_line1, $address_line2, $city, $state, $postal_code, $country, $phone) {
        $stmt = $this->db->prepare("
            INSERT INTO addresses (user_id, recipient_name, address_line1, address_line2, city, state, postal_code, country, phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssssss", $user_id, $recipient_name, $address_line1, $address_line2, $city, $state, $postal_code, $country, $phone);

        if ($stmt->execute()) {
            return $this->db->insert_id;
        } else {
            throw new Exception("Failed to create address: " . $stmt->error);
        }
    }

    /**
     * Get user addresses
     */
    public function getUserAddresses($user_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM addresses
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get address by ID
     */
    public function getAddressById($address_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM addresses WHERE address_id = ?
        ");
        $stmt->bind_param("i", $address_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Update address
     */
    public function updateAddress($address_id, $recipient_name, $address_line1, $address_line2, $city, $state, $postal_code, $country, $phone) {
        $stmt = $this->db->prepare("
            UPDATE addresses
            SET recipient_name = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, country = ?, phone = ?
            WHERE address_id = ?
        ");
        $stmt->bind_param("ssssssssi", $recipient_name, $address_line1, $address_line2, $city, $state, $postal_code, $country, $phone, $address_id);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Failed to update address: " . $stmt->error);
        }
    }

    /**
     * Delete address
     */
    public function deleteAddress($address_id) {
        $stmt = $this->db->prepare("DELETE FROM addresses WHERE address_id = ?");
        $stmt->bind_param("i", $address_id);
        return $stmt->execute();
    }
}
?>
