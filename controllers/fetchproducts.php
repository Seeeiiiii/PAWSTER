<?php

include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php');

class prod_auto {

    public array $products     = [];
    public int   $total_count  = 0;
    public int   $total_pages  = 1;

    // Valid category values — must match the ENUM in tblsellerproduct
    public const CATEGORIES = [
        'Food & Treats',
        'Collar & Leashes',
        'Grooming',
        'Bed & Crates',
        'Toys',
        'Health & Vet',
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
            // Get total count for this category
            $countStmt = $db->conn->prepare(
                "SELECT COUNT(*) FROM tblsellerproduct WHERE category = ?"
            );
            $countStmt->bind_param("s", $category);
            $countStmt->execute();
            $countStmt->bind_result($this->total_count);
            $countStmt->fetch();
            $countStmt->close();

            // Paginated results
            $stmt = $db->conn->prepare(
                "SELECT * FROM tblsellerproduct WHERE category = ? ORDER BY brand_name ASC LIMIT ? OFFSET ?"
            );
            $stmt->bind_param("sii", $category, $per_page, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            // Get total count for all products
            $countResult = mysqli_query($db->conn, "SELECT COUNT(*) FROM tblsellerproduct");
            $this->total_count = (int) mysqli_fetch_row($countResult)[0];

            // Paginated results
            $result = mysqli_query(
                $db->conn,
                "SELECT * FROM tblsellerproduct ORDER BY category ASC, brand_name ASC LIMIT $per_page OFFSET $offset"
            );
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