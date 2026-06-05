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

    public function addListing(
        string $primary_category,
        string $brand_name,
        string $product_desc,
        int    $sellerid,
        array  $photo_file = []
    ): bool {

        $photo_name = null;

        if (!empty($photo_file['name'])) {
            $allowed  = ['image/png'];
            $max_size = 5 * 1024 * 1024;

            if (!in_array($photo_file['type'], $allowed)) return false;
            if ($photo_file['size'] > $max_size)          return false;

            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $photo_name = uniqid('prod_') . '_' . basename($photo_file['name']);
            if (!move_uploaded_file($photo_file['tmp_name'], $upload_dir . $photo_name)) {
                return false;
            }
        }

        if ($photo_name) {
            $stmt = $this->conn->prepare(
                "INSERT INTO tblsellerproduct (primary_category, brand_name, product_desc, sellerid, productimage)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssis", $primary_category, $brand_name, $product_desc, $sellerid, $photo_name);
        } else {
            $stmt = $this->conn->prepare(
                "INSERT INTO tblsellerproduct (primary_category, brand_name, product_desc, sellerid)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("sssi", $primary_category, $brand_name, $product_desc, $sellerid);
        }

        return $stmt->execute();
    }

    public function updateListing(
        int    $productid,
        int    $userid,
        string $primary_category,
        string $brand_name,
        string $product_desc,
        array  $photo_file = []
    ): bool {

        /* Verify ownership */
        $check = $this->conn->prepare(
            "SELECT p.productid FROM tblsellerproduct p
             JOIN tblsellerstatus s ON s.formid = p.sellerid
             WHERE p.productid = ? AND s.userid = ? LIMIT 1"
        );
        $check->bind_param("ii", $productid, $userid);
        $check->execute();
        $check->store_result();
        $owned = $check->num_rows > 0;
        $check->close();

        if (!$owned) return false;

        $photo_name = null;

        if (!empty($photo_file['name'])) {
            $allowed  = ['image/png'];
            $max_size = 5 * 1024 * 1024;

            if (!in_array($photo_file['type'], $allowed)) return false;
            if ($photo_file['size'] > $max_size)          return false;

            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $photo_name = uniqid('prod_') . '_' . basename($photo_file['name']);
            if (!move_uploaded_file($photo_file['tmp_name'], $upload_dir . $photo_name)) {
                return false;
            }
        }

        if ($photo_name) {
            $stmt = $this->conn->prepare(
                "UPDATE tblsellerproduct
                 SET primary_category = ?, brand_name = ?, product_desc = ?, productimage = ?
                 WHERE productid = ?"
            );
            $stmt->bind_param("ssssi", $primary_category, $brand_name, $product_desc, $photo_name, $productid);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE tblsellerproduct
                 SET primary_category = ?, brand_name = ?, product_desc = ?
                 WHERE productid = ?"
            );
            $stmt->bind_param("sssi", $primary_category, $brand_name, $product_desc, $productid);
        }

        return $stmt->execute();
    }
}