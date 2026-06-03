<?php
include_once __DIR__ . '/../config/app.php';

class ApplicationFormController
{
    public $conn;

    public function __construct()
    {
        $db = new DatabaseConnection();
        $this->conn = $db->conn;
    }

    public function submitApplication(
        string $business_name,
        string $contact_num,
        string $dti_reg,
        string $bir_reg,
        string $address,
        string $business_permit,
        string $valid_id,
        string $primary_category,
        string $brand_name,
        string $product_desc,
        int $userid
    ): bool {

        $stmt1 = $this->conn->prepare(
            "INSERT INTO tblapplicationform (business_name, contact_num, dti_reg, bir_reg, address, business_permit, valid_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt1->bind_param("sssssss", $business_name, $contact_num, $dti_reg, $bir_reg, $address, $business_permit, $valid_id);

        if (!$stmt1->execute()) {
            return false;
        }

        $form_id = $this->conn->insert_id;

        $stmt2 = $this->conn->prepare(
            "INSERT INTO tblsellerproduct (primary_category, brand_name, product_desc, sellerid)
             VALUES (?, ?, ?, ?)"
        );
        $stmt2->bind_param("sssi", $primary_category, $brand_name, $product_desc, $form_id);

        if (!$stmt2->execute()) {
            return false;
        }

        $status = 'pending';
        $stmt3 = $this->conn->prepare(
            "INSERT INTO tblsellerstatus (userid, formid, status)
     VALUES (?, ?, ?)"
        );
        $stmt3->bind_param("iis", $userid, $form_id, $status);

        if (!$stmt3->execute()) {
            return false;
        }

        return true;
    }
}
