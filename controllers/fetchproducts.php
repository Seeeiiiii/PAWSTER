<?php

include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php');

class prod_auto {

    public array $products     = [];
    public int   $total_count  = 0;
    public int   $total_pages  = 1;


    public const CATEGORIES = [
        'Pet Food',
        'Grooming Supplies',
        'Pet Accessories',
        'Pet Clothes',
    ];

    /**
     * @param object      $db        Your $db object (has ->conn mysqli connection)
     * @param string|null $category  Exact ENUM value to filter by, or null for all
     * @param int         $page      Current page number (1-based)
     * @param int         $per_page  Number of products per page
     */
    public function __construct($db, ?string $category = null, int $page = 1, int $per_page = 12) {
        $this->fetchProducts($db, $category, $page, $per_page);
    }

    public function fetchProducts($db, ?string $category = null, int $page = 1, int $per_page = 12): void {

        // Reject unknown category strings — fall back to all products
        if ($category !== null && !in_array($category, self::CATEGORIES, true)) {
            $category = null;
        }

        $offset = ($page - 1) * $per_page;

        if ($category !== null) {

            $countStmt = $db->conn->prepare(
                "SELECT COUNT(*) FROM tblsellerproduct WHERE primary_category = ?"
            );
            $countStmt->bind_param("s", $category);
            $countStmt->execute();
            $countStmt->bind_result($this->total_count);
            $countStmt->fetch();
            $countStmt->close();

      
            $stmt = $db->conn->prepare(
                "SELECT p.*, f.business_name
                 FROM tblsellerproduct p
                 LEFT JOIN tblapplicationform f ON f.formid = p.sellerid
                 WHERE p.primary_category = ?
                 ORDER BY p.brand_name ASC
                 LIMIT ? OFFSET ?"
            );
            $stmt->bind_param("sii", $category, $per_page, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
       
            $countStmt = $db->conn->prepare("SELECT COUNT(*) FROM tblsellerproduct");
            $countStmt->execute();
            $countStmt->bind_result($this->total_count);
            $countStmt->fetch();
            $countStmt->close();

          
            $stmt = $db->conn->prepare(
                "SELECT p.*, f.business_name
                 FROM tblsellerproduct p
                 LEFT JOIN tblapplicationform f ON f.formid = p.sellerid
                 ORDER BY p.primary_category ASC, p.brand_name ASC
                 LIMIT ? OFFSET ?"
            );
            $stmt->bind_param("ii", $per_page, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
        }

        while ($row = mysqli_fetch_assoc($result)) {
            $this->products[] = $row;
        }

        $this->total_pages = (int) ceil($this->total_count / $per_page);
    }

    public function __clone() {
        $this->productimage     = '';
        $this->brand_name       = '';
        $this->product_desc     = '';
        $this->primary_category = '';
    }

    public function __toString() {
        $output  = "<p>Photo: "       . $this->productimage     . "<br>\n";
        $output .= "Brand: "          . $this->brand_name       . "<br>\n";
        $output .= "Description: "    . $this->product_desc     . "<br>\n";
        $output .= "Category: "       . $this->primary_category . "<br>\n";
        return $output;
    }

    public function displayPhoto() {
        echo "<img src='" . $this->productimage . "' alt='Product Image'>";
    }

    public function displaySpecs() {
        echo "<p>Brand: "       . $this->brand_name       . "</p>";
        echo "<p>Description: " . $this->product_desc     . "</p>";
        echo "<p>Category: "    . $this->primary_category . "</p>";
    }
}
?>